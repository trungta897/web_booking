<?php

use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleSwitchController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TutorController;
use App\Http\Controllers\TutorPayoutController;
use App\Http\Controllers\TutorProfileController;
use Illuminate\Support\Facades\Route;

// Language switching route (available to all users)
Route::get('/language/{locale}', [LanguageController::class, 'switchLanguage'])->name('language.switch');

// Public routes
Route::get('/', [PageController::class, 'index'])->name('home');

Route::get('/find-tutors', [TutorController::class, 'index'])->name('tutors.index');
Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
Route::get('/subjects/{subject}/tutors', [SubjectController::class, 'listTutorsForSubject'])->name('subjects.tutors');
Route::get('/how-it-works', [PageController::class, 'howItWorks'])->name('how-it-works');
Route::get('/pricing', [PageController::class, 'pricing'])->name('pricing');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);
});

// Protected routes (require authentication)
Route::middleware(['auth'])->group(function () {
    Route::get('/tutor/dashboard', [TutorController::class, 'dashboard'])
        ->middleware(\App\Http\Middleware\RoleSwitchMiddleware::class . ':tutor')
        ->name('tutor.dashboard');

    Route::get('/student/dashboard', [StudentController::class, 'dashboard'])
        ->middleware(\App\Http\Middleware\RoleSwitchMiddleware::class . ':student')
        ->name('student.dashboard');

    // Role switching routes
    Route::get('/role-switch/{role}', [RoleSwitchController::class, 'switchToRole'])->name('role.switch');
    Route::get('/role-switch-back', [RoleSwitchController::class, 'switchBack'])->name('role.switchBack');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/education', [ProfileController::class, 'updateEducation'])->name('profile.update-education');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
    Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar'])->name('profile.upload-avatar');

    // Booking routes
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create/{tutor}', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings/{tutor}', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::get('/bookings/{booking}/student-profile', [BookingController::class, 'showStudentProfile'])->name('bookings.student-profile');
    Route::get('/bookings/{booking}/payment', [BookingController::class, 'payment'])->name('bookings.payment');
    Route::put('/bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update');
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');

    // Message routes
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{user}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{user}', [MessageController::class, 'store'])->name('messages.store');

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}', [NotificationController::class, 'show'])->name('notifications.show');
    Route::post('/notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Favorite routes
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/tutors/{tutor}/favorite', [TutorController::class, 'toggleFavorite'])->name('tutors.favorite');

    // Payment routes
    Route::post('/bookings/{booking}/payment-intent', [PaymentController::class, 'createIntent'])->name('payments.create-intent');
    Route::get('/bookings/{booking}/payment/confirm', [PaymentController::class, 'confirm'])->name('payments.confirm');
    Route::post('/bookings/{booking}/payment/process', [PaymentController::class, 'processPayment'])->name('payments.process');
    Route::post('/bookings/{booking}/payment/vnpay', [PaymentController::class, 'createVnpayPayment'])->name('payments.vnpay.create');
    Route::get('/payments/vnpay/return', [PaymentController::class, 'vnpayReturn'])->name('payments.vnpay.return');
    Route::post('/payments/vnpay/ipn', [PaymentController::class, 'vnpayIpn'])->name('payments.vnpay.ipn');
    Route::get('/bookings/{booking}/transactions', [PaymentController::class, 'getTransactionHistory'])->name('payments.transactions');
    Route::get('/bookings/{booking}/transactions/view', [PaymentController::class, 'viewTransactionHistory'])->name('payments.transactions.view');
    Route::get('/bookings/{booking}/transaction-history', [PaymentController::class, 'viewTransactionHistory'])->name('bookings.transactions');
    Route::post('/webhook/stripe', [PaymentController::class, 'handleWebhook'])->name('payments.webhook');

    // Refund routes
    Route::post('/bookings/{booking}/refund', [PaymentController::class, 'processRefund'])->name('payments.refund');
    Route::get('/bookings/{booking}/refund/confirm', [PaymentController::class, 'confirmRefund'])->name('payments.refund.confirm');

    // Tutor availability routes
    Route::get('/tutors/{tutor}/availability/{day}', [TutorController::class, 'checkAvailability'])->name('tutors.availability');
    Route::middleware(\App\Http\Middleware\RoleSwitchMiddleware::class . ':tutor')->group(function () {
        Route::get('/availability', [TutorController::class, 'availability'])->name('tutor.availability');
        Route::post('/availability', [TutorController::class, 'updateAvailability'])->name('tutor.availability.update');
        Route::get('/calendar/bookings/{date}', [TutorController::class, 'getBookingsForDate'])->name('tutor.calendar.bookings');

        // Tutor earnings and payouts
        Route::prefix('earnings')->name('tutors.earnings.')->group(function () {
            Route::get('/', [TutorPayoutController::class, 'index'])->name('index');
            Route::get('/details', [TutorPayoutController::class, 'earnings'])->name('details');
            Route::get('/payout/create', [TutorPayoutController::class, 'create'])->name('payout.create');
            Route::post('/payout', [TutorPayoutController::class, 'store'])->name('payout.store');
            Route::get('/history', [TutorPayoutController::class, 'history'])->name('history');
            Route::get('/payout/{payout}', [TutorPayoutController::class, 'show'])->name('payout.show');
        });
    });

    // Review routes
    Route::post('/tutors/{tutor}/reviews', [TutorController::class, 'storeReview'])->name('tutors.reviews.store');
    Route::put('/reviews/{review}', [TutorController::class, 'updateReview'])->name('tutors.reviews.update');
    Route::delete('/reviews/{review}', [TutorController::class, 'destroyReview'])->name('tutors.reviews.destroy');
});

