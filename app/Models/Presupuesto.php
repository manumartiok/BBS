<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presupuesto extends Model
{
    // Días de vigencia desde la creación
    public const VIGENCIA_DIAS = 3;

    // Estados (se calculan por la fecha de vencimiento): clave => [texto, color del texto]
    public const ESTADOS = [
        'pendiente' => ['Pendiente', '#00920A'],
        'vencido' => ['Vencido', '#FF0000'],
    ];

    protected $fillable = [
        'usuario_id',
        'entrega',
        'forma_pago',
        'mensaje',
        'fecha_vencimiento',
        'subtotal',
        'iva',
        'total',
        'totales',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'subtotal' => 'float',
        'iva' => 'float',
        'total' => 'float',
        'totales' => 'array',
    ];

    // Pendiente hasta el final del día de vencimiento; después, vencido
    public function getEstadoAttribute(): string
    {
        return $this->fecha_vencimiento->copy()->endOfDay()->isPast() ? 'vencido' : 'pendiente';
    }

    public function getEstadoTextoAttribute(): string
    {
        return self::ESTADOS[$this->estado][0];
    }

    public function getEstadoColorAttribute(): string
    {
        return self::ESTADOS[$this->estado][1];
    }

    public function usuario()
    {
        return $this->belongsTo(WebUsuario::class, 'usuario_id');
    }

    public function items()
    {
        return $this->hasMany(PresupuestoItem::class);
    }
}
