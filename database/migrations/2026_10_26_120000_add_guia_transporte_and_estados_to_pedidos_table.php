<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('pedidos', 'guia_transporte')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->string('guia_transporte')->nullable()->after('fecha_entrega');
            });
        }

        // Estados anteriores: en_preparacion -> pendiente
        DB::table('pedidos')->where('estado', 'en_preparacion')->update(['estado' => 'pendiente']);
    }

    public function down(): void
    {
        DB::table('pedidos')->whereIn('estado', ['pendiente', 'en_proceso', 'enviado'])->update(['estado' => 'en_preparacion']);

        if (Schema::hasColumn('pedidos', 'guia_transporte')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->dropColumn('guia_transporte');
            });
        }
    }
};
