<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    // Estados del pedido, en orden de avance: clave => [texto, color del texto en la web, color del badge en admin]
    public const ESTADOS = [
        'pendiente' => ['Pendiente', '#FF0000', 'bg-red-500'],
        'en_proceso' => ['En proceso', '#D47C00', 'bg-orange-500'],
        'enviado' => ['Enviado', '#006F9E', 'bg-sky-600'],
        'entregado' => ['Entregado', '#00920A', 'bg-green-500'],
    ];

    protected $fillable = [
        'usuario_id',
        'cliente_id',
        'estado',
        'entrega',
        'forma_pago',
        'mensaje',
        'fecha_entrega',
        'guia_transporte',
        'puntos_ganados',
        'subtotal',
        'iva',
        'total',
        'totales',
    ];

    protected $casts = [
        'subtotal' => 'float',
        'iva' => 'float',
        'total' => 'float',
        'totales' => 'array',
        'fecha_entrega' => 'date',
    ];

    public function getEstadoTextoAttribute(): string
    {
        return self::ESTADOS[$this->estado][0] ?? ucfirst(str_replace('_', ' ', (string) $this->estado));
    }

    public function getEstadoColorAttribute(): string
    {
        return self::ESTADOS[$this->estado][1] ?? '#000000';
    }

    public function getEstadoBadgeAttribute(): string
    {
        return self::ESTADOS[$this->estado][2] ?? 'bg-gray-400';
    }

    public function usuario()
    {
        return $this->belongsTo(WebUsuario::class, 'usuario_id');
    }

    public function cliente()
    {
        return $this->belongsTo(WebUsuario::class, 'cliente_id');
    }

    public function items()
    {
        return $this->hasMany(PedidoItem::class);
    }
}
