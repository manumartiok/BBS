<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('web_usuarios', 'rol')) {
            Schema::table('web_usuarios', function (Blueprint $table) {
                $table->string('rol')->nullable()->after('contraseña');
            });
        }

        if (!Schema::hasColumn('web_usuarios', 'puntos')) {
            Schema::table('web_usuarios', function (Blueprint $table) {
                $table->unsignedInteger('puntos')->default(0)->after('rol');
            });
        }
    }

    public function down(): void
    {
        Schema::table('web_usuarios', function (Blueprint $table) {
            foreach (['rol', 'puntos'] as $columna) {
                if (Schema::hasColumn('web_usuarios', $columna)) {
                    $table->dropColumn($columna);
                }
            }
        });
    }
};
