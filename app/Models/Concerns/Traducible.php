<?php

namespace App\Models\Concerns;

use App\Support\Idiomas;

/**
 * Para modelos con campos traducibles.
 *
 * En el modelo:
 *     use Traducible;
 *     protected array $traducibles = ['titulo', 'texto'];
 *
 * En las vistas:
 *     {{ $item->t('titulo') }}   -> texto en el idioma actual
 *
 * Si el sitio tiene un solo idioma, t() devuelve el valor tal cual.
 */
trait Traducible
{
    public function initializeTraducible(): void
    {
        if (!Idiomas::esMultiple()) {
            return;
        }

        foreach ($this->traducibles ?? [] as $campo) {
            $this->mergeCasts([$campo => 'array']);
        }
    }

    public function t(string $campo, ?string $idioma = null)
    {
        return Idiomas::texto($this->{$campo}, $idioma);
    }
}
