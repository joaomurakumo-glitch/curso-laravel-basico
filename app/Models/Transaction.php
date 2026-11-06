<?php

namespace App\Models;

use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

// Limita quais campos podem ser preenchidos em operações de criação ou atualização.
#[Fillable(['description', 'amount', 'type', 'date', 'category_id', 'notes'])]
class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    // Em vez de apagar a linha do banco, marca a data de exclusão para permitir restauração.
    use SoftDeletes;

    /**
     * Retorna a categoria vinculada a este lançamento.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Converte valores e datas para tipos adequados ao uso na aplicação.
     *
     * Isso permite trabalhar com valor decimal e data sem conversões manuais em cada tela.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'date' => 'date',
        ];
    }
}
