<?php

namespace App\Console\Commands;

use App\Models\Comprobante;
use App\Services\ContabiliumService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SincronizarComprobantes extends Command
{
    protected $signature = 'contabilium:comprobantes {--desde=2020-01-01 : Fecha de emisión desde (AAAA-MM-DD)}';

    protected $description = 'Sincroniza la tabla comprobantes (cuenta corriente) con los comprobantes de Contabilium';

    public function handle(ContabiliumService $contabilium): int
    {
        $params = [
            'fechaDesde' => $this->option('desde'),
            'fechaHasta' => now()->toDateString(),
        ];

        $this->info("Sincronizando comprobantes de {$params['fechaDesde']} a {$params['fechaHasta']}...");

        $creados = 0;
        $actualizados = 0;
        $barra = null;
        $page = 1;
        $procesados = 0;

        // Se trae una página y se carga antes de pedir la siguiente
        do {
            try {
                $data = $contabilium->pagina('api/comprobantes/search', $page, 50, $params);
            } catch (\Throwable $e) {
                $this->newLine();
                $this->error("No se pudo traer la página $page de Contabilium: " . $e->getMessage());
                return self::FAILURE;
            }

            $lote = $data['Items'] ?? [];
            $total = $data['TotalItems'] ?? 0;

            if (!$barra) {
                $this->info($total . ' comprobantes en Contabilium.');
                $barra = $this->output->createProgressBar($total);
                $barra->start();
            }

            foreach ($lote as $item) {
                $comprobante = Comprobante::firstOrNew(['contabilium_id' => $item['Id']]);
                $comprobante->exists ? $actualizados++ : $creados++;

                $comprobante->fill([
                    'cliente_id' => $item['IdCliente'] ?? 0,
                    'razon_social' => trim((string) ($item['RazonSocial'] ?? '')),
                    'tipo' => $item['TipoFc'] ?? null,
                    'numero' => $item['Numero'] ?? null,
                    'fecha_emision' => $this->fecha($item['FechaEmision'] ?? null),
                    'fecha_vencimiento' => $this->fecha($item['FechaVencimiento'] ?? null),
                    'importe' => $this->importe($item['ImporteTotalNeto'] ?? '0'),
                    'saldo' => $this->importe($item['Saldo'] ?? '0'),
                    'condicion_venta' => $item['CondicionVenta'] ?? null,
                ])->save();

                $barra->advance();
            }

            $procesados += count($lote);
            $page++;
        } while (count($lote) > 0 && $procesados < $total);

        $barra?->finish();
        $this->newLine(2);

        $this->table(['Creados', 'Actualizados'], [[$creados, $actualizados]]);

        return self::SUCCESS;
    }

    // Los importes vienen como texto con formato argentino ("-118.761,50")
    private function importe(string $valor): float
    {
        return (float) str_replace(['.', ','], ['', '.'], $valor);
    }

    private function fecha(?string $valor): ?string
    {
        return $valor ? Carbon::parse($valor)->toDateString() : null;
    }
}
