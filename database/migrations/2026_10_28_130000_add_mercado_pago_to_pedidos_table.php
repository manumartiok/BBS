<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Solo se completan en los pedidos pagados con Mercado Pago
        Schema::table('pedidos', function (Blueprint $table) {
            if (!Schema::hasColumn('pedidos', 'pago_estado')) {
                $table->string('pago_estado')->nullable()->after('forma_pago');
            }
            if (!Schema::hasColumn('pedidos', 'mp_preference_id')) {
                $table->string('mp_preference_id')->nullable()->after('pago_estado');
            }
            if (!Schema::hasColumn('pedidos', 'mp_payment_id')) {
                $table->string('mp_payment_id')->nullable()->after('mp_preference_id');
            }
            if (!Schema::hasColumn('pedidos', 'mp_status_detail')) {
                $table->string('mp_status_detail')->nullable()->after('mp_payment_id');
            }
            if (!Schema::hasColumn('pedidos', 'pagado_at')) {
                $table->timestamp('pagado_at')->nullable()->after('mp_status_detail');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            foreach (['pago_estado', 'mp_preference_id', 'mp_payment_id', 'mp_status_detail', 'pagado_at'] as $columna) {
                if (Schema::hasColumn('pedidos', $columna)) {
                    $table->dropColumn($columna);
                }
            }
        });
    }
};
