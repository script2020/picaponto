<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('horario_semanas');

        Schema::create('horario_semanas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('data');
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fim')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'data']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('horario_semanas');

        Schema::create('horario_semanas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('dia_semana');
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fim')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'dia_semana']);
        });
    }
};
