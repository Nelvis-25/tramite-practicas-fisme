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
        Schema::create('jurado_de_informes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('informe_de_practica_id')->constrained('informe_de_practicas')->onDelete('cascade');
            $table->foreignId('docente_id')->constrained('docentes')->onDelete('cascade');
            $table->enum('cargo', ['Secretario', 'Presidente', 'Vocal', 'Accesitario']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurado_de_informes');
    }
};
