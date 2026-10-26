<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marca_id')->nullable()->constrained('marcas')->nullOnDelete();
            $table->string('orden')->nullable();
            $table->string('nombre')->nullable();
            $table->string('codigo')->nullable();
            $table->string('stock_fisico')->nullable();
            $table->string('stock_transito')->nullable();
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2)->nullable();
            $table->json('caracteristicas')->nullable();
            $table->string('foto')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('destacado')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
