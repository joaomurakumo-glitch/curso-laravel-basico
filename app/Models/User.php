<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Define os dados aceitos para gravação e esconde informações privadas ao serializar o usuário.
#[Fillable(['name', 'surname', 'address', 'cep', 'email', 'cpf', 'phone', 'age', 'password'])]
#[Hidden(['cep', 'cpf', 'phone', 'password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Converte atributos sensíveis ou numéricos para os tipos usados pela aplicação.
     *
     * A senha é transformada em hash automaticamente; ela nunca deve ser salva em texto puro.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'age' => 'integer',
            'password' => 'hashed',
        ];
    }
}
