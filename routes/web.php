<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GroupController as AdminGroupController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\SubjectController as AdminSubjectController;
use App\Http\Controllers\Admin\Subject\TopicController as AdminTopicController;
use App\Http\Controllers\Admin\Subject\NusqaController as AdminNusqaController;
use App\Http\Controllers\Admin\Subject\TopicQuestionController;
use App\Http\Controllers\Admin\Subject\NusqaQuestionController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Public\HomeController;
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
    Route::resource('groups', AdminGroupController::class)->except(['show']);
    Route::resource('users', AdminUserController::class)->except(['show']);

    /* Subjects */
    Route::resource('subjects', AdminSubjectController::class);

    /* Topics nested under subject */
    Route::resource('subjects/{subject}/topics', AdminTopicController::class)
        ->except(['index'])
        ->names('subjects.topics');

    /* Nusqas nested under subject */
    Route::resource('subjects/{subject}/nusqas', AdminNusqaController::class)
        ->except(['index'])
        ->names('subjects.nusqas');

    /* Questions under topics */
    Route::prefix('subjects/{subject}/topics/{topic}/questions')
        ->name('subjects.topics.questions.')
        ->group(function () {
            Route::get('/',              [TopicQuestionController::class, 'index'])->name('index');
            Route::get('/create',        [TopicQuestionController::class, 'create'])->name('create');
            Route::post('/one',          [TopicQuestionController::class, 'storeOne'])->name('store.one');
            Route::post('/multi',        [TopicQuestionController::class, 'storeMulti'])->name('store.multi');
            Route::post('/match',        [TopicQuestionController::class, 'storeMatch'])->name('store.match');
            Route::post('/group',        [TopicQuestionController::class, 'storeGroup'])->name('store.group');
            Route::delete('/{question}', [TopicQuestionController::class, 'destroy'])->name('destroy');
        });

    Route::prefix('subjects/{subject}/nusqas/{nusqa}/questions')
        ->name('subjects.nusqas.questions.')
        ->group(function () {
            Route::get('/',              [NusqaQuestionController::class, 'index'])->name('index');
            Route::get('/create',        [NusqaQuestionController::class, 'create'])->name('create');
            Route::post('/one',          [NusqaQuestionController::class, 'storeOne'])->name('store.one');
            Route::post('/multi',        [NusqaQuestionController::class, 'storeMulti'])->name('store.multi');
            Route::post('/match',        [NusqaQuestionController::class, 'storeMatch'])->name('store.match');
            Route::post('/group',        [NusqaQuestionController::class, 'storeGroup'])->name('store.group');
            Route::delete('/{question}', [NusqaQuestionController::class, 'destroy'])->name('destroy');
        });
});

/* Student */
Route::prefix('student')->name('student.')->middleware(['auth', 'role:student'])->group(function () {
    Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
});
