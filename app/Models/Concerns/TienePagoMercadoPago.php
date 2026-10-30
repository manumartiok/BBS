<?php

namespace App\Models\Concerns;

// Estado del pago con Mercado Pago (columnas pago_estado, mp_*, pagado_at). Lo usan Pedido y PedidoPublico;
// los demás medios de pago dejan pago_estado en null
trait TienePagoMercadoPago
{
    // [texto, clase del badge]
    public const PAGO_ESTADOS = [
        'pendiente' => ['Pago pendiente', 'bg-yellow-500'],
        'aprobado' => ['Pagado', 'bg-green-500'],
        'rechazado' => ['Pago rechazado', 'bg-red-500'],
        'cancelado' => ['Pago cancelado', 'bg-gray-400'],
        'devuelto' => ['Pago devuelto', 'bg-gray-400'],
    ];

    public function getPagoEstadoTextoAttribute(): ?string
    {
        return $this->pago_estado ? (self::PAGO_ESTADOS[$this->pago_estado][0] ?? $this->pago_estado) : null;
    }

    public function getPagoEstadoBadgeAttribute(): string
    {
        return self::PAGO_ESTADOS[$this->pago_estado][1] ?? 'bg-gray-400';
    }

    // Aviso a mostrar en la pantalla de pago: 'pendiente' (pago en curso), 'rechazado' o null
    public function getResultadoPagoAttribute(): ?string
    {
        return match ($this->pago_estado) {
            'pendiente' => $this->mp_payment_id ? 'pendiente' : null,
            'rechazado', 'cancelado' => 'rechazado',
            default => null,
        };
    }

    // Se puede (re)intentar pagar: Mercado Pago sin pago aprobado ni en curso
    public function getPuedePagarMercadoPagoAttribute(): bool
    {
        return $this->forma_pago === 'mercado_pago'
            && ($this->pago_estado === 'rechazado' || $this->pago_estado === 'cancelado' || ($this->pago_estado === 'pendiente' && !$this->mp_payment_id));
    }
}
