<?php

use App\Http\Controllers\AdministratorController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentGuardianController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;

Route::middleware(['locale'])->group(function () {

    Route::get('/', [PageController::class, 'welcome'])->name('welcome');

    Route::middleware(['auth'])->group(function () {

        Route::middleware('verified')->group(function () {

            Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard');

        });

        Route::resource('administrator', AdministratorController::class);
        Route::resource('teacher', TeacherController::class);
        Route::resource('student-guardian', StudentGuardianController::class);

        Route::resource('student', StudentController::class);

        Route::as('account.')->group(function () {
            Route::get('/account/profile', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::patch('/account/profile', [ProfileController::class, 'update'])->name('profile.update');
            Route::delete('/account/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
            Route::get('/account/change-password', [PasswordController::class, 'edit'])->name('password.edit');
            Route::get('/account/change-language', [PageController::class, 'locale'])->name('locale');
        });
    });

    require __DIR__.'/auth.php';
});
