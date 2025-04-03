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
        Schema::create('integrante_comisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('docente_id')->constrained('docentes');
            $table->foreignId('comision_permanente_id')->constrained('comision_permanentes');
            $table->enum('cargo', ['Secretario', 'Presidente', 'Vocal', 'Accesitario']);
            $table->timestamps();
            
            // Evita duplicados
            $table->unique(['docente_id', 'comision_permanente_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integrante_comisions');
    }
};
