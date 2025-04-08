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
        Schema::create('plan_de_practicas', function (Blueprint $table) {
        $table->id();
        $table->foreignId('solicitud_id')->constrained('solicituds')->onDelete('cascade');
        $table->foreignId('comision_permanente_id')->constrained('comision_permanentes')->onDelete('cascade');
        $table->date('fecha_resolucion')->nullable();
        $table->date('fecha_entrega_a_docentes')->nullable();
        $table->date('fecha_sustentacion')->nullable();
        $table->string('estado', 50);
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_de_practicas');
    }
};
