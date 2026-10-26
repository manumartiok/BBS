<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cotizacion extends Model
{
    protected $table = 'cotizaciones';

    protected $fillable = [
        'moneda',
        'valor',
        'fecha',
    ];

    protected $casts = [
        'valor' => 'float',
        'fecha' => 'date',
    ];

    // Se consulta una sola vez por request: se usa para convertir el precio de cada producto
    private static ?float $dolar = null;
    private static bool $dolarCargado = false;

    protected static function booted(): void
    {
        static::saved(fn () => static::$dolarCargado = false);
    }

    // Última cotización guardada del dólar oficial (null si todavía no se sincronizó)
    public static function dolar(): ?float
    {
        if (!static::$dolarCargado) {
            static::$dolar = static::where('moneda', 'USD')->value('valor');
            static::$dolarCargado = true;
        }

        return static::$dolar;
    }
}
