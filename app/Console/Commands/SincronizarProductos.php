<?php

namespace App\Console\Commands;

use App\Models\Producto;
use App\Services\ContabiliumService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class SincronizarProductos extends Command
{
    protected $signature = 'contabilium:productos';

    protected $description = 'Sincroniza la tabla productos con los productos (conceptos) de Contabilium';

    // Las fotos se descargan sin autenticación desde esta URL + el campo "Foto"
    private const FOTOS_URL = 'https://app.contabilium.com/files/explorer/';

    public function handle(ContabiliumService $contabilium): int
    {
        $this->info('Trayendo productos de Contabilium...');

        try {
            $items = $contabilium->todos('api/conceptos/search');
        } catch (\Throwable $e) {
            $this->error('No se pudo conectar con Contabilium: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info(count($items) . ' productos recibidos.');

        // Cantidad de letras del orden: AA..ZZ alcanza para 676, AAA..ZZZ para 17576
        $total = max(count($items), Producto::count());
        $letras = 2;
        while ($total > 26 ** $letras) {
            $letras++;
        }

        $creados = 0;
        $actualizados = 0;
        $omitidos = [];

        DB::transaction(function () use ($items, $letras, &$creados, &$actualizados, &$omitidos) {
            $siguiente = Producto::count();

            foreach ($items as $item) {
                $codigo = trim((string) ($item['Codigo'] ?? ''));
                $precio = $item['Precio'] ?? 0;

                if ($codigo === '') {
                    $omitidos[] = ['-', $item['Nombre'] ?? '', 'Sin código'];
                    continue;
                }

                // La columna precio es decimal(10,2)
                if ($precio > 99999999.99) {
                    $omitidos[] = [$codigo, $item['Nombre'] ?? '', 'Precio fuera de rango: ' . $precio];
                    continue;
                }

                $producto = Producto::where('codigo', $codigo)->first();

                // Producto nuevo: se crea con los valores por defecto.
                // Si ya existe se respetan marca, orden, destacado, foto y descripción cargados en el admin.
                if (!$producto) {
                    $producto = new Producto();
                    $producto->codigo = $codigo;
                    $producto->marca_id = null;
                    $producto->orden = $this->orden($siguiente++, $letras);
                    $producto->stock_transito = 0;
                    $producto->destacado = false;
                    $creados++;
                } else {
                    $actualizados++;
                }

                $producto->nombre = $item['Nombre'] ?? '';
                $producto->stock_fisico = $item['Stock'] ?? 0;
                $producto->precio = $precio;
                $producto->iva = $item['Iva'] ?? null;
                $producto->active = true;
                $producto->save();
            }
        });

        $this->table(['Creados', 'Actualizados', 'Omitidos'], [[$creados, $actualizados, count($omitidos)]]);

        if ($omitidos) {
            $this->warn('Productos omitidos:');
            $this->table(['Código', 'Nombre', 'Motivo'], $omitidos);
        }

        $this->sincronizarFotos($items);

        return self::SUCCESS;
    }

    // Descarga las fotos de Contabilium a storage/productos.
    // No pisa las fotos cargadas a mano: solo completa productos sin foto (o con /default.png)
    // y actualiza las que ya habían venido de Contabilium.
    private function sincronizarFotos(array $items): void
    {
        $conFoto = array_filter($items, fn ($item) => !empty($item['Foto']) && trim((string) ($item['Codigo'] ?? '')) !== '');

        if (!$conFoto) {
            return;
        }

        $this->info('Sincronizando ' . count($conFoto) . ' fotos...');

        $disco = Storage::disk('public');
        $descargadas = 0;
        $sinCambios = 0;
        $manuales = 0;
        $errores = [];

        foreach ($conFoto as $item) {
            $producto = Producto::where('codigo', trim($item['Codigo']))->first();

            if (!$producto) {
                continue;
            }

            $archivo = 'productos/' . basename($item['Foto']);
            $url = Storage::url($archivo);

            if (!empty($producto->foto) && $producto->foto !== '/default.png' && $producto->foto !== $url) {
                $manuales++;
                continue;
            }

            try {
                // A veces Contabilium manda la URL completa pegada a la ruta
                // ("121417/Productos-Servicios/https://app.contabilium.com/...")
                $origen = str_contains($item['Foto'], '://')
                    ? substr($item['Foto'], strpos($item['Foto'], 'http'))
                    : self::FOTOS_URL . ltrim($item['Foto'], '/');

                // Si ya está descargada, solo se vuelve a bajar si cambió el tamaño o la fecha
                if ($disco->exists($archivo)) {
                    $head = Http::timeout(15)->head($origen)->throw();
                    $modificada = strtotime($head->header('Last-Modified') ?: 'now');

                    if ((int) $head->header('Content-Length') === $disco->size($archivo)
                        && $modificada <= $disco->lastModified($archivo)) {
                        if ($producto->foto !== $url) {
                            $producto->foto = $url;
                            $producto->save();
                        }
                        $sinCambios++;
                        continue;
                    }
                }

                $response = Http::timeout(30)->get($origen)->throw();

                if (!str_starts_with((string) $response->header('Content-Type'), 'image/')) {
                    throw new \RuntimeException('No es una imagen');
                }

                $disco->put($archivo, $response->body());

                $producto->foto = $url;
                $producto->save();
                $descargadas++;
            } catch (\Throwable $e) {
                $errores[] = [$producto->codigo, $item['Foto'], $e->getMessage()];
            }
        }

        $this->table(
            ['Descargadas', 'Sin cambios', 'Foto manual (no se tocó)', 'Errores'],
            [[$descargadas, $sinCambios, $manuales, count($errores)]]
        );

        if ($errores) {
            $this->warn('Fotos con error:');
            $this->table(['Código', 'Foto', 'Error'], $errores);
        }
    }

    // Posición -> letras: 0 = AA, 1 = AB, ... 26 = BA (o AAA, AAB... con 3 letras)
    private function orden(int $posicion, int $letras): string
    {
        $orden = '';

        for ($i = 0; $i < $letras; $i++) {
            $orden = chr(65 + ($posicion % 26)) . $orden;
            $posicion = intdiv($posicion, 26);
        }

        return $orden;
    }
}
