<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GroupController as AdminGroupController;
use App\Http\Controllers\Admin\RatingController as AdminRatingController;
use App\Http\Controllers\Admin\ResultController as AdminResultController;
use App\Http\Controllers\Admin\Subject\PartController as AdminPartController;
use App\Http\Controllers\Admin\Subject\PartQuestionController;
use App\Http\Controllers\Admin\SubjectController as AdminSubjectController;
use App\Http\Controllers\Admin\TestAccessController as AdminTestAccessController;
use App\Http\Controllers\Admin\UploadController as AdminUploadController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\TestController as StudentTestController;
use Illuminate\Support\Facades\Route;

/* Public */
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/locale/{locale}', [LocaleController::class, 'update'])->name('locale.update');

/* Auth */
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/* Artisan Commands */
Route::get('optimize', function () {
    echo Artisan::call('optimize');
});
Route::get('storage:link', function () {
    echo Artisan::call('storage:link');
});
Route::get('storage:unlink', function () {
    echo Artisan::call('storage:unlink');
});

/* Admin */
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:administrator'])->group(function () {

    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::post('/uploads/image', [AdminUploadController::class, 'image'])->name('uploads.image');
    Route::get('results', [AdminResultController::class, 'index'])->name('results.index');
    Route::get('results/export', [AdminResultController::class, 'export'])->name('results.export');
    Route::get('results/{test}', [AdminResultController::class, 'show'])->name('results.show');
    Route::get('rating', [AdminRatingController::class, 'index'])->name('rating.index');
    Route::resource('groups', AdminGroupController::class);
    Route::get('groups/{group}/results', [AdminGroupController::class, 'results'])->name('groups.results');
    Route::post('groups/{group}/students', [AdminGroupController::class, 'addStudent'])->name('groups.students.add');
    Route::delete('groups/{group}/students/{user}', [AdminGroupController::class, 'removeStudent'])->name('groups.students.remove');
    Route::resource('users', AdminUserController::class)->except(['show']);
    Route::get('users/archive', [AdminUserController::class, 'archive'])->name('users.archive');
    Route::get('users/{user}/results', [AdminUserController::class, 'results'])->name('users.results');
    Route::patch('users/{user}/restore', [AdminUserController::class, 'restore'])->name('users.restore')->withTrashed();
    Route::patch('users/{user}/grant-trial', [AdminUserController::class, 'grantTrial'])->name('users.grant-trial');
    Route::resource('test-accesses', AdminTestAccessController::class)->except(['show']);
    Route::get('test-accesses/{test_access}/results', [AdminTestAccessController::class, 'results'])->name('test-accesses.results');
    Route::patch('test-accesses/{test_access}/toggle-active', [AdminTestAccessController::class, 'toggleActive'])->name('test-accesses.toggle-active');

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
    Route::get('/tests/history', [StudentDashboardController::class, 'history'])->name('tests.history');
    Route::get('/info', [StudentDashboardController::class, 'info'])->name('info');

    Route::get('/test/{access}', [StudentTestController::class, 'index'])->name('test.index');
    Route::post('/test/{access}/start', [StudentTestController::class, 'start'])->name('test.start');
    Route::get('/test/{test}/process', [StudentTestController::class, 'process'])->name('test.process');
    Route::post('/test/{test}/save', [StudentTestController::class, 'save'])->name('test.save');
    Route::post('/test/{test}/finish', [StudentTestController::class, 'finish'])->name('test.finish');
    Route::get('/test/{test}/result', [StudentTestController::class, 'result'])->name('test.result');
    Route::get('/test/{test}/detail', [StudentTestController::class, 'detail'])->name('test.detail');
});
