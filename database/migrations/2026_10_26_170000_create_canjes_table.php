<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('canjes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('web_usuarios')->cascadeOnDelete();
            $table->string('envio');
            $table->string('envio_codigo_postal')->nullable();
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
            $table->unsignedInteger('puntos');
            $table->timestamps();
        });

        Schema::create('canje_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('canje_id')->constrained('canjes')->cascadeOnDelete();
            $table->foreignId('producto_id')->nullable()->constrained('productos')->nullOnDelete();
            $table->string('codigo')->nullable();
            $table->string('nombre')->nullable();
            $table->unsignedInteger('puntos');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canje_items');
        Schema::dropIfExists('canjes');
    }
};
