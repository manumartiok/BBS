<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\PedidoPublico;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Net\MPSearchRequest;
use MercadoPago\Resources\Payment;
use MercadoPago\Resources\Preference;

// Checkout Pro de Mercado Pago para los pedidos de los dos carritos: el público (PedidoPublico)
// y el de clientes (Pedido). Los dos guardan el pago en las mismas columnas (TienePagoMercadoPago)
class MercadoPagoService
{
    // estado de Mercado Pago => estado del pago en el pedido
    private const ESTADOS = [
        'approved' => 'aprobado',
        'authorized' => 'pendiente',
        'pending' => 'pendiente',
        'in_process' => 'pendiente',
        'in_mediation' => 'pendiente',
        'rejected' => 'rechazado',
        'cancelled' => 'cancelado',
        'refunded' => 'devuelto',
        'charged_back' => 'devuelto',
    ];

    // external_reference de los pedidos de clientes; los públicos van con el id solo
    private const PREFIJO_CLIENTES = 'C-';

    public function __construct()
    {
        MercadoPagoConfig::setAccessToken((string) config('services.mercadopago.access_token'));
    }

    public function publicKey(): string
    {
        return (string) config('services.mercadopago.public_key');
    }

    public function referencia(Pedido|PedidoPublico $pedido): string
    {
        return ($pedido instanceof Pedido ? self::PREFIJO_CLIENTES : '') . $pedido->id;
    }

    public function pedidoDeReferencia(?string $referencia): Pedido|PedidoPublico|null
    {
        $referencia = (string) $referencia;
        if (str_starts_with($referencia, self::PREFIJO_CLIENTES)) {
            $id = substr($referencia, strlen(self::PREFIJO_CLIENTES));
            return ctype_digit($id) ? Pedido::find((int) $id) : null;
        }

        return ctype_digit($referencia) ? PedidoPublico::find((int) $referencia) : null;
    }

    // Preferencia con el total del pedido en un solo ítem, para que el monto cobrado sea exactamente el del pedido
    public function crearPreferencia(Pedido|PedidoPublico $pedido, string $email, string $retorno): Preference
    {
        $datos = [
            'items' => [[
                'id' => $this->referencia($pedido),
                'title' => 'Pedido #' . str_pad($pedido->id, 8, '0', STR_PAD_LEFT) . ' - ' . config('app.name'),
                'quantity' => 1,
                'unit_price' => round((float) $pedido->total, 2),
                'currency_id' => 'ARS',
            ]],
            'payer' => ['email' => $email],
            'external_reference' => $this->referencia($pedido),
            'statement_descriptor' => mb_substr((string) config('app.name'), 0, 22),
        ];

        // Mercado Pago descarta las back_urls que no son https y no notifica a localhost: sin túnel,
        // el pago se confirma cuando el comprador vuelve a la pantalla de pago (pagoDelPedido)
        if ($this->retornoAutomatico()) {
            $datos['back_urls'] = ['success' => $retorno, 'failure' => $retorno, 'pending' => $retorno];
            $datos['auto_return'] = 'approved';
            $datos['notification_url'] = route('mercadopago.webhook');
        }

        $preferencia = (new PreferenceClient())->create($datos);
        $pedido->update(['mp_preference_id' => $preferencia->id]);

        return $preferencia;
    }

    // Si Mercado Pago puede volver solo al sitio (APP_URL público con https)
    public function retornoAutomatico(): bool
    {
        return $this->urlPublica(route('mercadopago.webhook'));
    }

    public function obtenerPago(int $paymentId): Payment
    {
        return (new PaymentClient())->get($paymentId);
    }

