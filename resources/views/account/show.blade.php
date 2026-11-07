<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Minha conta | Saldo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="registration-page">
    @include('partials.theme-toggle')
    <main class="account-shell">
        <a class="brand registration-brand" href="{{ route('dashboard') }}" aria-label="Saldo, início">
            <span class="brand-mark" aria-hidden="true">s</span>
            <span>saldo<span class="brand-period">.</span></span>
        </a>

        <section class="registration-card" aria-labelledby="account-title">
            <p class="eyebrow">SEUS DADOS PROTEGIDOS</p>
            <h1 id="account-title">Minha conta<span class="heading-period">.</span></h1>
            <p class="registration-copy">Confira os dados informados ao criar seu cadastro. O CEP e o CPF ficam bloqueados para edição nesta tela.</p>

            @if (session('status'))
                <div class="flash-message" role="status">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="flash-message flash-error" role="alert">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            {{-- Os campos de CEP e CPF são somente para leitura; não existe rota que os altere. --}}
            <div class="registration-grid account-profile">
                <div>
                    <label class="field-label" for="account-name">Nome</label>
                    <input class="text-input" id="account-name" value="{{ $user->name }}" readonly>
                </div>
                <div>
                    <label class="field-label" for="account-surname">Sobrenome</label>
                    <input class="text-input" id="account-surname" value="{{ $user->surname ?: 'Não informado' }}" readonly>
                </div>
                <div class="registration-field-full">
                    <label class="field-label" for="account-address">Endereço</label>
                    <input class="text-input" id="account-address" value="{{ $user->address ?: 'Não informado' }}" readonly>
                </div>
                <div>
                    <label class="field-label" for="account-cep">CEP (bloqueado)</label>
                    <input class="text-input" id="account-cep" name="cep" value="{{ $user->cep ? substr($user->cep, 0, 5).'-'.substr($user->cep, 5, 3) : 'Não informado' }}" readonly aria-readonly="true">
                </div>
                <div>
                    <label class="field-label" for="account-cpf">CPF (bloqueado)</label>
                    <input class="text-input" id="account-cpf" name="cpf" value="{{ $user->cpf ? substr($user->cpf, 0, 3).'.'.substr($user->cpf, 3, 3).'.'.substr($user->cpf, 6, 3).'-'.substr($user->cpf, 9, 2) : 'Não informado' }}" readonly aria-readonly="true">
                </div>
                <div class="registration-field-full">
                    <label class="field-label" for="account-email">E-mail</label>
                    <input class="text-input" id="account-email" value="{{ $user->email }}" readonly>
                </div>
                <div>
                    <label class="field-label" for="account-phone">Telefone celular</label>
                    <input class="text-input" id="account-phone" value="{{ $user->phone ?: 'Não informado' }}" readonly>
                </div>
                <div>
                    <label class="field-label" for="account-age">Idade</label>
                    <input class="text-input" id="account-age" value="{{ $user->age ? $user->age.' anos' : 'Não informado' }}" readonly>
                </div>
            </div>

            <section class="account-password-section" aria-labelledby="password-title">
                <p class="eyebrow">SEGURANÇA</p>
                <h2 id="password-title">Redefinir senha</h2>
                <p class="registration-copy">Informe a senha atual para autorizar a troca. A nova senha precisa ter pelo menos 8 caracteres.</p>

                <form method="POST" action="{{ route('account.password.update') }}">
                    @csrf
                    @method('PUT')

                    <label class="field-label" for="current-password">Senha atual</label>
                    <input class="text-input" id="current-password" name="current_password" type="password" autocomplete="current-password" required aria-invalid="{{ $errors->has('current_password') ? 'true' : 'false' }}">
                    @error('current_password')
                        <p class="field-error">{{ $message }}</p>
                    @enderror

                    <label class="field-label" for="new-password">Nova senha</label>
                    <input class="text-input" id="new-password" name="password" type="password" minlength="8" autocomplete="new-password" required aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}">
                    @error('password')
                        <p class="field-error">{{ $message }}</p>
                    @enderror

                    <label class="field-label" for="new-password-confirmation">Confirme a nova senha</label>
                    <input class="text-input" id="new-password-confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" required>

                    <button class="button button-primary registration-submit" type="submit">Salvar nova senha</button>
                </form>
            </section>

            <div class="account-actions">
                <a class="registration-back" href="{{ route('dashboard') }}">Voltar para a página inicial</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="account-logout" type="submit">Sair da conta</button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
