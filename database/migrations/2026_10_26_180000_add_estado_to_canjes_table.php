<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('canjes', function (Blueprint $table) {
            if (!Schema::hasColumn('canjes', 'estado')) {
                $table->string('estado')->default('pendiente')->after('puntos');
            }
            if (!Schema::hasColumn('canjes', 'guia_transporte')) {
                $table->string('guia_transporte')->nullable()->after('estado');
            }
        });
    }

    public function down(): void
    {
        Schema::table('canjes', function (Blueprint $table) {
            foreach (['estado', 'guia_transporte'] as $columna) {
                if (Schema::hasColumn('canjes', $columna)) {
                    $table->dropColumn($columna);
                }
            }
        });
    }
};
