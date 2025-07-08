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
        ->middleware(\App\Http\Middleware\RoleSwitchMiddleware::class.':tutor')
        ->name('tutor.dashboard');

    Route::get('/student/dashboard', [StudentController::class, 'dashboard'])
        ->middleware(\App\Http\Middleware\RoleSwitchMiddleware::class.':student')
        ->name('student.dashboard');

    // Role switching routes
    Route::get('/role-switch/{role}', [RoleSwitchController::class, 'switchToRole'])->name('role.switch');
    Route::get('/role-switch-back', [RoleSwitchController::class, 'switchBack'])->name('role.switchBack');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Tutor profile routes
    Route::middleware(\App\Http\Middleware\RoleSwitchMiddleware::class.':tutor')->group(function () {
        Route::get('/tutor/profile', [TutorProfileController::class, 'show'])->name('tutor.profile.show');
        Route::get('/tutor/profile/edit', [TutorProfileController::class, 'edit'])->name('tutor.profile.edit');
        Route::put('/tutor/profile', [TutorProfileController::class, 'update'])->name('tutor.profile.update');
    });

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
    Route::middleware(\App\Http\Middleware\RoleSwitchMiddleware::class.':tutor')->group(function () {
        Route::get('/availability', [TutorController::class, 'availability'])->name('tutor.availability');
        Route::post('/availability', [TutorController::class, 'updateAvailability'])->name('tutor.availability.update');
    });

    // Review routes
    Route::post('/tutors/{tutor}/reviews', [TutorController::class, 'storeReview'])->name('tutors.reviews.store');
    Route::put('/reviews/{review}', [TutorController::class, 'updateReview'])->name('tutors.reviews.update');
    Route::delete('/reviews/{review}', [TutorController::class, 'destroyReview'])->name('tutors.reviews.destroy');
});

// Admin routes - Moved to separate domain
Route::domain(config('app.admin_domain'))->middleware(['auth', \App\Http\Middleware\RoleSwitchMiddleware::class.':admin', \App\Http\Middleware\SetLocale::class])->group(function () {
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
Route::middleware(['auth', \App\Http\Middleware\RoleSwitchMiddleware::class.':admin', \App\Http\Middleware\SetLocale::class])->prefix('admin')->name('admin.')->group(function () {
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
});

// Test route for Tutor and Subject relationships
Route::get('/test-relationships', function () {
    $tutor = \App\Models\Tutor::with('subjects')->first();
    $subject = \App\Models\Subject::with('tutors')->first();

    return response()->json([
        'tutor' => $tutor,
        'subject' => $subject,
    ]);
})->name('test.relationships');

// Test auth route
Route::middleware(['auth'])->get('/test-auth', function () {
    return response()->json([
        'authenticated' => true,
        'user_id' => \Illuminate\Support\Facades\Auth::id(),
        'user_email' => \Illuminate\Support\Facades\Auth::user()->email ?? 'no email',
    ]);
})->name('test.auth');

// Test booking access
Route::middleware(['auth'])->get('/test-booking/{booking}', function (\App\Models\Booking $booking) {
    return response()->json([
        'booking_id' => $booking->id,
        'student_id' => $booking->student_id,
        'current_user_id' => \Illuminate\Support\Facades\Auth::id(),
        'is_owner' => $booking->student_id === \Illuminate\Support\Facades\Auth::id(),
        'status' => $booking->status,
        'payment_status' => $booking->payment_status,
        'can_pay' => $booking->status === 'accepted' && $booking->payment_status !== 'paid',
        'price' => $booking->price,
        'tutor_user_id' => $booking->tutor->user_id ?? null,
        'is_tutor' => ($booking->tutor && $booking->tutor->user_id === \Illuminate\Support\Facades\Auth::id()),
    ]);
})->name('test.booking');

// Debug payment route
Route::middleware(['auth'])->post('/debug-payment/{booking}', function (\App\Models\Booking $booking, \Illuminate\Http\Request $request) {
    try {
        $user = \Illuminate\Support\Facades\Auth::user();

        return response()->json([
            'booking_exists' => true,
            'booking_id' => $booking->id,
            'user_id' => $user->id,
            'user_role' => $user->role,
            'is_student' => $booking->student_id === $user->id,
            'is_tutor' => $booking->tutor && $booking->tutor->user_id === $user->id,
            'booking_status' => $booking->status,
            'payment_status' => $booking->payment_status,
            'booking_price' => $booking->price,
            'request_data' => $request->all(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
})->name('debug.payment');

// Test VNPay success route
Route::middleware(['auth'])->get('/test-vnpay-success/{booking}', function (\App\Models\Booking $booking) {
    $fakeVnpayData = [
        'vnp_Amount' => ($booking->price * 100),
        'vnp_BankCode' => 'NCB',
        'vnp_CardType' => 'ATM',
        'vnp_OrderInfo' => 'Thanh toan hoc phi - ' . $booking->subject->name,
        'vnp_PayDate' => now()->format('YmdHis'),
        'vnp_ResponseCode' => '00', // Success code
        'vnp_TmnCode' => 'TEST123',
        'vnp_TransactionNo' => '14562789',
        'vnp_TxnRef' => $booking->vnpay_txn_ref ?: 'BOOKING_' . $booking->id . '_' . time(),
    ];

    // Simulate successful payment
    $booking->update([
        'payment_status' => 'paid',
        'payment_method' => 'vnpay',
    ]);

    return redirect()->route('bookings.show', $booking)
        ->with('success', 'Thanh toán thành công! (Test mode)');
})->name('test.vnpay.success');

// Rate limited routes
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/tutors/{tutor}', [TutorController::class, 'show'])->name('tutors.show');
    Route::post('/tutors/{tutor}/favorite', [TutorController::class, 'toggleFavorite'])->name('tutors.favorite');
    Route::get('/tutors/{tutor}/availability/{day}', [TutorController::class, 'checkAvailability'])->name('tutors.availability');
});

// VNPay Demo & Test routes
Route::middleware(['auth'])->group(function () {
    // VNPay Demo for users (general access)
    Route::get('/vnpay-demo', [PaymentController::class, 'showVnpayDemo'])->name('vnpay.demo.view');
    Route::post('/vnpay-demo', [PaymentController::class, 'createDemoVnpay'])->name('vnpay.demo.create');

    // VNPay Test for admin only
    Route::middleware(\App\Http\Middleware\RoleSwitchMiddleware::class.':admin')->group(function () {
        Route::get('/test-vnpay', [PaymentController::class, 'showVnpayTest'])->name('test.vnpay.view');
        Route::post('/test-vnpay', [PaymentController::class, 'createTestVnpay'])->name('test.vnpay');
        Route::post('/test-vnpay-ipn', [PaymentController::class, 'testVnpayIpn'])->name('test.vnpay.ipn');
    });

    // VNPay Result page
    Route::get('/vnpay-result', [PaymentController::class, 'showVnpayResult'])->name('vnpay.result');
});

require __DIR__.'/auth.php';
