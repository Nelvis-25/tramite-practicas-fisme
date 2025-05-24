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
        Schema::create('observacion_evaluacion_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluacion_plan_id')->constrained('evaluacion_plan_de_practicas')->onDelete('cascade');
            $table->string('observacion', 900)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('observacion_evaluacion_plans');
    }
};
