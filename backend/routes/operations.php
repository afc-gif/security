<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Operations\AdminController;

// Admin Operational Dashboard (Admin & Manager)
Route::middleware(['auth', 'role:admin,manager'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
});

// Operations Admin API Routes
Route::middleware(['auth', 'admin'])->prefix('api')->group(function () {
    Route::get('/clients', [\App\Http\Controllers\Operations\ApiClientController::class, 'index']);
    Route::get('/inspections', [\App\Http\Controllers\Operations\ApiInspectionController::class, 'index']);
});

Route::middleware('auth')->prefix('api')->group(function () {
    Route::get('/products/search', [\App\Http\Controllers\Commerce\SolutionItemController::class, 'search']);
});

// Operations Admin Web Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    // Admin Notifications
    Route::post('/notifications/{notification}/read', [\App\Http\Controllers\Operations\NotificationController::class, 'markRead'])
        ->name('admin.notifications.read');

    // Clients Management
    Route::resource('clients', \App\Http\Controllers\Operations\ClientController::class)
        ->except(['show'])
        ->names('admin.clients');

    // Operations Overview
    Route::get('/operations', [\App\Http\Controllers\Operations\OperationsOverviewController::class, 'index'])
        ->name('admin.operations.overview');

    // Job Requests Management
    Route::get('/job-inbox', [\App\Http\Controllers\Operations\JobInboxController::class, 'index'])
        ->name('admin.job-inbox.index');
    Route::get('/service-categories', [\App\Http\Controllers\Operations\ServiceCategoryController::class, 'index'])
        ->name('admin.service-categories.index');
    Route::post('/service-categories', [\App\Http\Controllers\Operations\ServiceCategoryController::class, 'store'])
        ->name('admin.service-categories.store');
    Route::put('/service-categories/{serviceCategory}', [\App\Http\Controllers\Operations\ServiceCategoryController::class, 'update'])
        ->name('admin.service-categories.update');
    Route::delete('/service-categories/{serviceCategory}', [\App\Http\Controllers\Operations\ServiceCategoryController::class, 'destroy'])
        ->name('admin.service-categories.destroy');
    Route::post('/service-categories/{serviceCategory}/checklist-templates', [\App\Http\Controllers\Operations\ServiceCategoryController::class, 'storeTemplate'])
        ->name('admin.service-categories.templates.store');
    Route::put('/checklist-templates/{template}', [\App\Http\Controllers\Operations\ServiceCategoryController::class, 'updateTemplate'])
        ->name('admin.service-categories.templates.update');
    Route::delete('/checklist-templates/{template}', [\App\Http\Controllers\Operations\ServiceCategoryController::class, 'destroyTemplate'])
        ->name('admin.service-categories.templates.destroy');
    Route::resource('job-requests', \App\Http\Controllers\Operations\JobRequestController::class)
        ->only(['index', 'create', 'store', 'show', 'update', 'destroy'])
        ->names('admin.job-requests');
    Route::post('/job-items/{jobItem}/checklist', [\App\Http\Controllers\Operations\JobRequestController::class, 'addChecklistItem'])
        ->name('admin.job-items.checklist.store');
    Route::delete('/job-items/{jobItem}/checklist/{checklistItem}', [\App\Http\Controllers\Operations\JobRequestController::class, 'destroyChecklistItem'])
        ->name('admin.job-items.checklist.destroy');
    Route::get('/job-items/{jobItem}', [\App\Http\Controllers\Operations\JobItemController::class, 'show'])
        ->name('admin.job-items.show');
    Route::post('/job-items/{jobItem}/review', [\App\Http\Controllers\Operations\JobItemController::class, 'review'])
        ->name('admin.job-items.review');
    Route::post('/job-items/{jobItem}/reopen', [\App\Http\Controllers\Operations\JobItemController::class, 'reopen'])
        ->name('admin.job-items.reopen');
    Route::post('/job-items/{jobItem}/assign', [\App\Http\Controllers\Operations\JobItemController::class, 'assign'])
        ->name('admin.job-items.assign');
    Route::post('/job-items/{jobItem}/convert-to-project', [\App\Http\Controllers\Operations\JobItemController::class, 'convertToProject'])
        ->name('admin.job-items.convert-to-project');

    // Inspections Management
    Route::resource('inspections', \App\Http\Controllers\Operations\InspectionController::class)
        ->only(['index', 'create', 'store', 'show'])
        ->names('admin.inspections');
    Route::post('/inspections/{inspection}/review', [\App\Http\Controllers\Operations\InspectionController::class, 'review'])
        ->name('admin.inspections.review');
    Route::post('/inspections/{inspection}/convert-to-project', [\App\Http\Controllers\Operations\ProjectController::class, 'convertFromInspection'])
        ->name('admin.inspections.convert-to-project');

    // Projects Management
    Route::resource('projects', \App\Http\Controllers\Operations\ProjectController::class)
        ->only(['index', 'create', 'store', 'show'])
        ->names('admin.projects');
    Route::post('/projects/{project}/complete', [\App\Http\Controllers\Operations\ProjectController::class, 'complete'])
        ->name('admin.projects.complete');
    Route::post('/projects/{project}/reopen-work', [\App\Http\Controllers\Operations\ProjectController::class, 'reopenWork'])
        ->name('admin.projects.reopen-work');
    Route::delete('/projects/{project}', [\App\Http\Controllers\Operations\ProjectController::class, 'destroy'])
        ->name('admin.projects.destroy');
    Route::post('/project-updates/{update}/review', [\App\Http\Controllers\Operations\ProjectController::class, 'reviewUpdate'])
        ->name('admin.project-updates.review');


    // Tasks Management
    Route::resource('tasks', \App\Http\Controllers\Operations\TaskController::class)
        ->only(['index', 'create', 'store', 'show'])
        ->names('admin.tasks');

    // Field Reports Management
    Route::get('/field-reports', [\App\Http\Controllers\Operations\FieldReportController::class, 'index'])
        ->name('admin.field-reports.index');
});
