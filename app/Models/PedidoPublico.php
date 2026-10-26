<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Pedido de un usuario público (distinto de Pedido, que es de los clientes de la zona privada)
class PedidoPublico extends Model
{
    protected $table = 'pedidos_publicos';

    protected $fillable = [
        'usuario_publico_id',
        'estado',
        'envio',
        'envio_codigo_postal',
        'forma_pago',
        'nombre',
        'cuit',
        'email',
        'celular',
        'direccion',
        'localidad',
        'provincia',
        'codigo_postal',
        'mensaje',
        'entrega_otra',
        'total',
    ];

    protected $casts = [
        'entrega_otra' => 'array',
        'total' => 'float',
    ];

    public const ENVIOS = [
        'mercado_flex' => 'Mercado Flex',
        'transporte_terminal' => 'Transporte a la terminal',
        'retiro_local' => 'Retiro en local',
    ];

    public const PAGOS = [
        'mercado_pago' => 'Mercado Pago',
        'transferencia' => 'Transferencia bancaria',
        'efectivo' => 'Efectivo',
    ];

    // El estado usa los mismos valores que los pedidos de la zona privada (Pedido::ESTADOS)
    public function getEstadoTextoAttribute(): string
    {
        return Pedido::ESTADOS[$this->estado][0] ?? ucfirst(str_replace('_', ' ', (string) $this->estado));
    }

    public function getEstadoBadgeAttribute(): string
    {
        return Pedido::ESTADOS[$this->estado][2] ?? 'bg-gray-400';
    }

    public function usuario()
    {
        return $this->belongsTo(UsuarioPublico::class, 'usuario_publico_id');
    }

    public function items()
    {
        return $this->hasMany(PedidoPublicoItem::class);
    }
}
