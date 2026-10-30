<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ContabiliumService
{
    // Una página de un endpoint de búsqueda (Items, TotalItems, ...)
    // $params: filtros extra del endpoint (ej. fechaDesde / fechaHasta en comprobantes)
    public function pagina(string $endpoint, int $page = 1, int $pageSize = 50, array $params = []): array
    {
        return $this->get($endpoint, ['page' => $page, 'pageSize' => $pageSize] + $params);
    }

    // Recorre todas las páginas de un endpoint de búsqueda y junta los Items
    public function todos(string $endpoint, int $pageSize = 50, array $params = []): array
    {
        $items = [];
        $page = 1;

        do {
            $data = $this->pagina($endpoint, $page, $pageSize, $params);
            $lote = $data['Items'] ?? [];
            $items = array_merge($items, $lote);
            $page++;
        } while (count($lote) > 0 && count($items) < ($data['TotalItems'] ?? 0));

        return $items;
    }

    // GET autenticado; si el token venció, pide uno nuevo y reintenta una vez
    public function get(string $endpoint, array $params = []): array
    {
        $response = Http::withToken($this->token())
            ->acceptJson()
            ->get($this->url($endpoint), $params);

        if ($response->status() === 401) {
            Cache::forget('contabilium_token');

            $response = Http::withToken($this->token())
                ->acceptJson()
                ->get($this->url($endpoint), $params);
        }

        $response->throw();

        return $response->json() ?? [];
    }

    // PDF de un comprobante (contenido binario)
    public function pdfComprobante(int $id): string
    {
        $response = Http::withToken($this->token())
            ->timeout(30)
            ->get($this->url('api/comprobantes/obtenerPdf'), ['id' => $id]);

        if ($response->status() === 401) {
            Cache::forget('contabilium_token');

            $response = Http::withToken($this->token())
                ->timeout(30)
                ->get($this->url('api/comprobantes/obtenerPdf'), ['id' => $id]);
        }

        $response->throw();

        if (!str_starts_with((string) $response->header('Content-Type'), 'application/pdf')) {
            throw new \RuntimeException('Contabilium no devolvió un PDF');
        }

        return $response->body();
    }

    // Token de acceso (dura 24 h, se guarda en caché un poco menos)
    private function token(): string
    {
        return Cache::remember('contabilium_token', now()->addHours(23), function () {
            $response = Http::asForm()->post($this->url('token'), [
                'grant_type' => 'client_credentials',
                'client_id' => config('services.contabilium.email'),
                'client_secret' => config('services.contabilium.api_key'),
            ])->throw();

            return $response->json('access_token');
        });
    }

    private function url(string $endpoint): string
    {
        return rtrim(config('services.contabilium.url'), '/') . '/' . $endpoint;
    }
}
