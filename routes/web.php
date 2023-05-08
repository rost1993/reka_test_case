<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TodoController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/todo', [TodoController::class, 'index'])->name('todo');
    Route::get('/todo_create', [TodoController::class, 'create'])->name('todo.create');
    Route::post('/todo_store', [TodoController::class, 'store'])->name('todo.store');
    Route::get('/todo_edit/{id}', [TodoController::class, 'edit'])->name('todo.edit');
    Route::post('/todo_update/{id}', [TodoController::class, 'update'])->name('todo.update');
    Route::post('/todo_destroy/{id}', [TodoController::class, 'destroy'])->name('todo.destroy');
    Route::post('/todo_destroy_file/{id}', [TodoController::class, 'destroy_file'])->name('todo.destroy_file');
    Route::post('/todo_destroy_tag/{id_todo_tag}', [TodoController::class, 'destroy_tag'])->name('todo.destroy_tag');
});

require __DIR__.'/auth.php';
