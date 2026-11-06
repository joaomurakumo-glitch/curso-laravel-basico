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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // O tipo diferencia categorias de receita das categorias de despesa.
            $table->string('type', 12);
            $table->string('color', 7)->default('#687B68');
            $table->timestamps();

            // Evita repetir o mesmo nome dentro do mesmo tipo de categoria.
            $table->unique(['name', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
