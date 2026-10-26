<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('productos', 'puntos_compra')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->unsignedInteger('puntos_compra')->nullable()->after('puntos');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('productos', 'puntos_compra')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->dropColumn('puntos_compra');
            });
        }
    }
};
