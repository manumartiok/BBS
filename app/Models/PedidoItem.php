<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoItem extends Model
{
    protected $fillable = [
        'pedido_id',
        'producto_id',
        'medida_id',
        'codigo',
        'nombre',
        'seccion',
        'formacion',
        'color',
        'numerado',
        'presentacion',
        'cantidad',
        'cantidad_metros',
        'rollos',
        'bobinas',
        'metros',
        'precio_base',
        'precio_con_descuento',
        'subtotal',
        'carrito_item',
    ];

    protected $casts = [
        'cantidad' => 'float',
        'cantidad_metros' => 'float',
        'rollos' => 'float',
        'bobinas' => 'float',
        'metros' => 'float',
        'precio_base' => 'float',
        'precio_con_descuento' => 'float',
        'subtotal' => 'float',
        'carrito_item' => 'array',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
