<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TransactionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::middleware('guest')->group(function () {
    Route::get('/', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('/', [AuthenticatedSessionController::class, 'store']);
});

// Protected routes
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');

    // Categories
    Route::resource('categories', CategoryController::class);
    Route::get('/api/categories', [CategoryController::class, 'api'])->name('categories.api');
    Route::get('/api/categories/grouped', [CategoryController::class, 'grouped'])->name('categories.grouped');

    // Transactions
    Route::resource('transactions', TransactionController::class);

    // Reports (we'll add this in step 3)
    Route::get('/reports', [App\Http\Controllers\ReportsController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/pdf', [App\Http\Controllers\ReportsController::class, 'exportPdf'])->name('reports.export.pdf');
    Route::get('/reports/export/excel', [App\Http\Controllers\ReportsController::class, 'exportExcel'])->name('reports.export.excel');
    Route::get('/reports/export/csv', [App\Http\Controllers\ReportsController::class, 'exportCsv'])->name('reports.export.csv');

});

require __DIR__ . '/auth.php';

