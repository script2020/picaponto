<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registos_horas', function (Blueprint $table) {
            $table->json('ficheiros')->nullable()->after('observacoes');
        });
    }

    public function down(): void
    {
        Schema::table('registos_horas', function (Blueprint $table) {
            $table->dropColumn('ficheiros');
        });
    }
};
