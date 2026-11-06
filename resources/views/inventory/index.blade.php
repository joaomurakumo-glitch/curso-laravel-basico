<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Estoque | Saldo</title>
    @vite(['resources/css/app.css', 'resources/js/enhanced-forms.js'])
</head>
<body class="registration-page">
    <main class="inventory-shell">
        <header class="inventory-header">
            <div>
                <a class="brand registration-brand" href="{{ route('dashboard') }}" aria-label="Saldo, início">
                    <span class="brand-mark" aria-hidden="true">s</span>
                    <span>saldo<span class="brand-period">.</span></span>
                </a>
                <p class="eyebrow">CONTROLE DE PRODUTOS</p>
                <h1>Estoque<span class="heading-period">.</span></h1>
                <p class="registration-copy">Cadastre produtos, registre saídas e acompanhe os descontos aplicados.</p>
            </div>
            <a class="button button-filter" href="{{ route('dashboard') }}">Voltar ao painel</a>
        </header>

        @if (session('status'))
            <div class="flash-message" role="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="flash-message flash-error" role="alert" aria-labelledby="inventory-errors-title">
                <strong id="inventory-errors-title">Não foi possível salvar. Corrija os campos indicados:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="registration-card inventory-create-card" aria-labelledby="inventory-create-title">
            <p class="eyebrow">NOVO PRODUTO</p>
            <h2 id="inventory-create-title">Adicionar ao estoque</h2>
            <form method="POST" action="{{ route('inventory.items.store') }}" data-trim-form>
                @csrf
                <div class="inventory-create-grid">
                    <div>
                        <label class="field-label" for="inventory-name">Nome do produto</label>
                        <input class="text-input" id="inventory-name" name="name" type="text" minlength="3" maxlength="100" value="{{ old('name') }}" data-trim required>
                    </div>
                    <div>
                        <label class="field-label" for="inventory-stock">Quantidade inicial</label>
                        <input class="text-input" id="inventory-stock" name="stock_quantity" type="number" min="0" max="4294967295" step="1" value="{{ old('stock_quantity', 0) }}" required>
                    </div>
                    <div>
                        <label class="field-label" for="inventory-price">Preço unitário (R$)</label>
                        <input class="text-input" id="inventory-price" name="unit_price" type="number" min="0.01" max="99999999.99" step="0.01" value="{{ old('unit_price') }}" placeholder="0.00" required>
                    </div>
                </div>
                <button class="button button-primary inventory-submit" type="submit">Cadastrar produto</button>
            </form>
        </section>

        <section class="inventory-list" aria-label="Produtos cadastrados">
            @forelse ($items as $item)
                <article class="registration-card inventory-item-card">
                    <header class="inventory-item-heading">
                        <div>
                            <p class="eyebrow">PRODUTO</p>
                            <h2>{{ $item->name }}</h2>
                        </div>
                        <div class="inventory-stock-badge">
                            <strong>{{ number_format($item->stock_quantity, 0, ',', '.') }}</strong>
                            <span>em estoque</span>
                        </div>
                    </header>
                    <p class="inventory-price">Preço unitário: <strong>R$ {{ number_format((float) $item->unit_price, 2, ',', '.') }}</strong></p>

                    <form class="inventory-movement-form" method="POST" action="{{ route('inventory.movements.store', $item) }}" data-discount-form data-unit-price="{{ $item->unit_price }}">
                        @csrf
                        <h3>Registrar saída</h3>
                        <div class="inventory-movement-fields">
                            <div>
                                <label class="field-label" for="quantity-{{ $item->id }}">Quantidade de saída</label>
                                <input class="text-input" id="quantity-{{ $item->id }}" name="quantity" type="number" min="1" max="{{ $item->stock_quantity }}" step="1" value="{{ old('quantity') }}" required data-discount-quantity>
                            </div>
                            <div>
                                <label class="field-label" for="discount-{{ $item->id }}">Desconto (%)</label>
                                <input class="text-input" id="discount-{{ $item->id }}" name="discount_percent" type="number" min="0" max="30" step="0.01" value="{{ old('discount_percent', '0') }}" required data-discount-percent>
                            </div>
                            <p class="inventory-total">Total com desconto <strong data-discount-total>R$ 0,00</strong></p>
                        </div>
                        <button class="button button-primary" type="submit">Confirmar saída</button>
                    </form>

                    @if ($item->movements->isNotEmpty())
                        <div class="inventory-history">
                            <h3>Saídas registradas</h3>
                            @foreach ($item->movements as $movement)
                                <details class="inventory-movement">
                                    <summary>
                                        <span>{{ $movement->created_at->format('d/m/Y H:i') }} · {{ $movement->quantity }} un.</span>
                                        <strong>R$ {{ number_format((float) $movement->total, 2, ',', '.') }}</strong>
                                    </summary>
                                    <form class="inventory-movement-form" method="POST" action="{{ route('inventory.movements.update', ['inventoryItem' => $item, 'movement' => $movement]) }}" data-discount-form data-unit-price="{{ $movement->unit_price }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="inventory-movement-fields">
                                            <div>
                                                <label class="field-label" for="edit-quantity-{{ $movement->id }}">Quantidade</label>
                                                <input class="text-input" id="edit-quantity-{{ $movement->id }}" name="quantity" type="number" min="1" step="1" value="{{ old('quantity', $movement->quantity) }}" required data-discount-quantity>
                                            </div>
                                            <div>
                                                <label class="field-label" for="edit-discount-{{ $movement->id }}">Desconto (%)</label>
                                                <input class="text-input" id="edit-discount-{{ $movement->id }}" name="discount_percent" type="number" min="0" max="30" step="0.01" value="{{ old('discount_percent', $movement->discount_percent) }}" required data-discount-percent>
                                            </div>
                                            <p class="inventory-total">Total com desconto <strong data-discount-total>R$ {{ number_format((float) $movement->total, 2, ',', '.') }}</strong></p>
                                        </div>
                                        <button class="button button-outline" type="submit">Atualizar saída</button>
                                    </form>
                                </details>
                            @endforeach
                        </div>
                    @endif
                </article>
            @empty
                <div class="registration-card inventory-empty">
                    <p>Nenhum produto cadastrado. Use o formulário acima para começar a controlar o estoque.</p>
                </div>
            @endforelse
        </section>
    </main>
</body>
</html>
