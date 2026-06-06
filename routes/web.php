<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GroupController as AdminGroupController;
use App\Http\Controllers\Admin\Subject\PartController as AdminPartController;
use App\Http\Controllers\Admin\Subject\PartQuestionController;
use App\Http\Controllers\Admin\SubjectController as AdminSubjectController;
use App\Http\Controllers\Admin\TestAccessController as AdminTestAccessController;
use App\Http\Controllers\Admin\UploadController as AdminUploadController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use Illuminate\Support\Facades\Route;

/* Public */
Route::get('/', [HomeController::class, 'index'])->name('home');

/* Auth */
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/* Admin */
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:administrator'])->group(function () {

    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::post('/uploads/image', [AdminUploadController::class, 'image'])->name('uploads.image');
    Route::resource('groups', AdminGroupController::class);
    Route::post('groups/{group}/students', [AdminGroupController::class, 'addStudent'])->name('groups.students.add');
    Route::delete('groups/{group}/students/{user}', [AdminGroupController::class, 'removeStudent'])->name('groups.students.remove');
    Route::resource('users', AdminUserController::class)->except(['show']);
    Route::resource('test-accesses', AdminTestAccessController::class)->except(['show']);

    /* Subjects */
    Route::resource('subjects', AdminSubjectController::class);

    /* Parts nested under subject (replaces topics + nusqas) */
    Route::resource('subjects/{subject}/parts', AdminPartController::class)
        ->except(['index'])
        ->names('subjects.parts');

    /* Questions under parts */
    Route::prefix('subjects/{subject}/parts/{part}/questions')
        ->name('subjects.parts.questions.')
        ->group(function () {
            Route::post('/one', [PartQuestionController::class, 'storeOne'])->name('store.one');
            Route::post('/multi', [PartQuestionController::class, 'storeMulti'])->name('store.multi');
            Route::post('/match', [PartQuestionController::class, 'storeMatch'])->name('store.match');
            Route::post('/group', [PartQuestionController::class, 'storeGroup'])->name('store.group');
            Route::get('/{question}/edit', [PartQuestionController::class, 'edit'])->name('edit');
            Route::put('/{question}', [PartQuestionController::class, 'update'])->name('update');
            Route::delete('/{question}', [PartQuestionController::class, 'destroy'])->name('destroy');
            Route::get('/{question}/details/{detail}/edit', [PartQuestionController::class, 'editDetail'])->name('details.edit');
            Route::put('/{question}/details/{detail}', [PartQuestionController::class, 'updateDetail'])->name('details.update');
            Route::delete('/{question}/details/{detail}', [PartQuestionController::class, 'destroyDetail'])->name('details.destroy');
        });
});

/* Student */
Route::prefix('student')->name('student.')->middleware(['auth', 'role:student'])->group(function () {
    Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
});
