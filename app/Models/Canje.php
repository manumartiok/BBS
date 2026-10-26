<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Canje extends Model
{
    // Estados del canje, en orden de avance: clave => [texto, color del texto en la web, color del badge en admin]
    public const ESTADOS = [
        'pendiente' => ['Pendiente', '#FF0000', 'bg-red-500'],
        'enviado' => ['Enviado', '#006F9E', 'bg-sky-600'],
        'entregado' => ['Entregado', '#00920A', 'bg-green-500'],
    ];

    public const ENVIOS = [
        'mercado_flex' => 'Mercado Flex',
        'transporte_terminal' => 'Transporte a la terminal',
        'retiro_local' => 'Retiro en local',
    ];

    protected $fillable = [
        'usuario_id',
        'envio',
        'envio_codigo_postal',
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
        'puntos',
        'estado',
        'guia_transporte',
    ];

    protected $casts = [
        'entrega_otra' => 'array',
        'puntos' => 'integer',
    ];

    public function getEstadoTextoAttribute(): string
    {
        return self::ESTADOS[$this->estado][0] ?? ucfirst((string) $this->estado);
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

    public function items()
    {
        return $this->hasMany(CanjeItem::class);
    }
}
