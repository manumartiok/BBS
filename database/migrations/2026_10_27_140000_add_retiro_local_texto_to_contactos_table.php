<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('contactos', 'retiro_local_texto')) {
            Schema::table('contactos', function (Blueprint $table) {
                $table->text('retiro_local_texto')->nullable()->after('whatssap');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('contactos', 'retiro_local_texto')) {
            Schema::table('contactos', function (Blueprint $table) {
                $table->dropColumn('retiro_local_texto');
            });
        }
    }
};
