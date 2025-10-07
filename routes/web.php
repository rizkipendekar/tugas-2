<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\HabitController;
use App\Http\Controllers\CalendarController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Todo routes
    Route::get('/todos', [TodoController::class, 'index'])->name('todos.index');
    Route::post('/todos', [TodoController::class, 'store'])->name('todos.store');
    Route::patch('/todos/{todo}', [TodoController::class, 'update'])->name('todos.update');
    Route::delete('/todos/{todo}', [TodoController::class, 'destroy'])->name('todos.destroy');
    Route::patch('/todos/{todo}/toggle', [TodoController::class, 'toggle'])->name('todos.toggle');
    
    // Habit routes
    Route::get('/habits', [HabitController::class, 'index'])->name('habits.index');
    Route::post('/habits', [HabitController::class, 'store'])->name('habits.store');
    Route::patch('/habits/{habit}', [HabitController::class, 'update'])->name('habits.update');
    Route::delete('/habits/{habit}', [HabitController::class, 'destroy'])->name('habits.destroy');
    Route::post('/habits/{habit}/complete', [HabitController::class, 'complete'])->name('habits.complete');
    Route::delete('/habits/{habit}/uncomplete', [HabitController::class, 'uncomplete'])->name('habits.uncomplete');
    Route::get('/habits/{habit}/statistics', [HabitController::class, 'statistics'])->name('habits.statistics');
    
    // Calendar routes
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::post('/calendar/tasks', [CalendarController::class, 'store'])->name('calendar.tasks.store');
    Route::patch('/calendar/tasks/{task}', [CalendarController::class, 'update'])->name('calendar.tasks.update');
    Route::delete('/calendar/tasks/{task}', [CalendarController::class, 'destroy'])->name('calendar.tasks.destroy');
    Route::patch('/calendar/tasks/{task}/toggle', [CalendarController::class, 'toggleComplete'])->name('calendar.tasks.toggle');
    Route::get('/calendar/tasks/{date}', [CalendarController::class, 'getTasksForDate'])->name('calendar.tasks.date');
    Route::post('/calendar/goals', [CalendarController::class, 'storeGoal'])->name('calendar.goals.store');
    Route::get('/calendar/stats', [CalendarController::class, 'getStats'])->name('calendar.stats');
});

require __DIR__.'/auth.php';
