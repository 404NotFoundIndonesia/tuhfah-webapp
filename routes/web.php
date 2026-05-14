<?php

use App\Http\Controllers\AdministratorController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\HonorariumController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\LearningProgressController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentGatewayController;
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

        // Payment — explicit routes so /export, /create, /webhook resolve before {payment}
        Route::prefix('payment')->name('payment.')->group(function () {
            Route::get('/', [PaymentController::class, 'index'])->name('index');
            Route::get('/create', [PaymentController::class, 'create'])->name('create');
            Route::post('/', [PaymentController::class, 'store'])->name('store');
            Route::get('/export', [PaymentController::class, 'export'])->name('export');
            Route::get('/{payment}', [PaymentController::class, 'show'])->name('show');
            Route::patch('/{payment}/mark-paid', [PaymentController::class, 'markPaid'])->name('mark-paid');
            Route::delete('/{payment}', [PaymentController::class, 'destroy'])->name('destroy');
        });

        Route::get('/my-child/payments', [PaymentController::class, 'guardianIndex'])->name('payment.guardian');

        // Honorarium
        Route::prefix('honorarium')->name('honorarium.')->group(function () {
            Route::get('/', [HonorariumController::class, 'index'])->name('index');
            Route::get('/create', [HonorariumController::class, 'create'])->name('create');
            Route::post('/', [HonorariumController::class, 'store'])->name('store');
            Route::get('/export', [HonorariumController::class, 'export'])->name('export');
            Route::get('/{honorarium}', [HonorariumController::class, 'show'])->name('show');
            Route::patch('/{honorarium}/mark-paid', [HonorariumController::class, 'markPaid'])->name('mark-paid');
            Route::delete('/{honorarium}', [HonorariumController::class, 'destroy'])->name('destroy');
        });

        // Payment gateway checkout (guardian only)
        Route::post('/payment/{payment}/checkout', [PaymentGatewayController::class, 'checkout'])->name('payment.checkout');

        // Announcements — explicit so /create resolves before {announcement}
        Route::prefix('announcement')->name('announcement.')->group(function () {
            Route::get('/', [AnnouncementController::class, 'index'])->name('index');
            Route::get('/create', [AnnouncementController::class, 'create'])->name('create');
            Route::post('/', [AnnouncementController::class, 'store'])->name('store');
            Route::get('/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('edit');
            Route::put('/{announcement}', [AnnouncementController::class, 'update'])->name('update');
            Route::patch('/{announcement}/publish', [AnnouncementController::class, 'publish'])->name('publish');
            Route::delete('/{announcement}', [AnnouncementController::class, 'destroy'])->name('destroy');
        });

        // Inventory — explicit routes so /create resolves before {inventory}
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/', [InventoryController::class, 'index'])->name('index');
            Route::get('/create', [InventoryController::class, 'create'])->name('create');
            Route::post('/', [InventoryController::class, 'store'])->name('store');
            Route::get('/{inventory}', [InventoryController::class, 'show'])->name('show');
            Route::get('/{inventory}/edit', [InventoryController::class, 'edit'])->name('edit');
            Route::put('/{inventory}', [InventoryController::class, 'update'])->name('update');
            Route::delete('/{inventory}', [InventoryController::class, 'destroy'])->name('destroy');
            Route::post('/{inventory}/log', [InventoryController::class, 'log'])->name('log');
        });

        // Notifications
        Route::prefix('notifications')->name('notification.')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::get('/count', [NotificationController::class, 'unreadCount'])->name('count');
            Route::patch('/read-all', [NotificationController::class, 'markAllRead'])->name('read-all');
            Route::patch('/{id}/read', [NotificationController::class, 'markRead'])->name('read');
        });

        Route::as('account.')->group(function () {
            Route::get('/account/profile', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::patch('/account/profile', [ProfileController::class, 'update'])->name('profile.update');
            Route::delete('/account/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
            Route::get('/account/change-password', [PasswordController::class, 'edit'])->name('password.edit');
            Route::get('/account/change-language', [PageController::class, 'locale'])->name('locale');
        });
    });

    // Announcement show — public, guests can view published public announcements
    Route::get('/announcement/{announcement}', [AnnouncementController::class, 'show'])->name('announcement.show');

    // Payment webhook — public, no auth (Midtrans calls it server-side)
    Route::post('/payment/webhook', [PaymentGatewayController::class, 'webhook'])->name('payment.webhook');

    require __DIR__.'/auth.php';
});
