<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PasswordConfirmationController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// O painel reúne o resumo financeiro e os filtros de período.
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// O cadastro limita tentativas para reduzir envios automatizados.
Route::middleware('guest')->group(function () {
    Route::get('/cadastro', [RegistrationController::class, 'create'])->name('register');
    Route::post('/cadastro', [RegistrationController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('register.store');

    // Login é limitado para dificultar tentativas repetidas de descobrir uma senha.
    Route::get('/entrar', [SessionController::class, 'create'])->name('login');
    Route::post('/entrar', [SessionController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('login.store');
});
Route::post('/sair', [SessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// A senha atual precisa ser confirmada antes de abrir informações pessoais protegidas.
Route::get('/confirmar-senha', [PasswordConfirmationController::class, 'create'])
    ->middleware('auth')
    ->name('password.confirm');
Route::post('/confirmar-senha', [PasswordConfirmationController::class, 'store'])
    ->middleware(['auth', 'throttle:10,1'])
    ->name('password.confirm.store');
Route::middleware(['auth', 'password.confirm'])->group(function () {
    Route::get('/minha-conta', [AccountController::class, 'show'])->name('account.show');
});

// Alterar senha exige a senha atual no próprio formulário, mesmo com sessão autenticada.
Route::put('/minha-conta/senha', [AccountController::class, 'updatePassword'])
    ->middleware(['auth', 'throttle:10,1'])
    ->name('account.password.update');

// Operações do CRUD de lançamentos, incluindo exclusão lógica e restauração.
Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
Route::post('/transactions/{transaction}/restore', [TransactionController::class, 'restore'])->name('transactions.restore');

// Categorias são criadas pelo modal do painel.
Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
