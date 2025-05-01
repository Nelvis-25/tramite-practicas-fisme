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
        Schema::create('estudiantes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
        $table->string('dni', 8)->unique();
        $table->string('codigo', 10)->unique();
        $table->string('tipo_estudiante')->nullable();
        $table->string('ciclo')->nullable();
        $table->string('facultad', 250);
        $table->string('carrera', 120);
        $table->string('telefono', 9)->unique();
        $table->string('email', 125)->unique();
        $table->string('direccion', 250)->nullable();
        $table->boolean('estado')->default(true);
        $table->unsignedBigInteger('user_id');
        
        // Foreign keys
        
              
        $table->foreign('user_id')
              ->references('id')
              ->on('users')
              ->cascadeOnDelete()
              ->cascadeOnUpdate()
              ;
              


        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estudiantes');
    }
};
