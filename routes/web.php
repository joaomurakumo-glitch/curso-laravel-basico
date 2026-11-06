<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryItemController;
use App\Http\Controllers\InventoryMovementController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/cadastro', [RegistrationController::class, 'create'])->name('register');
Route::post('/cadastro', [RegistrationController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('register.store');

Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
Route::post('/transactions/{transaction}/restore', [TransactionController::class, 'restore'])->name('transactions.restore');

Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');

Route::get('/estoque', [InventoryItemController::class, 'index'])->name('inventory.items.index');
Route::post('/estoque', [InventoryItemController::class, 'store'])->name('inventory.items.store');
Route::post('/estoque/{inventoryItem}/saidas', [InventoryMovementController::class, 'store'])
    ->name('inventory.movements.store');
Route::put('/estoque/{inventoryItem}/saidas/{movement}', [InventoryMovementController::class, 'update'])
    ->name('inventory.movements.update');
