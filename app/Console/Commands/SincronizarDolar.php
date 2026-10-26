<?php

namespace App\Console\Commands;

use App\Models\Cotizacion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SincronizarDolar extends Command
{
    protected $signature = 'bcra:dolar';

    protected $description = 'Sincroniza la cotización del dólar oficial con la API de estadísticas cambiarias del BCRA';

    private const URL = 'https://api.bcra.gob.ar/estadisticascambiarias/v1.0/Cotizaciones';

    public function handle(): int
    {
        $this->info('Consultando la cotización del BCRA...');

        try {
            $results = Http::timeout(15)->retry(2, 500)->get(self::URL)->throw()->json('results');
        } catch (\Throwable $e) {
            $this->error('No se pudo conectar con el BCRA: ' . $e->getMessage());
            return self::FAILURE;
        }

        $usd = collect($results['detalle'] ?? [])->firstWhere('codigoMoneda', 'USD');
        $valor = $usd['tipoCotizacion'] ?? null;

        if (!is_numeric($valor) || $valor <= 0) {
            $this->error('La respuesta del BCRA no incluye la cotización del dólar.');
            return self::FAILURE;
        }

        $cotizacion = Cotizacion::updateOrCreate(
            ['moneda' => 'USD'],
            ['valor' => $valor, 'fecha' => $results['fecha'] ?? null]
        );

        $this->info('Dólar oficial: $' . number_format($cotizacion->valor, 2, ',', '.') . ' (' . ($results['fecha'] ?? 's/f') . ')');

        return self::SUCCESS;
    }
}
