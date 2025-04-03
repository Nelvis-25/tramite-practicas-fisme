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
        Schema::create('solicituds', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 700);
            $table->foreignId('estudiante_id')->constrained('estudiantes');
            $table->foreignId('linea_investigacion_id')->constrained('linea_investigacions');
            $table->foreignId('asesor_id')->constrained('docentes');
            $table->string('solicitud')->nullable(); 
            $table->string('constancia')->nullable();
            $table->string('informe')->nullable();
            $table->string('carta_presentacion')->nullable();
            $table->string('comprobante_pago')->nullable();
            $table->enum('estado', ['Pendiente', 'Validado', 'Rechazado']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicituds');
    }
};
