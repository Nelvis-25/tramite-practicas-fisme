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
        Schema::create('evaluacion_de_informes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('informe_de_practica_id')->constrained('informe_de_practicas')->onDelete('cascade');
            $table->foreignId('jurado_de_informe_id')->constrained('jurado_de_informes')->onDelete('cascade');
            $table->date('fecha_evaluacion')->nullable();
            $table->string('observacion', 900)->nullable(); 
            $table->tinyInteger('ronda')->nullable(); 
            $table->decimal('nota', 5, 2)->nullable();
            $table->enum('estado', ['Pendiente', 'Aprobado', 'Desaprobado', 'Observado','Evaluado']);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluacion_de_informes');
    }
};
