<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Field\FieldDashboardController;
use App\Http\Controllers\Field\InspectionController as FieldInspectionController;
use App\Http\Controllers\Field\JobController as FieldJobController;
use App\Http\Controllers\Field\ProjectController as FieldProjectController;
use App\Http\Controllers\Field\TaskController as FieldTaskController;
use App\Http\Controllers\Field\CoordinatorJobAssignmentController;

// Field Staff Routes
Route::middleware(['auth', 'role:field_staff,field_coordinator'])->prefix('field')->group(function () {
    Route::get('/dashboard', [FieldDashboardController::class, 'index'])->name('field.dashboard');
    Route::get('/dashboard/pending-assignments', [FieldDashboardController::class, 'pendingAssignments'])->name('field.dashboard.pending-assignments');
    Route::get('/inspections', [FieldInspectionController::class, 'index'])->name('field.inspections.index');
    Route::get('/inspections/{inspection}', [FieldInspectionController::class, 'show'])->name('field.inspections.show');
    Route::post('/inspections/{inspection}/submit', [FieldInspectionController::class, 'submitReport'])->name('field.inspections.submit');
    Route::get('/jobs', [FieldJobController::class, 'index'])->name('field.jobs.index');
    Route::post('/jobs/{jobItem}/claim', [FieldJobController::class, 'claim'])->name('field.jobs.claim');
    Route::get('/jobs/{jobItem}', [FieldJobController::class, 'show'])->name('field.jobs.show');
    Route::post('/jobs/{jobItem}/submit', [FieldJobController::class, 'submit'])->name('field.jobs.submit');
    Route::get('/projects', [FieldProjectController::class, 'index'])->name('field.projects.index');
    Route::get('/projects/{project}', [FieldProjectController::class, 'show'])->name('field.projects.show');
    Route::post('/projects/{project}/start-update', [FieldProjectController::class, 'startUpdate'])->name('field.projects.start-update');
    Route::post('/projects/{project}/updates', [FieldProjectController::class, 'submitUpdate'])->name('field.projects.submit-update');
    Route::post('/projects/{project}/release-update', [FieldProjectController::class, 'releaseUpdate'])->name('field.projects.release-update');
    Route::patch('/projects/{project}/requirements/{requirement}', [FieldProjectController::class, 'updateRequirement'])->name('field.projects.requirements.update');
    Route::get('/tasks', [FieldTaskController::class, 'index'])->name('field.tasks.index');
    Route::get('/tasks/{task}', [FieldTaskController::class, 'show'])->name('field.tasks.show');
    Route::patch('/tasks/{task}/status', [FieldTaskController::class, 'updateStatus'])->name('field.tasks.update-status');
});

// Field Coordinator Routes
Route::middleware(['auth', 'role:field_coordinator'])->prefix('coordinator')->group(function () {
    Route::get('/jobs', [CoordinatorJobAssignmentController::class, 'index'])->name('coordinator.jobs.index');
    Route::post('/jobs/{jobItem}/checklist', [CoordinatorJobAssignmentController::class, 'addChecklistItem'])->name('coordinator.jobs.checklist.store');
    Route::delete('/jobs/{jobItem}/checklist/{checklistItem}', [CoordinatorJobAssignmentController::class, 'destroyChecklistItem'])->name('coordinator.jobs.checklist.destroy');
    Route::post('/jobs/{jobItem}/assign', [CoordinatorJobAssignmentController::class, 'assign'])->name('coordinator.jobs.assign');
    Route::post('/jobs/{jobItem}/claim', [CoordinatorJobAssignmentController::class, 'claim'])->name('coordinator.jobs.claim');
    Route::post('/jobs/{jobItem}/release', [CoordinatorJobAssignmentController::class, 'release'])->name('coordinator.jobs.release');
    Route::post('/jobs/{jobItem}/review', [CoordinatorJobAssignmentController::class, 'review'])->name('coordinator.jobs.review');
});
