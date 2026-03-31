<?php

use App\Http\Controllers\Api\DashboardSummaryController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EstimateController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalesController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->middleware('auth');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

Route::middleware('auth')->prefix('api')->group(function () {
    Route::get('/dashboard/summary', DashboardSummaryController::class)
        ->name('api.dashboard.summary');
});

Route::middleware(['auth', 'checkRole:sales'])->group(function () {
    Route::post('/estimates', [EstimateController::class, 'store'])->name('estimates.store');
    Route::get('/estimates/{estimate}', [EstimateController::class, 'show'])->name('estimates.show');
    Route::post('/estimates/{estimate}/convert', [EstimateController::class, 'convert'])->name('estimates.convert');

    Route::get('/sales', [SalesController::class, 'create'])->name('sales.index');
    Route::post('/sales', [SalesController::class, 'store'])->name('sales.store');

    Route::get('/sales/{sale}', [SalesController::class, 'show'])->name('sales.show');
    Route::post('/sales/{sale}/payments', [PaymentController::class, 'storeSalePayment'])
        ->name('sales.payments.store');

    Route::get('/sales/{sale}/invoice', [InvoiceController::class, 'invoice'])
        ->name('sales.invoice');

    Route::get('/reports/sales', [ReportController::class, 'sales'])
        ->name('reports.sales');

    Route::get('/reports/customer-ledger', [ReportController::class, 'customerLedger'])
        ->name('reports.customer-ledger');
});

Route::middleware(['auth', 'checkRole:purchase'])->group(function () {
    Route::get('/purchase', [PurchaseController::class, 'create'])->name('purchase.index');
    Route::post('/purchase', [PurchaseController::class, 'store'])->name('purchase.store');

    Route::get('/purchase/{purchase}', [PurchaseController::class, 'show'])->name('purchase.show');
    Route::post('/purchase/{purchase}/payments', [PaymentController::class, 'storePurchasePayment'])
        ->name('purchase.payments.store');

    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');

    Route::get('/reports/purchases', [ReportController::class, 'purchases'])
        ->name('reports.purchases');

    Route::get('/reports/profit-loss', [ReportController::class, 'profitLoss'])
        ->name('reports.profit-loss');

    Route::get('/reports/expenses', [ReportController::class, 'expenses'])
        ->name('reports.expenses');

    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
});

Route::middleware(['auth', 'checkRole:inventory.view'])->group(function () {
    Route::get('/inventory', [InventoryController::class, 'index'])
        ->name('inventory.index');
});

Route::middleware(['auth', 'checkRole:inventory.manage'])->group(function () {
    Route::get('/inventory/edit', function () {
        return view('inventory-edit');
    })->name('inventory.edit');
});

Route::middleware(['auth', 'checkRole:sales'])->group(function () {
    Route::get('/customers', [CustomerController::class, 'index'])
        ->name('customers.index');

    Route::get('/customers/create', [CustomerController::class, 'create'])
        ->name('customers.create');

    Route::post('/customers', [CustomerController::class, 'store'])
        ->name('customers.store');

    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])
        ->name('customers.edit');

    Route::put('/customers/{customer}', [CustomerController::class, 'update'])
        ->name('customers.update');

    Route::patch('/customers/{customer}', [CustomerController::class, 'update'])
        ->name('customers.update');

    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])
        ->name('customers.destroy');

    Route::get('/reports', function () {
        return view('reports');
    })->name('reports.index');
});

Route::middleware(['auth', 'checkRole:inventory.manage'])->group(function () {
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::patch('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::patch('/products/{product}/quick-price', [ProductController::class, 'quickUpdateProductPrice'])
        ->name('products.quick-price');
    Route::patch('/products/{product}/variants/{variant}/quick-price', [ProductController::class, 'quickUpdateVariantPrice'])
        ->name('products.variants.quick-price');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::patch('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