// Admin routes - Moved to separate domain
Route::domain(config('app.admin_domain'))->middleware(['auth', \App\Http\Middleware\RoleSwitchMiddleware::class . ':admin', \App\Http\Middleware\SetLocale::class])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/tutors', [AdminController::class, 'tutors'])->name('admin.tutors');
    Route::get('/students', [AdminController::class, 'students'])->name('admin.students');
    Route::get('/bookings', [AdminController::class, 'bookings'])->name('admin.bookings');
    Route::get('/subjects', [AdminController::class, 'subjects'])->name('admin.subjects');
    Route::post('/subjects', [AdminController::class, 'storeSubject'])->name('admin.subjects.store');
    Route::put('/subjects/{subject}', [AdminController::class, 'updateSubject'])->name('admin.subjects.update');
    Route::delete('/subjects/{subject}', [AdminController::class, 'destroySubject'])->name('admin.subjects.destroy');
    Route::get('/reports', [AdminController::class, 'reports'])->name('admin.reports');

    // Fallback for backward compatibility - these will be redirected by middleware
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/tutors', [AdminController::class, 'tutors']);
        Route::get('/students', [AdminController::class, 'students']);
        Route::get('/bookings', [AdminController::class, 'bookings']);
        Route::get('/subjects', [AdminController::class, 'subjects']);
        Route::get('/reports', [AdminController::class, 'reports']);
    });
});

