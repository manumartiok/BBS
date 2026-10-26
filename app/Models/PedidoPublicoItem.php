<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoPublicoItem extends Model
{
    protected $table = 'pedido_publico_items';

    protected $fillable = [
        'pedido_publico_id',
        'producto_id',
        'codigo',
        'nombre',
        'cantidad',
        'precio',
        'subtotal',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio' => 'float',
        'subtotal' => 'float',
    ];

    public function pedido()
    {
        return $this->belongsTo(PedidoPublico::class, 'pedido_publico_id');
    }
}
