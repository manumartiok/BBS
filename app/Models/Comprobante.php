<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comprobante extends Model
{
    protected $fillable = [
        'contabilium_id',
        'cliente_id',
        'razon_social',
        'tipo',
        'numero',
        'fecha_emision',
        'fecha_vencimiento',
        'importe',
        'saldo',
        'condicion_venta',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'importe' => 'float',
        'saldo' => 'float',
    ];

    // Usuario web vinculado (web_usuarios.contabilium_id = IdCliente de Contabilium)
    public function usuario()
    {
        return $this->belongsTo(WebUsuario::class, 'cliente_id', 'contabilium_id');
    }

    // Lo que ya se pagó del comprobante
    public function getHaberAttribute(): float
    {
        return $this->importe - $this->saldo;
    }
}
