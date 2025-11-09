<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetLocationController;
use App\Http\Controllers\MaintenanceLogController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\TechnicianAssetController;
use App\Http\Controllers\PendingAssetController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserCategoryController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\AssetTagController;
use App\Http\Controllers\ChecklistTemplateController;

// --- PUBLIC (GUEST) ROUTES ---
Route::get('/log/submit/{token}', [MaintenanceLogController::class, 'showPublicForm'])->name('public.log.form');
Route::post('/log/submit/{token}', [MaintenanceLogController::class, 'storePublicLog'])->name('public.log.store');
Route::get('/log/success', [MaintenanceLogController::class, 'showPublicSuccess'])->name('public.log.success');


// Main dashboard route
Route::get('/', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

// --- AUTHENTICATED USER ROUTES (All roles can access) ---
Route::middleware(['auth', 'verified'])->group(function () {
    // Asset Views
    Route::get('/assets', [AssetController::class, 'index'])->name('assets.index');
    Route::get('/assets/kanban', [AssetController::class, 'kanban'])->name('assets.kanban');
    Route::get('/assets/list', [AssetController::class, 'list'])->name('assets.list');
    Route::get('/assets/{asset}', [AssetController::class, 'show'])->name('assets.show');
    
    // Log Creation/Drafts
    Route::get('assets/{asset}/logs/create', [MaintenanceLogController::class, 'create'])->name('assets.logs.create');
    Route::post('assets/{asset}/logs', [MaintenanceLogController::class, 'store'])->name('assets.logs.store');
    
    Route::get('/my-drafts', [MaintenanceLogController::class, 'listDrafts'])->name('logs.drafts.index');
    Route::get('/logs/draft/{log}/edit', [MaintenanceLogController::class, 'editDraft'])->name('logs.draft.edit');
    Route::put('/logs/draft/{log}', [MaintenanceLogController::class, 'updateDraft'])->name('logs.draft.update');
    Route::delete('/logs/draft/{log}', [MaintenanceLogController::class, 'destroyDraft'])->name('logs.draft.destroy');
    
    // Location
    Route::get('assets/{asset}/location', [AssetLocationController::class, 'edit'])->name('assets.location.edit');
    Route::put('assets/{asset}/location', [AssetLocationController::class, 'update'])->name('assets.location.update');

    // Proposals
    Route::get('/proposals/create', [ProposalController::class, 'create'])->name('proposals.create');
    Route::post('/proposals', [ProposalController::class, 'store'])->name('proposals.store');

    // Technician Submissions
    Route::get('/submit-equipment', [TechnicianAssetController::class, 'create'])->name('technician.assets.create');
    Route::post('/submit-equipment', [TechnicianAssetController::class, 'store'])->name('technician.assets.store');
});

// --- MANAGER & ADMIN ROUTES ---
Route::middleware(['auth', 'manager'])->group(function () {
    Route::get('/reports/cost-analysis', [ReportController::class, 'costAnalysis'])->name('reports.cost');
    Route::post('assets/{asset}/generate-link', [MaintenanceLogController::class, 'generateSecureLink'])->name('assets.logs.generate_link');
});


// --- ADMIN-ONLY ROUTES ---
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    
    // --- FIX: Static routes MUST be defined BEFORE resource routes ---
    Route::get('tags/print', [AssetTagController::class, 'index'])->name('assets.tags.index');
    Route::get('tags/print/{asset}', [AssetTagController::class, 'show'])->name('assets.tags.show');
    // --- END FIX ---

    Route::resource('departments', DepartmentController::class);
    Route::resource('tags', TagController::class)->names('categories'); // This creates /tags/{tag}
    Route::resource('assets', AssetController::class)->except(['index', 'show']);

    Route::resource('users', UserController::class)->only(['index', 'edit', 'update', 'destroy', 'create', 'store']);
    Route::get('users/{user}/visibility', [UserCategoryController::class, 'edit'])->name('users.visibility.edit'); 
    Route::put('users/{user}/visibility', [UserCategoryController::class, 'update'])->name('users.visibility.update');

    Route::resource('webhooks', WebhookController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::post('webhooks/{webhook}/test', [WebhookController::class, 'test'])->name('webhooks.test');

    Route::get('/proposals', [ProposalController::class, 'index'])->name('proposals.index');
    Route::put('/proposals/{proposal}', [ProposalController::class, 'update'])->name('proposals.update');

    Route::get('/pending-assets', [PendingAssetController::class, 'index'])->name('pending.index');
    Route::get('/pending-assets/{asset}/approve', [PendingAssetController::class, 'edit'])->name('pending.edit');
    Route::put('/pending-assets/{asset}', [PendingAssetController::class, 'update'])->name('pending.update');
    Route::delete('/pending-assets/{asset}', [PendingAssetController::class, 'destroy'])->name('pending.destroy');
    
    // --- PM Checklist Template Builder Routes ---
    Route::resource('checklist-templates', ChecklistTemplateController::class);
    Route::post('checklist-templates/{checklistTemplate}/fields', [ChecklistTemplateController::class, 'storeField'])->name('checklist-templates.fields.store');
    Route::delete('checklist-templates/fields/{field}', [ChecklistTemplateController::class, 'destroyField'])->name('checklist-templates.fields.destroy');
    Route::post('checklist-templates/{checklistTemplate}/fields/{field}/move-up', [ChecklistTemplateController::class, 'moveFieldUp'])->name('checklist-templates.fields.moveUp');
    Route::post('checklist-templates/{checklistTemplate}/fields/{field}/move-down', [ChecklistTemplateController::class, 'moveFieldDown'])->name('checklist-templates.fields.moveDown');
});


// This file includes the login/register/profile routes
require __DIR__.'/auth.php';