    // Id del pago del pedido según Mercado Pago: el aprobado si hay alguno, si no el último intento
    public function pagoDelPedido(Pedido|PedidoPublico $pedido): ?int
    {
        $busqueda = (new PaymentClient())->search(new MPSearchRequest(20, 0, [
            'external_reference' => $this->referencia($pedido),
            'sort' => 'date_created',
            'criteria' => 'desc',
        ]));

        $pagos = collect($busqueda->results ?? [])->map(fn ($pago) => (array) $pago);
        $pago = $pagos->firstWhere('status', 'approved') ?? $pagos->first();

        return $pago ? (int) $pago['id'] : null;
    }

    /**
     * Trae de Mercado Pago el pago del pedido (el indicado o, si no, el que encuentre) y lo guarda.
     * Devuelve true solo la primera vez que el pedido queda aprobado.
     */
    public function sincronizar(Pedido|PedidoPublico $pedido, ?int $paymentId = null): bool
    {
        $paymentId ??= $this->pagoDelPedido($pedido);
        if (!$paymentId) {
            return false;
        }

        $pago = $this->obtenerPago($paymentId);
        if ($pago->external_reference !== $this->referencia($pedido)) {
            return false;
        }

        $recienAprobado = $this->aplicarPago($pedido, $pago);
        $pedido->refresh();

        return $recienAprobado;
    }

    /**
     * Guarda en el pedido el estado del pago. Devuelve true solo la primera vez que el pedido queda aprobado,
     * para que quien llama haga lo que corresponde (mails, puntos) una única vez.
     */
    public function aplicarPago(Pedido|PedidoPublico $pedido, Payment $pago): bool
    {
        $estado = self::ESTADOS[$pago->status] ?? 'pendiente';

        if ($estado === 'aprobado' && abs((float) $pago->transaction_amount - round((float) $pedido->total, 2)) > 0.01) {
            report(new \RuntimeException("Pago {$pago->id} de Mercado Pago por {$pago->transaction_amount} no coincide con el total del pedido {$this->referencia($pedido)}"));
            return false;
        }

        return DB::transaction(function () use ($pedido, $pago, $estado) {
            /** @var Model $pedido */
            $pedido = $pedido->newQuery()->lockForUpdate()->find($pedido->id);

            // un intento fallido posterior no pisa un pago ya aprobado
            if ($pedido->pago_estado === 'aprobado' && $estado !== 'aprobado' && $pedido->mp_payment_id !== (string) $pago->id) {
                return false;
            }

            $yaAprobado = $pedido->pago_estado === 'aprobado';
            $pedido->update([
                'pago_estado' => $estado,
                'mp_payment_id' => (string) $pago->id,
                'mp_status_detail' => $pago->status_detail,
                'pagado_at' => $estado === 'aprobado' ? ($pedido->pagado_at ?? now()) : $pedido->pagado_at,
            ]);

            return !$yaAprobado && $estado === 'aprobado';
        });
    }

    // Valida el header x-signature de las notificaciones; sin clave configurada no se valida
    public function firmaValida(Request $request, string $dataId): bool
    {
        $secreto = (string) config('services.mercadopago.webhook_secret');
        if ($secreto === '') {
            return true;
        }

        $partes = collect(explode(',', (string) $request->header('x-signature')))
            ->mapWithKeys(function ($parte) {
                [$clave, $valor] = array_pad(explode('=', trim($parte), 2), 2, '');
                return [$clave => $valor];
            });

        if (!$partes->has('ts') || !$partes->has('v1')) {
            return false;
        }

        $manifiesto = 'id:' . strtolower($dataId) . ';request-id:' . $request->header('x-request-id') . ';ts:' . $partes['ts'] . ';';

        return hash_equals(hash_hmac('sha256', $manifiesto, $secreto), $partes['v1']);
    }

    private function urlPublica(string $url): bool
    {
        $host = (string) parse_url($url, PHP_URL_HOST);

        return str_starts_with($url, 'https://')
            && !in_array($host, ['localhost', '127.0.0.1', '::1'], true)
            && !str_ends_with($host, '.localhost')
            && !str_ends_with($host, '.test');
    }
}
