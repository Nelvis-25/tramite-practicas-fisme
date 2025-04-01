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
        Schema::create('personal_universitarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 60);
            $table->string('dni', 8)->unique()->nullable();
            $table->string('codigo', 15)->unique();
            $table->string('telefono', 9);
            $table->string('email', 100)->unique();
            $table->string('cargo', 100);
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_universitarios');
    }
};
