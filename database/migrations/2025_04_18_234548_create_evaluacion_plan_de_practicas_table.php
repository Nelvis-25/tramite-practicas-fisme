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
        Schema::create('evaluacion_plan_de_practicas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plan_practica_id');
            $table->unsignedBigInteger('integrante_comision_id');
            $table->enum('estado', ['Pendiente', 'Aprobado', 'Desaprobado', 'Observado']);
            $table->string('observacion', 600)->nullable(); 
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->foreign('integrante_comision_id')->references('id')->on('integrante_comisions')->onDelete('cascade');
            // Llaves foráneas
            $table->foreign('plan_practica_id')->references('id')->on('plan_practicas')->onDelete('cascade');
            
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluacion_plan_de_practicas');
    }
};
