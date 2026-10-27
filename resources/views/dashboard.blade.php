<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Saldo | Finanças pessoais</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="finance-app">
    <div class="app-frame">
        <aside class="sidebar">
            <a class="brand" href="{{ route('dashboard') }}" aria-label="Saldo, início">
                <span class="brand-mark" aria-hidden="true">s</span>
                <span>saldo<span class="brand-period">.</span></span>
            </a>

            <div class="workspace-label">ESPAÇO PESSOAL</div>
            <nav class="primary-nav" aria-label="Navegação principal">
                <a class="nav-link is-active" href="{{ route('dashboard', ['month' => $month]) }}" aria-current="page">
                    <span class="nav-glyph" aria-hidden="true">◫</span>
                    Visão geral
                </a>
                <a class="nav-link" href="#transactions">
                    <span class="nav-glyph" aria-hidden="true">↕</span>
                    Lançamentos
                </a>
                <button class="nav-link nav-button" type="button" data-open-categories>
                    <span class="nav-glyph" aria-hidden="true">◇</span>
                    Categorias
                    <span class="nav-count">{{ $allCategories->count() }}</span>
                </button>
            </nav>

            <div class="sidebar-spacer"></div>
            <div class="sidebar-note">
                <span class="note-mark" aria-hidden="true">✳</span>
                <p>Organização simples para escolhas melhores.</p>
            </div>
            <div class="sidebar-foot">FINANÇAS PESSOAIS <span>·</span> 2026</div>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <div class="breadcrumbs"><span>FINANÇAS</span><span class="crumb-slash">/</span><strong>VISÃO GERAL</strong></div>
                <div class="topbar-right">
                    <span class="today-label">{{ now()->locale('pt_BR')->translatedFormat('l, d \d\e F') }}</span>
                    <span class="avatar" aria-label="Perfil pessoal">EU</span>
                </div>
            </header>

            <section class="page-heading">
                <div>
                    <p class="eyebrow">SEU DINHEIRO, EM PERSPECTIVA</p>
                    <h1>Visão geral<span class="heading-period">.</span></h1>
                    <p class="heading-copy">Acompanhe o que entra, o que sai e o que fica.</p>
                </div>
                <button class="button button-primary" type="button" data-open-transaction>
                    <span class="button-plus" aria-hidden="true">+</span>
                    Novo lançamento
                </button>
            </section>

            @if (session('status'))
                <div class="flash-message" role="status">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="flash-message flash-error" role="alert">
                    <strong>Não foi possível salvar.</strong>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <section class="overview-toolbar" aria-label="Período do resumo">
                <div class="month-switcher">
                    <a class="month-arrow" href="{{ route('dashboard', array_merge(request()->query(), ['month' => \Carbon\CarbonImmutable::createFromFormat('Y-m', $month)->subMonth()->format('Y-m')])) }}" aria-label="Mês anterior">‹</a>
                    <span class="month-label">{{ $monthLabel }}</span>
                    <a class="month-arrow" href="{{ route('dashboard', array_merge(request()->query(), ['month' => \Carbon\CarbonImmutable::createFromFormat('Y-m', $month)->addMonth()->format('Y-m')])) }}" aria-label="Próximo mês">›</a>
                </div>
                <form class="filter-form" action="{{ route('dashboard') }}" method="GET">
                    <label class="filter-control filter-month">
                        <span class="sr-only">Mês</span>
                        <input type="month" name="month" value="{{ $month }}" aria-label="Filtrar por mês">
                    </label>
                    <label class="filter-control">
                        <span class="sr-only">Tipo</span>
                        <select name="type" aria-label="Filtrar por tipo">
                            <option value="">Todos os tipos</option>
                            <option value="income" @selected($typeFilter === 'income')>Receitas</option>
                            <option value="expense" @selected($typeFilter === 'expense')>Despesas</option>
                        </select>
                    </label>
                    <label class="filter-control filter-category">
                        <span class="sr-only">Categoria</span>
                        <select name="category" aria-label="Filtrar por categoria">
                            <option value="">Todas as categorias</option>
                            @foreach ($allCategories as $category)
                                <option value="{{ $category->id }}" @selected((string) $categoryFilter === (string) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <button class="button button-filter" type="submit">Filtrar</button>
                    @if ($typeFilter !== '' || $categoryFilter !== '')
                        <a class="clear-filters" href="{{ route('dashboard', ['month' => $month]) }}">Limpar</a>
                    @endif
                </form>
            </section>

            <section class="summary-grid" aria-label="Resumo de {{ $monthLabel }}">
                <article class="summary-card summary-balance">
                    <div class="summary-topline"><span>Saldo do mês</span><span class="summary-icon balance-icon" aria-hidden="true">↑</span></div>
                    <p class="summary-value">R$ {{ number_format((float) $balanceTotal, 2, ',', '.') }}</p>
                    <p class="summary-caption">Receitas menos despesas</p>
                    <div class="balance-rule" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span></div>
                </article>
                <article class="summary-card summary-income">
                    <div class="summary-topline"><span>Receitas</span><span class="summary-icon income-icon" aria-hidden="true">↑</span></div>
                    <p class="summary-value">R$ {{ number_format((float) $incomeTotal, 2, ',', '.') }}</p>
                    <p class="summary-caption">{{ $monthTransactionCount }} {{ $monthTransactionCount === 1 ? 'lançamento no período' : 'lançamentos no período' }}</p>
                </article>
                <article class="summary-card summary-expense">
                    <div class="summary-topline"><span>Despesas</span><span class="summary-icon expense-icon" aria-hidden="true">↓</span></div>
                    <p class="summary-value">R$ {{ number_format((float) $expenseTotal, 2, ',', '.') }}</p>
                    <p class="summary-caption">{{ $topExpenseCategories->count() }} {{ $topExpenseCategories->count() === 1 ? 'categoria com gasto' : 'categorias com gastos' }}</p>
                </article>
            </section>

            <section class="content-grid">
                <article class="panel transaction-panel" id="transactions">
                    <div class="panel-heading">
                        <div>
                            <p class="eyebrow">MOVIMENTAÇÕES</p>
                            <h2>Últimos lançamentos</h2>
                        </div>
                        <span class="result-count">{{ $transactions->total() }} registros</span>
                    </div>

                    @if ($transactions->isNotEmpty())
                        <div class="transaction-table-wrap">
                            <table class="transaction-table">
                                <thead>
                                    <tr>
                                        <th scope="col">Descrição</th>
                                        <th scope="col">Categoria</th>
                                        <th scope="col">Data</th>
                                        <th scope="col" class="amount-heading">Valor</th>
                                        <th scope="col"><span class="sr-only">Ações</span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($transactions as $transaction)
                                        <tr>
                                            <td>
                                                <div class="transaction-description">
                                                    <span class="transaction-mark {{ $transaction->type === 'income' ? 'mark-income' : 'mark-expense' }}" aria-hidden="true">{{ $transaction->type === 'income' ? '↑' : '↓' }}</span>
                                                    <span>
                                                        <strong>{{ $transaction->description }}</strong>
                                                        @if ($transaction->notes)
                                                            <small>{{ $transaction->notes }}</small>
                                                        @endif
                                                    </span>
                                                </div>
                                            </td>
                                            <td><span class="category-label"><i style="--category-color: {{ $transaction->category->color }}"></i>{{ $transaction->category->name }}</span></td>
                                            <td class="date-cell">{{ $transaction->date->locale('pt_BR')->translatedFormat('d M') }}</td>
                                            <td class="amount-cell {{ $transaction->type === 'income' ? 'amount-income' : 'amount-expense' }}">
                                                {{ $transaction->type === 'income' ? '+' : '−' }} R$ {{ number_format((float) $transaction->amount, 2, ',', '.') }}
                                            </td>
                                            <td class="row-actions">
                                                <button
                                                    class="text-action edit-transaction"
                                                    type="button"
                                                    data-update-url="{{ route('transactions.update', array_merge(['transaction' => $transaction->id], request()->query())) }}"
                                                    data-description="{{ $transaction->description }}"
                                                    data-amount="{{ $transaction->amount }}"
                                                    data-type="{{ $transaction->type }}"
                                                    data-date="{{ $transaction->date->format('Y-m-d') }}"
                                                    data-category-id="{{ $transaction->category_id }}"
                                                    data-notes="{{ $transaction->notes }}"
                                                >Editar</button>
                                                <form class="delete-form" action="{{ route('transactions.destroy', array_merge(['transaction' => $transaction->id], request()->query())) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="text-action text-danger" type="submit">Excluir</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if ($transactions->hasPages())
                            <div class="pagination-row">
                                <span>Página {{ $transactions->currentPage() }} de {{ $transactions->lastPage() }}</span>
                                <div class="pagination-actions">
                                    @if ($transactions->previousPageUrl())
                                        <a href="{{ $transactions->previousPageUrl() }}" aria-label="Página anterior">‹</a>
                                    @else
                                        <span class="is-disabled" aria-hidden="true">‹</span>
                                    @endif
                                    @if ($transactions->nextPageUrl())
                                        <a href="{{ $transactions->nextPageUrl() }}" aria-label="Próxima página">›</a>
                                    @else
                                        <span class="is-disabled" aria-hidden="true">›</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="empty-state">
                            <span class="empty-mark" aria-hidden="true">+</span>
                            <h3>Nenhum lançamento por aqui</h3>
                            <p>Adicione uma receita ou despesa para começar a acompanhar seu mês.</p>
                            <button class="button button-outline" type="button" data-open-transaction>Adicionar lançamento</button>
                        </div>
                    @endif
                </article>

                <aside class="panel category-panel">
                    <div class="panel-heading category-heading">
                        <div>
                            <p class="eyebrow">ONDE VOCÊ GASTOU</p>
                            <h2>Despesas por categoria</h2>
                        </div>
                        <span class="panel-spark" aria-hidden="true">✳</span>
                    </div>
                    @if ($topExpenseCategories->isNotEmpty())
                        @php($maxExpenseTotal = max((float) $topExpenseCategories->max('total'), 1))
                        <div class="category-breakdown">
                            @foreach ($topExpenseCategories as $expenseCategory)
                                @php($categoryPercent = min(100, ((float) $expenseCategory->total / $maxExpenseTotal) * 100))
                                <div class="category-breakdown-item">
                                    <div class="breakdown-label">
                                        <span><i style="--category-color: {{ $expenseCategory->category->color }}"></i>{{ $expenseCategory->category->name }}</span>
                                        <strong>R$ {{ number_format((float) $expenseCategory->total, 2, ',', '.') }}</strong>
                                    </div>
                                    <div class="breakdown-track"><span style="width: {{ $categoryPercent }}%; --category-color: {{ $expenseCategory->category->color }}"></span></div>
                                </div>
                            @endforeach
                        </div>
                        <div class="category-total-row"><span>Total no mês</span><strong>R$ {{ number_format((float) $expenseTotal, 2, ',', '.') }}</strong></div>
                    @else
                        <div class="category-empty">
                            <span aria-hidden="true">○</span>
                            <p>As despesas do mês aparecem aqui, organizadas por categoria.</p>
                        </div>
                    @endif
                    <button class="category-manage" type="button" data-open-categories>Gerenciar categorias <span aria-hidden="true">→</span></button>
                </aside>
            </section>

            <footer class="page-footer">
                <span>Seu resumo considera todos os lançamentos do mês.</span>
                <span>Valores em reais <i aria-hidden="true">·</i> atualizados agora</span>
            </footer>
        </main>
    </div>

    <dialog class="app-dialog" id="transaction-dialog" aria-labelledby="transaction-dialog-title" data-reopen="{{ $errors->any() && ! $errors->has('name') && ! $errors->has('color') ? 'true' : 'false' }}">
        <form class="dialog-form" id="transaction-form" method="POST" action="{{ route('transactions.store', request()->only(['month', 'type', 'category'])) }}" data-store-url="{{ route('transactions.store', request()->only(['month', 'type', 'category'])) }}">
            @csrf
            <input id="transaction-method" type="hidden" name="_method" value="">
            <input type="hidden" name="month" value="{{ $month }}">
            <input type="hidden" name="return_type" value="{{ $typeFilter }}">
            <input type="hidden" name="return_category" value="{{ $categoryFilter }}">
            <div class="dialog-heading">
                <div>
                    <p class="eyebrow">MOVIMENTAÇÃO FINANCEIRA</p>
                    <h2 id="transaction-dialog-title">Novo lançamento</h2>
                </div>
                <button class="dialog-close" type="button" data-close-dialog aria-label="Fechar">×</button>
            </div>
            <div class="type-switch" role="group" aria-label="Tipo do lançamento">
                <button class="type-option is-selected" type="button" data-type-option="expense">Despesa</button>
                <button class="type-option" type="button" data-type-option="income">Receita</button>
                <input id="transaction-type" type="hidden" name="type" value="{{ old('type', 'expense') }}">
            </div>
            <label class="field-label" for="transaction-description">Descrição</label>
            <input class="text-input" id="transaction-description" name="description" maxlength="120" value="{{ old('description') }}" placeholder="Ex.: Mercado da semana" required>
            <div class="form-row">
                <div>
                    <label class="field-label" for="transaction-amount">Valor</label>
                    <div class="currency-input"><span>R$</span><input class="text-input" id="transaction-amount" name="amount" type="number" min="0.01" max="9999999999.99" step="0.01" value="{{ old('amount') }}" placeholder="0,00" required></div>
                </div>
                <div>
                    <label class="field-label" for="transaction-date">Data</label>
                    <input class="text-input" id="transaction-date" name="date" type="date" value="{{ old('date', now()->format('Y-m-d')) }}" required>
                </div>
            </div>
            <label class="field-label" for="transaction-category">Categoria</label>
            <select class="text-input" id="transaction-category" name="category_id" required>
                <option value="">Selecione uma categoria</option>
                @foreach ($allCategories as $category)
                    <option value="{{ $category->id }}" data-category-type="{{ $category->type }}" @selected((string) old('category_id') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <label class="field-label" for="transaction-notes">Observação <span class="optional-label">OPCIONAL</span></label>
            <textarea class="text-input notes-input" id="transaction-notes" name="notes" maxlength="500" rows="3" placeholder="Um detalhe para lembrar depois">{{ old('notes') }}</textarea>
            <div class="dialog-actions">
                <button class="button button-quiet" type="button" data-close-dialog>Cancelar</button>
                <button class="button button-primary" type="submit" id="transaction-submit">Salvar lançamento</button>
            </div>
        </form>
    </dialog>

    <dialog class="app-dialog category-dialog" id="category-dialog" aria-labelledby="category-dialog-title" data-reopen="{{ $errors->has('name') || $errors->has('color') || $errors->has('type') ? 'true' : 'false' }}">
        <div class="dialog-heading">
            <div>
                <p class="eyebrow">ORGANIZAÇÃO</p>
                <h2 id="category-dialog-title">Categorias</h2>
            </div>
            <button class="dialog-close" type="button" data-close-dialog aria-label="Fechar">×</button>
        </div>
        <div class="category-columns">
            @foreach (['expense' => 'Despesas', 'income' => 'Receitas'] as $categoryType => $categoryTypeLabel)
                <section class="category-list-section">
                    <h3>{{ $categoryTypeLabel }}</h3>
                    @foreach (($categories[$categoryType] ?? collect()) as $category)
                        <div class="category-list-item"><span><i style="--category-color: {{ $category->color }}"></i>{{ $category->name }}</span><span class="category-type-note">{{ $categoryTypeLabel }}</span></div>
                    @endforeach
                </section>
            @endforeach
        </div>
        <form class="new-category-form" method="POST" action="{{ route('categories.store', request()->only(['month', 'type', 'category'])) }}">
            @csrf
            <input type="hidden" name="month" value="{{ $month }}">
            <input type="hidden" name="return_type" value="{{ $typeFilter }}">
            <input type="hidden" name="return_category" value="{{ $categoryFilter }}">
            <p class="eyebrow">NOVA CATEGORIA</p>
            <div class="form-row category-create-row">
                <div>
                    <label class="field-label" for="new-category-name">Nome</label>
                    <input class="text-input" id="new-category-name" name="name" maxlength="40" value="{{ old('name') }}" placeholder="Ex.: Pets" required>
                </div>
                <div>
                    <label class="field-label" for="new-category-type">Tipo</label>
                    <select class="text-input" id="new-category-type" name="type" required>
                        <option value="expense" @selected(old('type', 'expense') === 'expense')>Despesa</option>
                        <option value="income" @selected(old('type') === 'income')>Receita</option>
                    </select>
                </div>
                <div>
                    <label class="field-label" for="new-category-color">Cor</label>
                    <input class="color-input" id="new-category-color" name="color" type="color" value="{{ old('color', '#66804f') }}" required>
                </div>
            </div>
            <div class="dialog-actions">
                <button class="button button-primary" type="submit">Adicionar categoria</button>
            </div>
        </form>
    </dialog>
</body>
</html>
