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
        Schema::create('observacion_evaluacions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluacion_de_informe_id')->constrained('evaluacion_de_informes')->onDelete('cascade');
            $table->string('observacion', 900)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('observacion_evaluacions');
    }
};
