<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Permite preencher com segurança apenas os campos usados para cadastrar uma categoria.
#[Fillable(['name', 'type', 'color'])]
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    /**
     * Relaciona a categoria a todos os lançamentos que a utilizam.
     *
     * O Eloquent usa essa relação para consultar os lançamentos relacionados à categoria.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
