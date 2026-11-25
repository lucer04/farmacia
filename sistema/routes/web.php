<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoleController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');

Route::resource('categorias', CategoryController::class)->names('categorias')->except('show');
Route::resource('roles', RoleController::class)->names('roles');