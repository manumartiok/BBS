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
    ];
}
