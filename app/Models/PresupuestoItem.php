<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresupuestoItem extends Model
{
    protected $fillable = [
        'presupuesto_id',
        'producto_id',
        'codigo',
        'nombre',
        'cantidad',
        'precio_base',
        'precio_con_descuento',
        'subtotal',
    ];

    protected $casts = [
        'cantidad' => 'float',
        'precio_base' => 'float',
        'precio_con_descuento' => 'float',
        'subtotal' => 'float',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function presupuesto()
    {
        return $this->belongsTo(Presupuesto::class);
    }
}
