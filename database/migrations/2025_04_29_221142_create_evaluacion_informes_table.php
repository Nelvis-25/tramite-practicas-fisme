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
        Schema::create('evaluacion_informes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('informe_practica_id')->constrained('informe_practicas')->onDelete('cascade');
            $table->foreignId('integrante_id')->constrained('integrantes')->onDelete('cascade');
            $table->date('fecha_evaluacion')->nullable();
            $table->string('observacion', 600)->nullable(); 
            $table->enum('estado', ['Pendiente', 'Aprobado', 'Desaprobado', 'Observado']);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluacion_informes');
    }
};
