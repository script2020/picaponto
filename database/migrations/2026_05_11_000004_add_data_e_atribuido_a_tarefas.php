<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tarefas', function (Blueprint $table) {
            $table->date('data')->nullable()->after('user_id');
            $table->foreignId('atribuido_a_id')->nullable()->after('data')
                  ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tarefas', function (Blueprint $table) {
            $table->dropForeign(['atribuido_a_id']);
            $table->dropColumn(['data', 'atribuido_a_id']);
        });
    }
};
