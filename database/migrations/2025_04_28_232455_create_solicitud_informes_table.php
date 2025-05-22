<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('solicitud_informes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
            $table->foreignId('practica_id')->constrained('practicas')->onDelete('cascade');
            $table->string('informe')->nullable();
            $table->string('solicitud')->nullable();
            $table->string('resolucion')->nullable();

            $table->enum('estado', [ 'Pendiente', 'Validado', 'Aceptado', 'Rechazado', 'Jurado asignado']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitud_informes');
    }
};
