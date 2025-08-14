<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lancamentos', function (Blueprint $table) {
            $table->id();
            $table->string('objeto');
            $table->string('processo')->unique();
            $table->decimal('valor', 10, 2);
            $table->enum('status', ['ativo', 'cancelado', 'reservado']);
            $table->unsignedBigInteger('secretaria_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('codigo_id');
            $table->timestamps();

            $table->foreign('secretaria_id')->references('id')->on('secretarias');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('codigo_id')->references('id')->on('codigos');
        });

        DB::statement('ALTER TABLE lancamentos ADD CONSTRAINT check_processo_format CHECK (processo ~ \'^[0-9]{4}/[0-9]{2}/[0-9]+$\')');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lancamentos');
    }
};
