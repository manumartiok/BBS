<?php

use App\Support\Idiomas;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla única para los banners simples de cada sección (foto + texto).
 * Cada sección es una fila: seccion = 'nosotros', 'novedades', 'contacto', 'productos', ...
 *
 * Reemplaza a nosotro_banners, novedade_banners y contacto_banners:
 * si esas tablas existen, copia sus datos y las elimina.
 */
return new class extends Migration
{
    private array $anteriores = [
        'nosotro_banners'  => 'nosotros',
        'novedade_banners' => 'novedades',
        'contacto_banners' => 'contacto',
    ];

    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('seccion')->unique();
            $table->string('foto')->nullable();

            if (Idiomas::esMultiple()) {
                $table->json('texto')->nullable();
            } else {
                $table->string('texto')->nullable();
            }

            $table->timestamps();
        });

        foreach ($this->anteriores as $tabla => $seccion) {
            if (!Schema::hasTable($tabla)) {
                continue;
            }

            $anterior = DB::table($tabla)->first();

            if ($anterior) {
                $texto = $anterior->texto ?? null;

                if (Idiomas::esMultiple() && $texto !== null && json_decode($texto) === null) {
                    $texto = json_encode([Idiomas::principal() => $texto]);
                }

                DB::table('banners')->insert([
                    'seccion'    => $seccion,
                    'foto'       => $anterior->foto ?? null,
                    'texto'      => $texto,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            Schema::dropIfExists($tabla);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
