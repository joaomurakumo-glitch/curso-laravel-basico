<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasswordConfirmationController extends Controller
{
    /**
     * Mostra a confirmação exigida antes de exibir dados da conta.
     */
    public function create(): View
    {
        return view('auth.confirm-password');
    }

    /**
     * Confirma a identidade e libera a página solicitada.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ], [
            'password.required' => 'Informe sua senha.',
            'password.current_password' => 'A senha informada está incorreta.',
        ]);

        $request->session()->passwordConfirmed();

        return redirect()->intended(route('account.show'));
    }
}
