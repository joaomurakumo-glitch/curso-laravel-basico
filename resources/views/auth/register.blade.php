<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Criar cadastro | Saldo</title>
    @vite(['resources/css/app.css', 'resources/js/enhanced-forms.js'])
</head>
<body class="registration-page">
    {{-- Centraliza o formulário para manter o cadastro legível em telas grandes e pequenas. --}}
    <main class="registration-shell">
        <a class="brand registration-brand" href="{{ route('dashboard') }}" aria-label="Saldo, início">
            <span class="brand-mark" aria-hidden="true">s</span>
            <span>saldo<span class="brand-period">.</span></span>
        </a>

        <section class="registration-card" aria-labelledby="registration-title">
            <p class="eyebrow">SEUS DADOS</p>
            <h1 id="registration-title">Criar cadastro<span class="heading-period">.</span></h1>
            <p class="registration-copy">Preencha seus dados para criar seu cadastro.</p>

            {{-- Mensagem de sucesso após salvar o cadastro. --}}
            @if (session('status'))
                <div class="flash-message" role="status">{{ session('status') }}</div>
            @endif

            {{-- Mostra todos os erros devolvidos pelo servidor sem esconder nenhum campo inválido. --}}
            @if ($errors->any())
                <div class="flash-message flash-error" role="alert" aria-labelledby="registration-errors-title">
                    <strong id="registration-errors-title">Não foi possível concluir o cadastro. Confira os campos:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- O token CSRF protege o formulário contra envios forjados de outros sites. --}}
            <form method="POST" action="{{ route('register.store') }}">
                @csrf
                <div class="registration-grid">
                    {{-- Dados pessoais básicos. old() recupera o valor digitado se a validação falhar. --}}
                    <div>
                        <label class="field-label" for="registration-name">Nome</label>
                        <input class="text-input" id="registration-name" name="name" type="text" minlength="3" maxlength="100" autocomplete="given-name" value="{{ old('name') }}" required aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}" @if ($errors->has('name')) aria-describedby="registration-name-error" @endif>
                        @error('name')
                            <p class="field-error" id="registration-name-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="field-label" for="registration-surname">Sobrenome</label>
                        <input class="text-input" id="registration-surname" name="surname" type="text" minlength="3" maxlength="100" autocomplete="family-name" value="{{ old('surname') }}" required aria-invalid="{{ $errors->has('surname') ? 'true' : 'false' }}" @if ($errors->has('surname')) aria-describedby="registration-surname-error" @endif>
                        @error('surname')
                            <p class="field-error" id="registration-surname-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="registration-field-full">
                        <label class="field-label" for="registration-address">Endereço</label>
                        <input class="text-input" id="registration-address" name="address" type="text" minlength="3" maxlength="100" autocomplete="street-address" value="{{ old('address') }}" required aria-invalid="{{ $errors->has('address') ? 'true' : 'false' }}" @if ($errors->has('address')) aria-describedby="registration-address-error" @endif>
                        @error('address')
                            <p class="field-error" id="registration-address-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="registration-field-full">
                        <label class="field-label" for="registration-email">E-mail</label>
                        <input class="text-input" id="registration-email" name="email" type="email" maxlength="255" autocomplete="email" value="{{ old('email') }}" required aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" @if ($errors->has('email')) aria-describedby="registration-email-error" @endif>
                        @error('email')
                            <p class="field-error" id="registration-email-error">{{ $message }}</p>
                        @enderror
                    </div>
                    {{-- CPF e telefone aceitam máscara na tela; o servidor normaliza e valida os números. --}}
                    <div class="registration-field-full">
                        <label class="field-label" for="registration-cpf">CPF</label>
                        <input class="text-input" id="registration-cpf" name="cpf" type="text" inputmode="numeric" minlength="11" maxlength="14" pattern="[0-9]{11}|[0-9]{3}\.[0-9]{3}\.[0-9]{3}-[0-9]{2}" placeholder="000.000.000-00" autocomplete="off" value="{{ old('cpf') }}" required aria-describedby="registration-cpf-hint{{ $errors->has('cpf') ? ' registration-cpf-error' : '' }}" aria-invalid="{{ $errors->has('cpf') ? 'true' : 'false' }}">
                        <p class="field-error" id="registration-cpf-hint">Digite os 11 números, com ou sem máscara.</p>
                        @error('cpf')
                            <p class="field-error" id="registration-cpf-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="registration-field-full">
                        <label class="field-label" for="registration-phone">Telefone celular</label>
                        <input class="text-input" id="registration-phone" name="phone" type="tel" inputmode="numeric" minlength="11" maxlength="15" pattern="[0-9]{11}|\([0-9]{2}\) 9[0-9]{4}-[0-9]{4}" placeholder="(11) 91234-5678" autocomplete="tel-national" value="{{ old('phone') }}" required aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}" @if ($errors->has('phone')) aria-describedby="registration-phone-error" @endif>
                        @error('phone')
                            <p class="field-error" id="registration-phone-error">{{ $message }}</p>
                        @enderror
                    </div>
                    {{-- Idade e senha têm limites no navegador; o servidor repete a validação por segurança. --}}
                    <div>
                        <label class="field-label" for="registration-age">Idade</label>
                        <input class="text-input" id="registration-age" name="age" type="number" min="18" max="100" step="1" value="{{ old('age') }}" required aria-invalid="{{ $errors->has('age') ? 'true' : 'false' }}" @if ($errors->has('age')) aria-describedby="registration-age-error" @endif>
                        @error('age')
                            <p class="field-error" id="registration-age-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="field-label" for="registration-password">Senha</label>
                        <input class="text-input" id="registration-password" name="password" type="password" minlength="8" autocomplete="new-password" required aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}" @if ($errors->has('password')) aria-describedby="registration-password-error" @endif>
                        @error('password')
                            <p class="field-error" id="registration-password-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="registration-field-full">
                        <label class="field-label" for="registration-password-confirmation">Confirme a senha</label>
                        <input class="text-input" id="registration-password-confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" required>
                    </div>
                </div>

                <button class="button button-primary registration-submit" type="submit">Criar cadastro</button>
            </form>

            <a class="registration-back" href="{{ route('dashboard') }}">Voltar para a página inicial</a>
        </section>
    </main>
</body>
</html>
