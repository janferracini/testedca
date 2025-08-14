<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('saldos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('unidades_id');
            $table->unsignedBigInteger('codigos_id');
            $table->decimal('saldo', 10, 2);
            $table->integer('ano');
            $table->timestamps();
            $table->foreign('unidades_id')->references('id')->on('unidades');
            $table->foreign('codigos_id')->references('id')->on('codigos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saldos');
    }
};
