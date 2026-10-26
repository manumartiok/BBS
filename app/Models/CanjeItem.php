<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CanjeItem extends Model
{
    protected $fillable = [
        'canje_id',
        'producto_id',
        'codigo',
        'nombre',
        'puntos',
    ];

    protected $casts = [
        'puntos' => 'integer',
    ];

    public function canje()
    {
        return $this->belongsTo(Canje::class);
    }
}
