<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\ValidCpf;
use App\Rules\ValidPhone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    /**
     * Exibe o formulário de cadastro.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Normaliza documentos, valida os dados e cria o cadastro.
     */
    public function store(Request $request): RedirectResponse
    {
        // A tela aceita números com ou sem máscara; o banco guarda somente os dígitos.
        $cpf = $request->input('cpf');

        if (is_string($cpf)) {
            $request->merge(['cpf' => ValidCpf::normalize($cpf)]);
        }

        $phone = $request->input('phone');

        if (is_string($phone)) {
            $request->merge(['phone' => ValidPhone::normalize($phone)]);
        }

        // Confere formato, limites e unicidade antes de criar a pessoa no banco.
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'surname' => ['required', 'string', 'min:3', 'max:100'],
            'address' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'cpf' => ['required', 'string', new ValidCpf, 'unique:users,cpf'],
            'phone' => ['required', 'string', new ValidPhone],
            'age' => ['required', 'integer', 'min:18', 'max:100'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'Informe seu nome.',
            'name.min' => 'O nome deve ter pelo menos 3 caracteres.',
            'name.max' => 'O nome pode ter no máximo 100 caracteres.',
            'surname.required' => 'Informe seu sobrenome.',
            'surname.min' => 'O sobrenome deve ter pelo menos 3 caracteres.',
            'surname.max' => 'O sobrenome pode ter no máximo 100 caracteres.',
            'address.required' => 'Informe seu endereço.',
            'address.min' => 'O endereço deve ter pelo menos 3 caracteres.',
            'address.max' => 'O endereço pode ter no máximo 100 caracteres.',
            'email.required' => 'Informe seu e-mail.',
            'email.email' => 'Informe um endereço de e-mail válido.',
            'email.unique' => 'Este e-mail já está cadastrado.',
            'cpf.required' => 'Informe seu CPF.',
            'cpf.unique' => 'Este CPF já está cadastrado.',
            'phone.required' => 'Informe seu telefone celular.',
            'age.required' => 'Informe sua idade.',
            'age.integer' => 'A idade deve ser um número inteiro.',
            'age.min' => 'A idade mínima permitida é 18 anos.',
            'age.max' => 'A idade máxima permitida é 100 anos.',
            'password.required' => 'Informe uma senha.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            'password.confirmed' => 'A confirmação da senha não corresponde.',
        ]);

        // O atributo password do modelo é convertido para hash antes de ser salvo.
        User::query()->create($validated);

        return to_route('register')
            ->with('status', 'Cadastro realizado com sucesso.');
    }
}
