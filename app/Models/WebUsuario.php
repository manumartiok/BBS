<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class WebUsuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'web_usuarios';

    // Roles de la zona privada: clave => texto
    public const ROLES = [
        'distribuidor' => 'Distribuidor',
        'gremio' => 'Gremio',
    ];

protected $fillable = [
    'usuario', 'email', 'contraseña', 'rol', 'puntos', 'active',
    'empresa', 'razon_social', 'cuit', 'telefono', 'domicilio',
    'codigo_postal', 'localidad', 'provincia', 'pais',
    'descuento1', 'descuento2', 'descuento3', 'descuento4', 'descuento5',
];


    protected $casts = [
        'puntos' => 'integer',
        'descuento1' => 'float',
        'descuento2' => 'float',
        'descuento3' => 'float',
        'descuento4' => 'float',
        'descuento5' => 'float',
    ];

    public function getAuthPassword()
    {
        return $this->contraseña;
    }

    /**
     * Devuelve array solo con los descuentos mayores a 0
     */
    public function getDescuentosArray(): array
    {
        return array_values(array_filter([
            $this->descuento1,
            $this->descuento2,
            $this->descuento3,
            $this->descuento4,
            $this->descuento5,
        ]));
    }

    /**
     * Calcula el descuento efectivo total en cascada (como porcentaje)
     */
    public function getDescuentoEfectivo(): float
    {
        $descuentos = $this->getDescuentosArray();
        if (empty($descuentos)) return 0;

        $factor = 1;
        foreach ($descuentos as $d) {
            $factor *= (1 - $d / 100);
        }

        return round((1 - $factor) * 100, 2);
    }
}