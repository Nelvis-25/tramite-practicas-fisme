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
        Schema::create('informe_practicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_informe_id')->constrained('solicitud_informes')->onDelete('cascade');
            $table->foreignId('jurado_informe_id')->constrained('jurado_informes')->onDelete('cascade');
            $table->date('fecha_resolucion')->nullable();
            $table->date('fecha_entrega_a_docentes')->nullable();
            $table->datetime('fecha_sustentacion')->nullable();
            $table->string('observaciones', 200)->nullable();
            $table->string('estado', 50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('informe_practicas');
    }
};
