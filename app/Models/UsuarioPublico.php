<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Usuarios de la parte pública de la web (se registran solos).
 * No son los clientes de la zona privada (WebUsuario), que vienen de la sincronización con Contabilium.
 */
class UsuarioPublico extends Authenticatable
{
    protected $table = 'usuarios_publicos';

    protected $fillable = [
        'nombre',
        'cuit',
        'email',
        'celular',
        'direccion',
        'localidad',
        'provincia',
        'codigo_postal',
        'password',
    ];

    protected $hidden = ['password'];

    public function pedidos()
    {
        return $this->hasMany(PedidoPublico::class, 'usuario_publico_id');
    }

    protected $casts = [
        'password' => 'hashed',
    ];
}
