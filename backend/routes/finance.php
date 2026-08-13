<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Finance\FinanceController;
use App\Http\Controllers\Finance\FinanceReportController;
use App\Http\Controllers\Finance\FinanceAnalysisController;

Route::middleware(['auth', 'finance.permission:finance.view'])->prefix('finance')->name('finance.')->group(function () {
    Route::get('/', [FinanceController::class, 'dashboard'])->name('dashboard');
    Route::get('/analysis', [FinanceAnalysisController::class, 'index'])->name('analysis');
    Route::post('/analysis/ask', [FinanceAnalysisController::class, 'ask'])->name('analysis.ask');
    Route::get('/reports', [FinanceReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/projects', [FinanceReportController::class, 'projects'])->name('reports.projects');
    Route::get('/reports/projects/export', [FinanceReportController::class, 'exportProjects'])->name('reports.projects.export');
    Route::get('/reports/expenses', [FinanceReportController::class, 'expenses'])->name('reports.expenses');
    Route::get('/reports/expenses/export', [FinanceReportController::class, 'exportExpenses'])->name('reports.expenses.export');
    Route::get('/reports/payments', [FinanceReportController::class, 'payments'])->name('reports.payments');
    Route::get('/reports/payments/export', [FinanceReportController::class, 'exportPayments'])->name('reports.payments.export');
    Route::get('/jobs', [FinanceController::class, 'jobs'])->name('jobs.index');
    Route::get('/jobs/{job}', [FinanceController::class, 'jobShow'])->name('jobs.show');
    Route::post('/jobs/{job}/expenses', [FinanceController::class, 'storeJobExpense'])
        ->middleware('finance.permission:finance.create')
        ->name('jobs.expenses.store');
    Route::get('/projects', [FinanceController::class, 'projects'])->name('projects.index');
    Route::get('/projects/{project}', [FinanceController::class, 'projectShow'])->name('projects.show');
    Route::post('/projects/{project}/expenses', [FinanceController::class, 'storeProjectExpense'])
        ->middleware('finance.permission:finance.create')
        ->name('projects.expenses.store');
    Route::post('/projects/{project}/financial', [FinanceController::class, 'saveProjectFinancial'])
        ->name('projects.financial.save');
    Route::post('/projects/{project}/payments', [FinanceController::class, 'storeProjectPayment'])
        ->middleware('finance.permission:finance.create')
        ->name('projects.payments.store');
    Route::put('/projects/{project}/payments/{payment}', [FinanceController::class, 'updateProjectPayment'])
        ->middleware('finance.permission:finance.edit')
        ->name('projects.payments.update');
    Route::delete('/projects/{project}/payments/{payment}', [FinanceController::class, 'destroyProjectPayment'])
        ->middleware('finance.permission:finance.delete')
        ->name('projects.payments.destroy');
    Route::get('/projects/{project}/material-costs/create', [FinanceController::class, 'createMaterialCost'])
        ->middleware('finance.permission:finance.create')
        ->name('material-costs.create');
    Route::post('/projects/{project}/material-costs', [FinanceController::class, 'storeMaterialCost'])
        ->middleware('finance.permission:finance.create')
        ->name('material-costs.store');
    Route::get('/material-costs/{materialCost}', [FinanceController::class, 'showMaterialCost'])->name('material-costs.show');
    Route::get('/material-costs/{materialCost}/edit', [FinanceController::class, 'editMaterialCost'])
        ->middleware('finance.permission:finance.edit')
        ->name('material-costs.edit');
    Route::put('/material-costs/{materialCost}', [FinanceController::class, 'updateMaterialCost'])
        ->middleware('finance.permission:finance.edit')
        ->name('material-costs.update');
    Route::post('/material-costs/{materialCost}/approve', [FinanceController::class, 'approveMaterialCost'])
        ->middleware('finance.permission:finance.approve')
        ->name('material-costs.approve');
    Route::post('/material-costs/{materialCost}/reject', [FinanceController::class, 'rejectMaterialCost'])
        ->middleware('finance.permission:finance.approve')
        ->name('material-costs.reject');
    Route::delete('/material-costs/{materialCost}', [FinanceController::class, 'destroyMaterialCost'])
        ->middleware('finance.permission:finance.delete')
        ->name('material-costs.destroy');
    Route::get('/expenses', [FinanceController::class, 'expenses'])->name('expenses.index');
    Route::get('/expenses/create', [FinanceController::class, 'create'])
        ->middleware('finance.permission:finance.create')
        ->name('expenses.create');
    Route::post('/expenses', [FinanceController::class, 'store'])
        ->middleware('finance.permission:finance.create')
        ->name('expenses.store');
    Route::get('/expenses/{expense}', [FinanceController::class, 'show'])->name('expenses.show');
    Route::get('/expenses/{expense}/edit', [FinanceController::class, 'edit'])
        ->middleware('finance.permission:finance.edit')
        ->name('expenses.edit');
    Route::put('/expenses/{expense}', [FinanceController::class, 'update'])
        ->middleware('finance.permission:finance.edit')
        ->name('expenses.update');
    Route::post('/expenses/{expense}/approve', [FinanceController::class, 'approve'])
        ->middleware('finance.permission:finance.approve')
        ->name('expenses.approve');
    Route::post('/expenses/{expense}/reject', [FinanceController::class, 'reject'])
        ->middleware('finance.permission:finance.approve')
        ->name('expenses.reject');
    Route::delete('/expenses/{expense}', [FinanceController::class, 'destroy'])
        ->middleware('finance.permission:finance.delete')
        ->name('expenses.destroy');
    Route::get('/documents/{document}/download', [FinanceController::class, 'downloadDocument'])
        ->name('documents.download');
});
