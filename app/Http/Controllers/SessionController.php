<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SessionController extends Controller
{
    /**
     * Mostra o formulário para entrar em uma conta existente.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Confere as credenciais e inicia uma sessão protegida.
     */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Informe seu e-mail.',
            'email.email' => 'Informe um endereço de e-mail válido.',
            'password.required' => 'Informe sua senha.',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'E-mail ou senha incorretos.'])
                ->withInput($request->only('email'));
        }

        $request->session()->regenerate();
        $request->session()->passwordConfirmed();

        return redirect()->intended(route('account.show'));
    }

    /**
     * Encerra a sessão e invalida o token anterior.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login')
            ->with('status', 'Você saiu da sua conta.');
    }
}
