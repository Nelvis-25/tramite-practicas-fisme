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
        Schema::create('docentes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('dni', 8)->unique();
            $table->string('codigo', 15)->unique();
            $table->string('telefono', 9);
            $table->string('email', 100)->unique();
            $table->string('especialidad', 100)->nullable();
            $table->string('grado_academico', 100)->nullable();
            $table->string('tipo_contrato', 100)->nullable();
            $table->boolean('estado')->default(true);
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('cargo_id');
            
            $table->foreign('cargo_id')
            ->references('id')
            ->on('cargos')
            ->cascadeOnDelete()
            ->cascadeOnUpdate()
            ;

            
      $table->foreign('user_id')
            ->references('id')
            ->on('users')
            ->cascadeOnDelete()
            ->cascadeOnUpdate()
            ;
            
            $table->timestamps();
        });
    }

  
    public function down(): void
    {
        Schema::dropIfExists('docentes');
    }
};
