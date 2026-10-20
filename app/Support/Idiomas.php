<?php

namespace App\Support;

class Idiomas
{
    public const DISPONIBLES = [
        'es' => 'Español',
        'en' => 'Inglés',
        'it' => 'Italiano',
        'fr' => 'Francés',
        'pt' => 'Portugués',
        'de' => 'Alemán',
    ];

    /**
     * Idiomas configurados en config/sitio.php (solo los válidos).
     */
    public static function todos(): array
    {
        $idiomas = array_values(array_intersect(
            (array) config('sitio.idiomas', ['es']),
            array_keys(self::DISPONIBLES)
        ));

        return $idiomas ?: ['es'];
    }

    public static function principal(): string
    {
        return self::todos()[0];
    }

    public static function esMultiple(): bool
    {
        return count(self::todos()) > 1;
    }

    public static function nombre(string $codigo): string
    {
        return self::DISPONIBLES[$codigo] ?? strtoupper($codigo);
    }

    /**
     * Devuelve el texto en el idioma actual.
     * Si el valor no es un array por idioma, lo devuelve tal cual.
     * Si falta la traducción, usa el idioma principal.
     */
    public static function texto($valor, ?string $idioma = null)
    {
        if (!is_array($valor)) {
            return $valor;
        }

        $idioma ??= app()->getLocale();

        return $valor[$idioma] ?? $valor[self::principal()] ?? null;
    }
}
