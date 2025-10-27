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
        Schema::create('gestores', function (Blueprint $table) {
            $table->unsignedBigInteger('id_gestor')->primary();
            $table->string('nome', 100);
            $table->string('email', 120)->unique();
            $table->string('senha', 255);
            $table->char('cnpj', 14)->unique();

            $table->foreign('id_gestor')
                ->references('id_usuario')
                ->on('usuarios')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gestores');
    }
};
