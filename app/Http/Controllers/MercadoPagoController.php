<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;
use Throwable;

// Notificaciones de pagos de Mercado Pago (webhook), para los pedidos de los dos carritos
class MercadoPagoController extends Controller
{
    // Responde 204 aunque el pago no sea de un pedido, así Mercado Pago no reintenta
    public function webhook(Request $request, MercadoPagoService $mercadoPago)
    {
        $tipo = $request->input('type') ?? $request->query('topic');
        $paymentId = (string) ($request->query('data_id') ?? $request->input('data.id') ?? $request->query('id'));

        if ($tipo !== 'payment' || !ctype_digit($paymentId)) {
            return response()->noContent();
        }
        if (!$mercadoPago->firmaValida($request, $paymentId)) {
            return response()->noContent(401);
        }

        try {
            $pago = $mercadoPago->obtenerPago((int) $paymentId);
            $pedido = $mercadoPago->pedidoDeReferencia($pago->external_reference);

            if ($pedido && $mercadoPago->aplicarPago($pedido, $pago)) {
                $pedido instanceof Pedido
                    ? app(PedidoController::class)->alAprobarsePago($pedido)
                    : app(CarritoPublicoController::class)->alAprobarsePago($pedido);
            }
        } catch (Throwable $e) {
            report($e);
        }

        return response()->noContent();
    }
}
