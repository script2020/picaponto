<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::table('registos_horas', function (Blueprint $table) {
            $table->dateTime('entrada')->change();
            $table->dateTime('saida')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('registos_horas', function (Blueprint $table) {
            $table->timestamp('entrada')->change();
            $table->timestamp('saida')->nullable()->change();
        });
    }
};
