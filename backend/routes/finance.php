<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Finance\FinanceController;
use App\Http\Controllers\Finance\FinancePosController;
use App\Http\Controllers\Finance\FinanceQuotationController;
use App\Http\Controllers\Finance\FinanceReportController;
use App\Http\Controllers\Finance\FinanceAnalysisController;
use App\Http\Controllers\Finance\FinanceProcurementController;

Route::middleware(['auth', 'finance.permission:finance.view'])->prefix('finance')->name('finance.')->group(function () {
    Route::get('/', [FinanceController::class, 'dashboard'])->name('dashboard');
    Route::get('/quotations', [FinanceQuotationController::class, 'index'])->name('quotations.index');
    Route::get('/quotations/create', [FinanceQuotationController::class, 'create'])
        ->middleware('finance.permission:finance.create')
        ->name('quotations.create');
    Route::post('/quotations', [FinanceQuotationController::class, 'store'])
        ->middleware('finance.permission:finance.create')
        ->name('quotations.store');
    Route::get('/quotations/{quotation}', [FinanceQuotationController::class, 'show'])->name('quotations.show');
    Route::get('/quotations/{quotation}/download', [FinanceQuotationController::class, 'download'])->name('quotations.download');
    Route::get('/quotations/{quotation}/edit', [FinanceQuotationController::class, 'edit'])
        ->middleware('finance.permission:finance.edit')
        ->name('quotations.edit');
    Route::put('/quotations/{quotation}', [FinanceQuotationController::class, 'update'])
        ->middleware('finance.permission:finance.edit')
        ->name('quotations.update');
    Route::patch('/quotations/{quotation}/status', [FinanceQuotationController::class, 'updateStatus'])
        ->middleware('finance.permission:finance.edit')
        ->name('quotations.status');
    Route::delete('/quotations/{quotation}', [FinanceQuotationController::class, 'destroy'])
        ->middleware('finance.permission:finance.delete')
        ->name('quotations.destroy');
    Route::get('/analysis', [FinanceAnalysisController::class, 'index'])->name('analysis');
    Route::post('/analysis/ask', [FinanceAnalysisController::class, 'ask'])->name('analysis.ask');
    // Finance POS Sales — read-only view of completed POS orders
    Route::get('/pos-sales', [FinancePosController::class, 'index'])->name('pos-sales.index');
    Route::get('/reports', [FinanceReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/projects', [FinanceReportController::class, 'projects'])->name('reports.projects');
    Route::get('/reports/projects/export', [FinanceReportController::class, 'exportProjects'])->name('reports.projects.export');
    Route::get('/reports/expenses', [FinanceReportController::class, 'expenses'])->name('reports.expenses');
    Route::get('/reports/expenses/export', [FinanceReportController::class, 'exportExpenses'])->name('reports.expenses.export');
    Route::get('/reports/payments', [FinanceReportController::class, 'payments'])->name('reports.payments');
    Route::get('/reports/payments/export', [FinanceReportController::class, 'exportPayments'])->name('reports.payments.export');
    Route::get('/reports/procurements', [FinanceReportController::class, 'procurements'])->name('reports.procurements');
    Route::get('/reports/procurements/export', [FinanceReportController::class, 'exportProcurements'])->name('reports.procurements.export');
    Route::get('/jobs', [FinanceController::class, 'jobs'])->name('jobs.index');
    Route::get('/jobs/{job}', [FinanceController::class, 'jobShow'])->name('jobs.show');
    Route::post('/jobs/{job}/expenses', [FinanceController::class, 'storeJobExpense'])
        ->middleware('finance.permission:finance.create')
        ->name('jobs.expenses.store');
    Route::post('/jobs/{job}/payments', [FinanceController::class, 'storeJobPayment'])
        ->middleware('finance.permission:finance.create')
        ->name('jobs.payments.store');
    Route::post('/inspections/{inspection}/payments', [FinanceController::class, 'storeInspectionPayment'])
        ->middleware('finance.permission:finance.create')
        ->name('inspections.payments.store');
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

    // Office / General Company Expenses
    Route::get('/office-expenses', [FinanceController::class, 'officeExpenses'])->name('office-expenses.index');
    Route::get('/office-expenses/create', [FinanceController::class, 'createOfficeExpense'])
        ->middleware('finance.permission:finance.create')
        ->name('office-expenses.create');
    Route::post('/office-expenses', [FinanceController::class, 'storeOfficeExpense'])
        ->middleware('finance.permission:finance.create')
        ->name('office-expenses.store');
    Route::get('/office-expenses/{expense}', [FinanceController::class, 'showOfficeExpense'])->name('office-expenses.show');
    Route::get('/office-expenses/{expense}/edit', [FinanceController::class, 'editOfficeExpense'])
        ->middleware('finance.permission:finance.edit')
        ->name('office-expenses.edit');
    Route::put('/office-expenses/{expense}', [FinanceController::class, 'updateOfficeExpense'])
        ->middleware('finance.permission:finance.edit')
        ->name('office-expenses.update');
    Route::post('/office-expenses/{expense}/approve', [FinanceController::class, 'approveOfficeExpense'])
        ->middleware('finance.permission:finance.approve')
        ->name('office-expenses.approve');
    Route::post('/office-expenses/{expense}/reject', [FinanceController::class, 'rejectOfficeExpense'])
        ->middleware('finance.permission:finance.approve')
        ->name('office-expenses.reject');
    Route::delete('/office-expenses/{expense}', [FinanceController::class, 'destroyOfficeExpense'])
        ->middleware('finance.permission:finance.delete')
        ->name('office-expenses.destroy');

    // Procurement & Inventory
    Route::get('/procurements', [FinanceProcurementController::class, 'index'])->name('procurements.index');
    Route::get('/procurements/create', [FinanceProcurementController::class, 'create'])
        ->middleware('finance.permission:finance.create')
        ->name('procurements.create');
    Route::post('/procurements', [FinanceProcurementController::class, 'store'])
        ->middleware('finance.permission:finance.create')
        ->name('procurements.store');
    Route::delete('/procurements/{procurement}', [FinanceProcurementController::class, 'destroy'])
        ->middleware('finance.permission:finance.delete')
        ->name('procurements.destroy');

    // Suppliers CRUD
    Route::post('/suppliers', [FinanceProcurementController::class, 'storeSupplier'])
        ->middleware('finance.permission:finance.create')
        ->name('suppliers.store');
    Route::get('/suppliers/{supplier}/edit', [FinanceProcurementController::class, 'editSupplier'])
        ->middleware('finance.permission:finance.edit')
        ->name('suppliers.edit');
    Route::put('/suppliers/{supplier}', [FinanceProcurementController::class, 'updateSupplier'])
        ->middleware('finance.permission:finance.edit')
        ->name('suppliers.update');
    Route::delete('/suppliers/{supplier}', [FinanceProcurementController::class, 'destroySupplier'])
        ->middleware('finance.permission:finance.delete')
        ->name('suppliers.destroy');

    // Inventory Products CRUD
    Route::post('/products', [FinanceProcurementController::class, 'storeProduct'])
        ->middleware('finance.permission:finance.create')
        ->name('products.store');
    Route::get('/products/{product}/edit', [FinanceProcurementController::class, 'editProduct'])
        ->middleware('finance.permission:finance.edit')
        ->name('products.edit');
    Route::put('/products/{product}', [FinanceProcurementController::class, 'updateProduct'])
        ->middleware('finance.permission:finance.edit')
        ->name('products.update');
    Route::delete('/products/{product}', [FinanceProcurementController::class, 'destroyProduct'])
        ->middleware('finance.permission:finance.delete')
        ->name('products.destroy');
});

