<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SincronizarTodo extends Command
{
    protected $signature = 'sincronizar';

    protected $description = 'Corre todas las sincronizaciones: clientes, comprobantes y productos de Contabilium, y el dólar del BCRA';

    // Los clientes van antes que los comprobantes: la cuenta corriente usa su contabilium_id
    private const COMANDOS = [
        'Clientes' => 'contabilium:clientes',
        'Comprobantes' => 'contabilium:comprobantes',
        'Productos' => 'contabilium:productos',
        'Dólar' => 'bcra:dolar',
    ];

    public function handle(): int
    {
        $resultados = [];

        // Si una sincronización falla se sigue con las demás
        foreach (self::COMANDOS as $nombre => $comando) {
            $this->newLine();
            $this->line("<options=bold>== $nombre ($comando)</>");

            $inicio = microtime(true);

            try {
                $ok = $this->call($comando) === self::SUCCESS;
            } catch (\Throwable $e) {
                $this->error($e->getMessage());
                $ok = false;
            }

            $resultados[] = [$nombre, $ok ? 'OK' : 'ERROR', round(microtime(true) - $inicio) . ' s'];
        }

        $this->newLine();
        $this->table(['Sincronización', 'Resultado', 'Tiempo'], $resultados);

        $fallas = count(array_filter($resultados, fn ($r) => $r[1] === 'ERROR'));

        return $fallas ? self::FAILURE : self::SUCCESS;
    }
}
