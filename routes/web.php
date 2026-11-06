<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// O painel reúne o resumo financeiro e os filtros de período.
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// O cadastro limita tentativas para reduzir envios automatizados.
Route::get('/cadastro', [RegistrationController::class, 'create'])->name('register');
Route::post('/cadastro', [RegistrationController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('register.store');

// Operações do CRUD de lançamentos, incluindo exclusão lógica e restauração.
Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
Route::post('/transactions/{transaction}/restore', [TransactionController::class, 'restore'])->name('transactions.restore');

// Categorias são criadas pelo modal do painel.
Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
