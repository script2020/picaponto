<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('horario_semanas', function (Blueprint $table) {
            $table->boolean('reposicao')->default(false)->after('ativo');
            $table->date('data_reposicao')->nullable()->after('reposicao');
        });
    }

    public function down(): void
    {
        Schema::table('horario_semanas', function (Blueprint $table) {
            $table->dropColumn(['reposicao', 'data_reposicao']);
        });
    }
};
