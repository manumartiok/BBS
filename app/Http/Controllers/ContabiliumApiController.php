<?php

namespace App\Http\Controllers;

use App\Services\ContabiliumService;
use Illuminate\Http\Request;

class ContabiliumApiController extends Controller
{
    public function __construct(private ContabiliumService $contabilium)
    {
    }

    // Productos ("conceptos" en Contabilium)
    // /api                 -> página 1, 50 productos
    // /api?page=2          -> otra página
    // /api?pageSize=10     -> cantidad por página
    // /api?todos=1         -> recorre todas las páginas
    public function productos(Request $request)
    {
        return $this->listado('api/conceptos/search', $request);
    }

    // Clientes
    // /api/clientes                 -> página 1, 50 clientes
    // /api/clientes?page=2          -> otra página
    // /api/clientes?pageSize=10     -> cantidad por página
    // /api/clientes?todos=1         -> recorre todas las páginas
    public function clientes(Request $request)
    {
        return $this->listado('api/clientes/search', $request);
    }

    // Cuentas corrientes: Contabilium no tiene un endpoint propio, se arma con los
    // comprobantes (facturas, notas de crédito...) y su campo "Saldo" (lo que falta cobrar)
    // /api                                      -> comprobantes de los últimos 30 días
    // /api?fechaDesde=2026-01-01&fechaHasta=2026-10-23
    // /api?conSaldo=1                           -> solo los que tienen saldo pendiente
    // /api?page=2 / ?pageSize=10 / ?todos=1     -> igual que el resto
    public function cuentasCorrientes(Request $request)
    {
        return $this->listado('api/comprobantes/search', $request, [
            'fechaDesde' => $request->query('fechaDesde', now()->subDays(30)->toDateString()),
            'fechaHasta' => $request->query('fechaHasta', now()->toDateString()),
        ], $request->boolean('conSaldo'));
    }

    // Trae una página (o todas con ?todos=1) de un endpoint de búsqueda
    private function listado(string $endpoint, Request $request, array $params = [], bool $conSaldo = false)
    {
        $pageSize = (int) $request->query('pageSize', 1);

        try {
            if (!$request->boolean('todos')) {
                $data = $this->contabilium->pagina(
                    $endpoint,
                    (int) $request->query('page', 100),
                    $pageSize,
                    $params
                );

                if ($conSaldo) {
                    $data['Items'] = $this->filtrarConSaldo($data['Items'] ?? []);
                }

                return $this->json($data);
            }

            $items = $this->contabilium->todos($endpoint, $pageSize, $params);

            if ($conSaldo) {
                $items = $this->filtrarConSaldo($items);
            }

            return $this->json([
                'TotalItems' => count($items),
                'Items' => $items,
            ]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 502);
        }
    }

    // El Saldo viene como texto con formato argentino ("160.000,00")
    private function filtrarConSaldo(array $items): array
    {
        return array_values(array_filter($items, function ($item) {
            $saldo = (float) str_replace(['.', ','], ['', '.'], (string) ($item['Saldo'] ?? '0'));

            return $saldo != 0;
        }));
    }

    private function json($data, int $status = 200)
    {
        return response()->json($data, $status, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
