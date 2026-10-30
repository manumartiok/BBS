<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('web_usuarios', 'contabilium_id')) {
            Schema::table('web_usuarios', function (Blueprint $table) {
                // Id del cliente en Contabilium (lo completa php artisan contabilium:clientes)
                $table->unsignedBigInteger('contabilium_id')->nullable()->index()->after('id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('web_usuarios', 'contabilium_id')) {
            Schema::table('web_usuarios', function (Blueprint $table) {
                $table->dropIndex(['contabilium_id']);
                $table->dropColumn('contabilium_id');
            });
        }
    }
};
