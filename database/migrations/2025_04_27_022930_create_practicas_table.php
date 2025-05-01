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
        Schema::create('practicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->nullable()->constrained('estudiantes')->onDelete('set null');
            $table->foreignId('docente_id')->nullable()->constrained('docentes')->onDelete('set null'); // asesor
            $table->foreignId('solicitude_id')->nullable()->constrained('solicitudes')->onDelete('set null');
            $table->foreignId('plan_practica_id')->nullable()->constrained('plan_practicas')->onDelete('set null');
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->onDelete('set null');
            $table->string('estado', 50);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('practicas');
    }
};
