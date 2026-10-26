<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos_publicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_publico_id')->constrained('usuarios_publicos')->cascadeOnDelete();
            $table->string('estado')->default('pendiente');
            $table->string('envio');
            $table->string('envio_codigo_postal')->nullable();
            $table->string('forma_pago');
            $table->string('nombre');
            $table->string('cuit');
            $table->string('email');
            $table->string('celular');
            $table->string('direccion');
            $table->string('localidad');
            $table->string('provincia');
            $table->string('codigo_postal');
            $table->text('mensaje')->nullable();
            $table->json('entrega_otra')->nullable();
            $table->decimal('total', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('pedido_publico_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_publico_id')->constrained('pedidos_publicos')->cascadeOnDelete();
            $table->foreignId('producto_id')->nullable()->constrained('productos')->nullOnDelete();
            $table->string('codigo')->nullable();
            $table->string('nombre')->nullable();
            $table->unsignedInteger('cantidad')->default(1);
            $table->decimal('precio', 14, 2)->default(0);
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_publico_items');
        Schema::dropIfExists('pedidos_publicos');
    }
};
