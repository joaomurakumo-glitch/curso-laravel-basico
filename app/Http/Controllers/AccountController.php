<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    /**
     * Exibe os dados da conta autenticada.
     */
    public function show(Request $request): View
    {
        return view('account.show', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Altera a senha somente depois de validar a senha atual.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ], [
            'current_password.required' => 'Informe sua senha atual.',
            'current_password.current_password' => 'A senha atual está incorreta.',
            'password.required' => 'Informe uma nova senha.',
            'password.min' => 'A nova senha deve ter pelo menos 8 caracteres.',
            'password.confirmed' => 'A confirmação da nova senha não corresponde.',
            'password.different' => 'A nova senha deve ser diferente da senha atual.',
        ]);

        $request->user()->update([
            'password' => $validated['password'],
        ]);

        return to_route('account.show')
            ->with('status', 'Senha alterada com sucesso.');
    }
}