// Fallback for backward compatibility - these will be redirected by middleware
Route::middleware(['auth', \App\Http\Middleware\RoleSwitchMiddleware::class . ':admin', \App\Http\Middleware\SetLocale::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Tutors
    Route::get('/tutors', [AdminController::class, 'tutors'])->name('tutors');
    Route::get('/tutors/{user}', [AdminController::class, 'showTutor'])->name('tutors.show');
    Route::patch('/tutors/{user}/suspend', [AdminController::class, 'suspendTutor'])->name('tutors.suspend');

    // Students
    Route::get('/students', [AdminController::class, 'students'])->name('students');
    Route::get('/students/{user}', [AdminController::class, 'showStudent'])->name('students.show');
    Route::patch('/students/{user}/suspend', [AdminController::class, 'suspendStudent'])->name('students.suspend');

    // Bookings
    Route::get('/bookings', [AdminController::class, 'bookings'])->name('bookings');
    Route::get('/bookings/{booking}', [AdminController::class, 'showBooking'])->name('bookings.show');

    // Subjects
    Route::get('/subjects', [AdminController::class, 'subjects'])->name('subjects');
    Route::get('/subjects/create', [AdminController::class, 'createSubject'])->name('subjects.create');
    Route::post('/subjects', [AdminController::class, 'storeSubject'])->name('subjects.store');
    Route::get('/subjects/{subject}', [AdminController::class, 'showSubject'])->name('subjects.show');
    Route::get('/subjects/{subject}/edit', [AdminController::class, 'editSubject'])->name('subjects.edit');
    Route::put('/subjects/{subject}', [AdminController::class, 'updateSubject'])->name('subjects.update');
    Route::get('/subjects/{subject}/confirm-delete', [AdminController::class, 'confirmDeleteSubject'])->name('subjects.confirm-delete');
    Route::delete('/subjects/{subject}', [AdminController::class, 'destroySubject'])->name('subjects.destroy');

    // Reports
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');

    // Reviews
    Route::get('/reviews', [AdminController::class, 'reviews'])->name('reviews');

    // Admin Profile
    Route::get('/profile', [AdminProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile/suspend', [AdminProfileController::class, 'suspend'])->name('profile.suspend');

    // Refund management routes
    Route::get('/refunds', [App\Http\Controllers\AdminRefundController::class, 'index'])->name('refunds');
    Route::get('/refunds/{refund}/details', [App\Http\Controllers\AdminRefundController::class, 'details'])->name('refunds.details');
    Route::post('/refunds/{booking}/start-processing', [App\Http\Controllers\AdminRefundController::class, 'startProcessing'])->name('refunds.start-processing');
    Route::post('/refunds/{booking}/complete', [App\Http\Controllers\AdminRefundController::class, 'complete'])->name('refunds.complete');

    // Payout management routes
    Route::get('/payouts', [App\Http\Controllers\AdminPayoutController::class, 'index'])->name('payouts.index');
    Route::get('/payouts/analytics', [App\Http\Controllers\AdminPayoutController::class, 'analytics'])->name('payouts.analytics');
    Route::get('/payouts/export', [App\Http\Controllers\AdminPayoutController::class, 'export'])->name('payouts.export');
    Route::get('/payouts/{payout}', [App\Http\Controllers\AdminPayoutController::class, 'show'])->name('payouts.show');
    Route::post('/payouts/{payout}/approve', [App\Http\Controllers\AdminPayoutController::class, 'approve'])->name('payouts.approve');
    Route::post('/payouts/{payout}/reject', [App\Http\Controllers\AdminPayoutController::class, 'reject'])->name('payouts.reject');
    Route::post('/payouts/{payout}/complete', [App\Http\Controllers\AdminPayoutController::class, 'complete'])->name('payouts.complete');
});

// Rate limited routes
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/tutors/{tutor}', [TutorController::class, 'show'])->name('tutors.show');
    Route::post('/tutors/{tutor}/favorite', [TutorController::class, 'toggleFavorite'])->name('tutors.favorite');
    Route::get('/tutors/{tutor}/availability/{day}', [TutorController::class, 'checkAvailability'])->name('tutors.availability');
});

// VNPay Result page (can accept query parameters from redirects)
Route::middleware(['auth'])->get('/vnpay-result', [PaymentController::class, 'showVnpayResult'])->name('vnpay.result');

require __DIR__ . '/auth.php';

// Clear avatar route
Route::post('/clear-avatar', function () {
    $user = \Illuminate\Support\Facades\Auth::user();
    $oldAvatar = $user->avatar;

    $user->avatar = null;
    $user->save();

    return response()->json([
        'success' => true,
        'message' => 'Avatar cleared successfully',
        'old_avatar' => $oldAvatar,
        'user_id' => $user->id,
    ]);
})->middleware('auth');


