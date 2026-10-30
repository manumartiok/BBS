<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Null en los pedidos anteriores: su total no incluía IVA
        Schema::table('pedidos_publicos', function (Blueprint $table) {
            if (!Schema::hasColumn('pedidos_publicos', 'subtotal')) {
                $table->decimal('subtotal', 14, 2)->nullable()->after('entrega_otra');
            }
            if (!Schema::hasColumn('pedidos_publicos', 'iva_105')) {
                $table->decimal('iva_105', 14, 2)->nullable()->after('subtotal');
            }
            if (!Schema::hasColumn('pedidos_publicos', 'iva_21')) {
                $table->decimal('iva_21', 14, 2)->nullable()->after('iva_105');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pedidos_publicos', function (Blueprint $table) {
            foreach (['subtotal', 'iva_105', 'iva_21'] as $columna) {
                if (Schema::hasColumn('pedidos_publicos', $columna)) {
                    $table->dropColumn($columna);
                }
            }
        });
    }
};
