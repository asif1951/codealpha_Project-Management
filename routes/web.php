<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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
});



Route::get('/create-task', function () {
    return Inertia::render('CreateTask');
})->middleware(['auth', 'verified'])->name('create-task');

Route::get('/all-tasks', function () {
    return Inertia::render('AllTask');
})->middleware(['auth', 'verified'])->name('all-tasks');

Route::get('/manage-users', function () {
    return Inertia::render('ManageUsers');
})->middleware(['auth', 'verified'])->name('manage-users');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Task routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/create-task', [TaskController::class, 'index'])->name('create-task');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::put('/tasks/{id}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::patch('/tasks/{id}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
});
//User Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/manage-users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::patch('/users/{user}/toggle-role', [UserController::class, 'toggleRole'])->name('users.toggle-role');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/user-tasks', [TaskController::class, 'getUserTasks'])->name('user-tasks');
});

Route::get('/', function () {
    $userTasks = [];
    
    if (auth()->check()) {
        $userTasks = \App\Models\Task::where('assigned_to', auth()->id())
            ->orWhere('created_by', auth()->id())
            ->orderBy('due_date', 'asc')
            ->get();
    }
    
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
        'userTasks' => $userTasks,  // টাস্কগুলি পাস করুন
    ]);
});
Route::middleware(['auth'])->group(function () {
    Route::get('/user-tasks', [TaskController::class, 'getUserTasks'])->name('user-tasks');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', [TaskController::class, 'getNotifications']);
    Route::post('/notifications/{id}/read', [TaskController::class, 'markNotificationAsRead']);
    Route::post('/notifications/read-all', [TaskController::class, 'markAllNotificationsAsRead']);
});

// For welcome page
Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

// Task status update route
Route::post('/tasks/{id}/status', [TaskController::class, 'updateStatus'])->middleware('auth');
Route::patch('/tasks/{id}/status', [TaskController::class, 'updateStatus'])->middleware('auth');
require __DIR__.'/auth.php';
