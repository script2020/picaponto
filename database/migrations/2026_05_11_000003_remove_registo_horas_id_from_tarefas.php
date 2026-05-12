<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tarefas', function (Blueprint $table) {
            $table->dropForeign(['registo_horas_id']);
            $table->dropColumn('registo_horas_id');
        });
    }

    public function down(): void
    {
        Schema::table('tarefas', function (Blueprint $table) {
            $table->foreignId('registo_horas_id')->nullable()->constrained('registos_horas')->nullOnDelete();
        });
    }
};
