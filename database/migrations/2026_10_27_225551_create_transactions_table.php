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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            // Guarda dinheiro com duas casas decimais, em vez de usar número de ponto flutuante.
            $table->decimal('amount', 12, 2);
            $table->string('type', 12);
            $table->date('date');
            // Uma categoria usada não pode ser apagada, para manter o histórico íntegro.
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Ajuda as consultas que filtram lançamentos por período e tipo.
            $table->index(['date', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
