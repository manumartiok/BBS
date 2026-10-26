<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Producto en el carrito de un usuario público
class CarritoPublicoItem extends Model
{
    protected $fillable = [
        'usuario_publico_id',
        'producto_id',
        'cantidad',
    ];

    protected $casts = [
        'cantidad' => 'integer',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
