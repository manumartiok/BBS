<?php

namespace App\Console\Commands;

use App\Models\WebUsuario;
use App\Services\ContabiliumService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SincronizarClientes extends Command
{
    protected $signature = 'contabilium:clientes {--rondas=4 : Rondas de bcrypt para las contraseñas iniciales (4 a 31)}';

    protected $description = 'Sincroniza la tabla web_usuarios con los clientes de Contabilium';

    private int $creados = 0;
    private int $actualizados = 0;
    private int $omitidos = 0;

    public function handle(ContabiliumService $contabilium): int
    {
        $rondas = max(4, min(31, (int) $this->option('rondas')));

        $this->info('Sincronizando clientes de Contabilium...');

        $barra = null;
        $page = 1;
        $procesados = 0;

        // Se trae una página y se carga antes de pedir la siguiente
        do {
            try {
                $data = $contabilium->pagina('api/clientes/search', $page);
            } catch (\Throwable $e) {
                $this->newLine();
                $this->error("No se pudo traer la página $page de Contabilium: " . $e->getMessage());
                $this->error('Lo cargado hasta acá quedó guardado; al volver a correr el comando sigue rápido.');
                return self::FAILURE;
            }

            $lote = $data['Items'] ?? [];
            $total = $data['TotalItems'] ?? 0;

            if (!$barra) {
                $this->info($total . ' clientes en Contabilium.');
                $barra = $this->output->createProgressBar($total);
                $barra->start();
            }

            foreach ($lote as $item) {
                $this->guardar($item, $rondas);
                $barra->advance();
            }

            $procesados += count($lote);
            $page++;
        } while (count($lote) > 0 && $procesados < $total);

        $barra?->finish();
        $this->newLine(2);

        $this->table(
            ['Creados', 'Actualizados', 'Omitidos (sin NroDoc)'],
            [[$this->creados, $this->actualizados, $this->omitidos]]
        );

        return self::SUCCESS;
    }

    private function guardar(array $item, int $rondas): void
    {
        // El NroDoc identifica al cliente (cuit) y es su contraseña inicial
        $cuit = trim((string) ($item['NroDoc'] ?? ''));

        if ($cuit === '') {
            $this->omitidos++;
            return;
        }

        $usuario = WebUsuario::where('cuit', $cuit)->first();

        // Usuario nuevo: activo, contraseña = NroDoc hasheado, descuentos y empresa en blanco.
        // Si ya existe no se tocan contraseña, descuentos, empresa ni estado.
        if (!$usuario) {
            $usuario = new WebUsuario();
            $usuario->cuit = $cuit;
            $usuario->contraseña = Hash::make($cuit, ['rounds' => $rondas]);
            $usuario->descuento1 = null;
            $usuario->descuento2 = null;
            $usuario->descuento3 = null;
            $usuario->descuento4 = null;
            $usuario->descuento5 = null;
            $usuario->empresa = null;
            $usuario->active = true;
            $this->creados++;
        } else {
            $this->actualizados++;
        }

        $usuario->usuario = trim((string) ($item['NombreFantasia'] ?? ''));
        $usuario->email = trim((string) ($item['Email'] ?? ''));
        $usuario->razon_social = trim((string) ($item['RazonSocial'] ?? ''));
        $usuario->telefono = $item['Telefono'] ?? null;
        $usuario->domicilio = $item['Domicilio'] ?? null;
        $usuario->codigo_postal = $item['Cp'] ?? null;
        $usuario->localidad = $item['Ciudad'] ?? null;
        $usuario->provincia = $item['Provincia'] ?? null;
        $usuario->save();
    }
}
