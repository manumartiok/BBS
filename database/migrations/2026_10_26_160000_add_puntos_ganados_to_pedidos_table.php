<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('pedidos', 'puntos_ganados')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->unsignedInteger('puntos_ganados')->default(0)->after('total');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pedidos', 'puntos_ganados')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->dropColumn('puntos_ganados');
            });
        }
    }
};
