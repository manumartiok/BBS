<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presupuestos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('web_usuarios')->cascadeOnDelete();
            $table->string('entrega')->nullable();
            $table->string('forma_pago')->nullable();
            $table->text('mensaje')->nullable();
            $table->date('fecha_vencimiento');
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('iva', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->json('totales')->nullable();
            $table->timestamps();
        });

        Schema::create('presupuesto_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presupuesto_id')->constrained('presupuestos')->cascadeOnDelete();
            $table->foreignId('producto_id')->nullable()->constrained('productos')->nullOnDelete();
            $table->string('codigo')->nullable();
            $table->string('nombre')->nullable();
            $table->decimal('cantidad', 14, 2)->default(0);
            $table->decimal('precio_base', 14, 2)->default(0);
            $table->decimal('precio_con_descuento', 14, 2)->default(0);
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presupuesto_items');
        Schema::dropIfExists('presupuestos');
    }
};
