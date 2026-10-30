<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('productos', 'iva')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->decimal('iva', 5, 2)->nullable()->after('precio');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('productos', 'iva')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->dropColumn('iva');
            });
        }
    }
};
