<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contacto extends Model
{
    protected $fillable = [
        'ubicacion',
        'direccion',
        'mail',
        'telefono',
        'instagram',
        'facebook',
        'youtube',
        'linkedin',
        'whatssap',
        'retiro_local_texto',
    ];

    // Texto que se ve debajo de "Retiro en local". Se edita desde el histórico de pedidos del admin;
    // mientras no se cargue, se arma con el horario y los datos de contacto.
    public function getRetiroLocalDetalleAttribute(): string
    {
        if (filled($this->retiro_local_texto)) {
            return $this->retiro_local_texto;
        }

        return collect(['Lu - Vi 8:30 - 17:30 / Sa 9:00 - 13:00', $this->direccion, $this->whatssap, $this->mail])
            ->filter()
            ->implode("\n");
    }

    public static function textoRetiroLocal(): string
    {
        return (self::first() ?? new self)->retiro_local_detalle;
    }
}
