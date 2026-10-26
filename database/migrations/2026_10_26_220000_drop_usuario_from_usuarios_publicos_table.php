<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// El acceso público entra con el email o con el nombre del registro: no hay campo "usuario".
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('usuarios_publicos', 'usuario')) {
            Schema::table('usuarios_publicos', function (Blueprint $table) {
                $table->dropUnique(['usuario']);
                $table->dropColumn('usuario');
            });
        }
    }

    public function down(): void
    {
        // sin vuelta atrás: el campo "usuario" ya no existe
    }
};
