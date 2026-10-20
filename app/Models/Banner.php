<?php

namespace App\Models;

use App\Models\Concerns\Traducible;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use Traducible;

    protected $fillable = [
        'seccion',
        'foto',
        'texto',
    ];

    protected array $traducibles = ['texto'];

    /**
     * Banner de una sección. Si todavía no existe, lo crea vacío.
     *
     *     $banner = Banner::seccion('nosotros');
     *     {{ $banner->foto }}  {{ $banner->t('texto') }}
     */
    public static function seccion(string $seccion): self
    {
        return static::firstOrCreate(['seccion' => $seccion]);
    }
}
