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
        Schema::create('docentes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('dni', 8)->unique();
            $table->string('codigo', 15)->unique();
            $table->string('telefono', 9);
            $table->string('email', 80)->unique();
            $table->string('especialidad', 100) ->nullable();
            $table->string('grado_academico', 100)->nullable();
            $table->string('tipo_contrato', 100)->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();
         
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('docentes');
    }
};
