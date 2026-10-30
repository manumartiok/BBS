<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Marca;
use App\Models\Cotizacion;
use App\Models\ProductoFoto;

class Producto extends Model
{
    protected $fillable = [
        'marca_id',
        'orden',
        'nombre',
        'codigo',
        'stock_fisico',
        'stock_transito',
        'descripcion',
        'precio',
        'iva',
        'puntos',
        'puntos_compra',
        'caracteristicas',
        'foto',
        'active',
        'destacado',
        'oferta',
        'mas_vendido',
        'descubre_mas',
    ];

    protected $casts = [
        'caracteristicas' => 'array',
        'active' => 'boolean',
        'destacado' => 'boolean',
        'oferta' => 'boolean',
        'mas_vendido' => 'boolean',
        'descubre_mas' => 'boolean',
    ];

    // Foto del producto; sin foto (o con el viejo valor '/default.png') se usa public/assets/img/default.png
    public function getFotoUrlAttribute(): string
    {
        $sinFoto = empty($this->foto) || $this->foto === '/default.png';

        return asset($sinFoto ? 'assets/img/default.png' : $this->foto);
    }

    // La columna precio está en dólares; esto es el precio en pesos a la cotización vigente.
    // Null si el producto no tiene precio o todavía no se sincronizó el dólar.
    public function getPrecioPesosAttribute(): ?float
    {
        $dolar = Cotizacion::dolar();

        if (is_null($this->precio) || is_null($dolar)) {
            return null;
        }

        return round($this->precio * $dolar, 2);
    }

    // Alícuota de IVA que se aplica en el carrito: 10,5% o 21%.
    // Sin IVA cargado (o con otro valor) se usa 21%.
    public function getAlicuotaIvaAttribute(): float
    {
        return abs((float) $this->iva - 10.5) < 0.01 ? 10.5 : 21.0;
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class);
    }

    public function fotos()
    {
        return $this->hasMany(ProductoFoto::class, 'productos_id')->orderBy('orden');
    }
}
