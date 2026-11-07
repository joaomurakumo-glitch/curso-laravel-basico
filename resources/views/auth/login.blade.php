<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Entrar | Saldo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="registration-page">
    @include('partials.theme-toggle')
    <main class="registration-shell">
        <a class="brand registration-brand" href="{{ route('dashboard') }}" aria-label="Saldo, início">
            <span class="brand-mark" aria-hidden="true">s</span>
            <span>saldo<span class="brand-period">.</span></span>
        </a>

        <section class="registration-card" aria-labelledby="login-title">
            <p class="eyebrow">BEM-VINDO DE VOLTA</p>
            <h1 id="login-title">Entrar na conta<span class="heading-period">.</span></h1>
            <p class="registration-copy">Use seu e-mail e sua senha para acessar sua conta.</p>

            @if (session('status'))
                <div class="flash-message" role="status">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="flash-message flash-error" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}">
                @csrf
                <label class="field-label" for="login-email">E-mail</label>
                <input class="text-input" id="login-email" name="email" type="email" autocomplete="email" value="{{ old('email') }}" required autofocus aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">

                <label class="field-label" for="login-password">Senha</label>
                <input class="text-input" id="login-password" name="password" type="password" autocomplete="current-password" required aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}">

                <label class="account-remember">
                    <input type="checkbox" name="remember" value="1">
                    Manter conectado neste dispositivo
                </label>

                <button class="button button-primary registration-submit" type="submit">Entrar</button>
            </form>

            <a class="registration-back" href="{{ route('register') }}">Ainda não tenho uma conta</a>
            <a class="registration-back" href="{{ route('dashboard') }}">Voltar para a página inicial</a>
        </section>
    </main>
</body>
</html>
