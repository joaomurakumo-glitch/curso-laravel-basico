<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Confirmar senha | Saldo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="registration-page">
    @include('partials.theme-toggle')
    <main class="registration-shell">
        <a class="brand registration-brand" href="{{ route('dashboard') }}" aria-label="Saldo, início">
            <span class="brand-mark" aria-hidden="true">s</span>
            <span>saldo<span class="brand-period">.</span></span>
        </a>

        <section class="registration-card" aria-labelledby="confirm-password-title">
            <p class="eyebrow">ÁREA PROTEGIDA</p>
            <h1 id="confirm-password-title">Confirme sua senha<span class="heading-period">.</span></h1>
            <p class="registration-copy">Digite a senha da sua conta para ver seus dados pessoais.</p>

            @if ($errors->any())
                <div class="flash-message flash-error" role="alert">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.confirm.store') }}">
                @csrf
                <label class="field-label" for="confirmation-password">Senha da conta</label>
                <input class="text-input" id="confirmation-password" name="password" type="password" autocomplete="current-password" required autofocus aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}">
                @error('password')
                    <p class="field-error">{{ $message }}</p>
                @enderror

                <button class="button button-primary registration-submit" type="submit">Confirmar e continuar</button>
            </form>

            <a class="registration-back" href="{{ route('dashboard') }}">Voltar para a página inicial</a>
        </section>
    </main>
</body>
</html>
