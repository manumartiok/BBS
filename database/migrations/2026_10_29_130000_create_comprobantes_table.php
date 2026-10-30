<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Comprobantes de Contabilium (facturas, notas de crédito...) para la cuenta corriente.
    // Los completa php artisan contabilium:comprobantes
    public function up(): void
    {
        Schema::create('comprobantes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contabilium_id')->unique();
            $table->unsignedBigInteger('cliente_id')->index(); // IdCliente de Contabilium = web_usuarios.contabilium_id
            $table->string('razon_social')->nullable();
            $table->string('tipo')->nullable();
            $table->string('numero')->nullable();
            $table->date('fecha_emision')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->decimal('importe', 15, 2)->default(0);
            $table->decimal('saldo', 15, 2)->default(0);
            $table->string('condicion_venta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comprobantes');
    }
};
