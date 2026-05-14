<?php

use App\Http\Controllers\AdministratorController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\LearningProgressController;
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

        // Attendance — explicit routes so /attendance/self and /attendance/summary
        // are resolved before the {attendance} wildcard
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/', [AttendanceController::class, 'index'])->name('index');
            Route::get('/create', [AttendanceController::class, 'create'])->name('create');
            Route::post('/', [AttendanceController::class, 'store'])->name('store');
            Route::get('/self', [AttendanceController::class, 'selfCreate'])->name('self');
            Route::post('/self', [AttendanceController::class, 'selfStore'])->name('self.store');
            Route::get('/summary', [AttendanceController::class, 'summary'])->name('summary');
            Route::get('/{attendance}', [AttendanceController::class, 'show'])->name('show');
        });

        Route::get('/my-child/attendance', [AttendanceController::class, 'guardianIndex'])->name('attendance.guardian');

        // Learning Progress — explicit routes so /chart-data resolves before {learningProgress}
        Route::prefix('learning-progress')->name('learning-progress.')->group(function () {
            Route::get('/', [LearningProgressController::class, 'index'])->name('index');
            Route::get('/create', [LearningProgressController::class, 'create'])->name('create');
            Route::post('/', [LearningProgressController::class, 'store'])->name('store');
            Route::get('/chart-data', [LearningProgressController::class, 'chartData'])->name('chart-data');
            Route::get('/{learningProgress}', [LearningProgressController::class, 'show'])->name('show');
            Route::get('/{learningProgress}/edit', [LearningProgressController::class, 'edit'])->name('edit');
            Route::put('/{learningProgress}', [LearningProgressController::class, 'update'])->name('update');
            Route::delete('/{learningProgress}', [LearningProgressController::class, 'destroy'])->name('destroy');
        });

        Route::get('/my-child/progress', [LearningProgressController::class, 'guardianIndex'])->name('learning-progress.guardian');

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
