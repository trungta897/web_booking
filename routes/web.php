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

// NEW: Comprehensive payment validation debug route
Route::middleware(['auth'])->get('/debug-payment-validation/{booking}', function (\App\Models\Booking $booking) {
    try {
        $user = \Illuminate\Support\Facades\Auth::user();

        // Load fresh relationships
        $booking->load(['transactions', 'tutor', 'student']);

        // Check each validation step
        $validationResults = [];

        // 1. Check booking access
        try {
            $isStudent = $booking->student_id === $user->id;
            $isTutor = $booking->tutor && $booking->tutor->user_id === $user->id;
            $isAdmin = $user->role === 'admin';

            $hasAccess = $isStudent || $isTutor || $isAdmin;
            $validationResults['booking_access'] = [
                'passed' => $hasAccess,
                'is_student' => $isStudent,
                'is_tutor' => $isTutor,
                'is_admin' => $isAdmin,
                'error' => $hasAccess ? null : 'No access to this booking',
            ];
        } catch (\Exception $e) {
            $validationResults['booking_access'] = [
                'passed' => false,
                'error' => $e->getMessage(),
            ];
        }

        // 2. Check payment permission
        try {
            $canPay = $booking->student_id === $user->id;
            $validationResults['payment_permission'] = [
                'passed' => $canPay,
                'booking_student_id' => $booking->student_id,
                'current_user_id' => $user->id,
                'error' => $canPay ? null : 'Only student who made booking can pay',
            ];
        } catch (\Exception $e) {
            $validationResults['payment_permission'] = [
                'passed' => false,
                'error' => $e->getMessage(),
            ];
        }

        // 3. Check booking status validations
        $statusValidations = [];

        // Check cancelled
        $isCancelled = $booking->status === 'cancelled';
        $statusValidations['not_cancelled'] = [
            'passed' => !$isCancelled,
            'booking_status' => $booking->status,
            'error' => $isCancelled ? 'Booking is cancelled' : null,
        ];

        // Check accepted
        $isAccepted = $booking->status === 'accepted';
        $statusValidations['is_accepted'] = [
            'passed' => $isAccepted,
            'booking_status' => $booking->status,
            'error' => !$isAccepted ? 'Booking is not accepted by tutor' : null,
        ];

        // Check if fully paid
        $isFullyPaid = $booking->isFullyPaid();
        $paymentStatusIsPaid = $booking->payment_status === 'paid';
        $hasCompletedTransactions = $booking->transactions()
            ->where('type', 'payment')
            ->where('status', 'completed')
            ->exists();

        $statusValidations['not_fully_paid'] = [
            'passed' => !$isFullyPaid,
            'is_fully_paid' => $isFullyPaid,
            'payment_status' => $booking->payment_status,
            'payment_status_is_paid' => $paymentStatusIsPaid,
            'has_completed_transactions' => $hasCompletedTransactions,
            'error' => $isFullyPaid ? 'Booking is already fully paid' : null,
        ];

        // Check active transactions - UPDATED: Không block dựa trên pending transaction nữa
        $hasActiveTransaction = $booking->transactions()
            ->where('status', 'pending')
            ->where('type', 'payment')
            ->where('created_at', '>', now()->subMinutes(2))
            ->exists();

        $statusValidations['no_active_transactions'] = [
            'passed' => true, // Luôn pass - không block dựa trên pending transaction
            'has_active_transaction' => $hasActiveTransaction,
            'note' => 'Pending transaction check disabled - allows immediate retry',
            'error' => null,
        ];

        $validationResults['booking_status_validation'] = $statusValidations;

        // Overall validation result
        $allValidationsPassed = $validationResults['booking_access']['passed'] &&
                               $validationResults['payment_permission']['passed'] &&
                               $statusValidations['not_cancelled']['passed'] &&
                               $statusValidations['is_accepted']['passed'] &&
                               $statusValidations['not_fully_paid']['passed'] &&
                               $statusValidations['no_active_transactions']['passed'];

        // Get all transactions details
        $transactions = $booking->transactions()->get()->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'status' => $transaction->status,
                'amount' => $transaction->amount,
                'payment_method' => $transaction->payment_method,
                'created_at' => $transaction->created_at->format('c'),
                'processed_at' => $transaction->processed_at ? $transaction->processed_at->format('c') : null,
                'gateway_transaction_id' => $transaction->gateway_transaction_id,
                'metadata' => $transaction->metadata,
            ];
        });

        return response()->json([
            'booking_id' => $booking->id,
            'booking_price' => $booking->price,
            'booking_status' => $booking->status,
            'payment_status' => $booking->payment_status,
            'vnpay_txn_ref' => $booking->vnpay_txn_ref,
            'payment_method' => $booking->payment_method,
            'current_user' => [
                'id' => $user->id,
                'role' => $user->role,
            ],
            'validation_results' => $validationResults,
            'can_make_payment' => $allValidationsPassed,
            'transactions' => $transactions,
            'transactions_count' => $transactions->count(),
            'debug_timestamp' => now()->toISOString(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
})->name('debug.payment.validation');

// NEW: Reset booking payment status (admin only)
Route::middleware(['auth'])->post('/reset-booking-payment/{booking}', function (\App\Models\Booking $booking, \Illuminate\Http\Request $request) {
    try {
        $user = \Illuminate\Support\Facades\Auth::user();

        // Only admin or booking owner can reset
        if ($user->role !== 'admin' && $booking->student_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Load relationships
        $booking->load('transactions');

        $resetActions = [];

        // 1. Mark pending transactions as failed
        $pendingTransactions = $booking->transactions()
            ->where('type', 'payment')
            ->where('status', 'pending')
            ->get();

        foreach ($pendingTransactions as $transaction) {
            $metadata = $transaction->metadata ?? [];
            $metadata['failure_reason'] = 'manual_reset';
            $metadata['reset_by'] = $user->id;
            $metadata['reset_at'] = now()->toISOString();

            $transaction->update([
                'status' => 'failed',
                'metadata' => $metadata,
            ]);

            $resetActions[] = "Transaction #{$transaction->id} marked as failed";
        }

        // 2. Reset booking payment status if needed
        $oldPaymentStatus = $booking->payment_status;
        $oldVnpayTxnRef = $booking->vnpay_txn_ref;
        $oldPaymentMethod = $booking->payment_method;

        // Only reset if not actually completed
        if (!$booking->transactions()->where('type', 'payment')->where('status', 'completed')->exists()) {
            $booking->update([
                'payment_status' => 'pending',
                'vnpay_txn_ref' => null,
                'payment_method' => null,
                'payment_intent_id' => null,
                'payment_at' => null,
            ]);

            $resetActions[] = "Booking payment status reset from '{$oldPaymentStatus}' to 'pending'";
            $resetActions[] = "VNPay TxnRef cleared: '{$oldVnpayTxnRef}'";
            $resetActions[] = "Payment method cleared: '{$oldPaymentMethod}'";
        }

        // 3. Log the reset action
        \Illuminate\Support\Facades\Log::info('Booking payment reset', [
            'booking_id' => $booking->id,
            'reset_by' => $user->id,
            'old_payment_status' => $oldPaymentStatus,
            'old_vnpay_txn_ref' => $oldVnpayTxnRef,
            'actions' => $resetActions,
        ]);

        return response()->json([
            'success' => true,
            'booking_id' => $booking->id,
            'actions_taken' => $resetActions,
            'new_status' => [
                'booking_status' => $booking->fresh()->status,
                'payment_status' => $booking->fresh()->payment_status,
                'vnpay_txn_ref' => $booking->fresh()->vnpay_txn_ref,
            ],
            'message' => 'Booking payment reset successfully. You can now try payment again.',
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
})->name('reset.booking.payment');

// Rate limited routes
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/tutors/{tutor}', [TutorController::class, 'show'])->name('tutors.show');
    Route::post('/tutors/{tutor}/favorite', [TutorController::class, 'toggleFavorite'])->name('tutors.favorite');
    Route::get('/tutors/{tutor}/availability/{day}', [TutorController::class, 'checkAvailability'])->name('tutors.availability');
});

// VNPay Result page (can accept query parameters from redirects)
Route::middleware(['auth'])->get('/vnpay-result', [PaymentController::class, 'showVnpayResult'])->name('vnpay.result');

require __DIR__ . '/auth.php';

// Debug route to test avatar upload
Route::get('/debug-avatar', function () {
    $user = \Illuminate\Support\Facades\Auth::user();
    return response()->json([
        'user_id' => $user->id,
        'avatar' => $user->avatar,
        'avatar_path' => $user->avatar ? asset('storage/avatars/' . $user->avatar) : null,
        'file_exists' => $user->avatar ? \Illuminate\Support\Facades\Storage::exists('public/avatars/' . $user->avatar) : false,
        'storage_path' => storage_path('app/public/avatars/'),
        'public_path' => public_path('storage/avatars/'),
        'storage_link_exists' => is_link(public_path('storage')),
        'avatars_dir_exists' => is_dir(storage_path('app/public/avatars')),
        'avatars_files' => \Illuminate\Support\Facades\Storage::files('public/avatars'),
    ]);
})->middleware('auth');

// Debug route to test form submission
Route::post('/debug-upload', function (\Illuminate\Http\Request $request) {
    try {
        $data = [
            'request_method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'csrf_token_present' => $request->has('_token'),
            'has_avatar' => $request->hasFile('avatar'),
            'avatar_info' => null,
            'validation_errors' => null,
            'upload_result' => null,
            'debug_info' => [
                'php_version' => PHP_VERSION,
                'user_id' => \Illuminate\Support\Facades\Auth::user()->id ?? 'not authenticated',
                'timestamp' => now()->toISOString(),
            ]
        ];

        if (!$request->hasFile('avatar')) {
            $data['error'] = 'No file uploaded';
            return response()->json($data);
        }

        $file = $request->file('avatar');
        $data['avatar_info'] = [
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'is_valid' => $file->isValid(),
            'error' => $file->getError(),
            'error_message' => $file->getErrorMessage(),
            'max_file_size' => ini_get('upload_max_filesize'),
            'max_post_size' => ini_get('post_max_size'),
        ];

        // Validate file
        if (!$file->isValid()) {
            $data['upload_result'] = [
                'success' => false,
                'error' => 'File is not valid: ' . $file->getErrorMessage(),
            ];
            return response()->json($data);
        }

        // Check file size (5MB limit)
        if ($file->getSize() > 5242880) {
            $data['upload_result'] = [
                'success' => false,
                'error' => 'File size exceeds 5MB limit',
            ];
            return response()->json($data);
        }

        // Check file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            $data['upload_result'] = [
                'success' => false,
                'error' => 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed',
            ];
            return response()->json($data);
        }

        try {
            // Create avatars directory if it doesn't exist
            if (!\Illuminate\Support\Facades\Storage::exists('public/avatars')) {
                \Illuminate\Support\Facades\Storage::makeDirectory('public/avatars');
            }

            // Test upload
            $filename = time() . '_debug.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/avatars', $filename);

            $data['upload_result'] = [
                'success' => true,
                'path' => $path,
                'filename' => $filename,
                'storage_exists' => \Illuminate\Support\Facades\Storage::exists($path),
                'full_path' => storage_path('app/' . $path),
                'public_url' => asset('storage/avatars/' . $filename),
                'file_size' => \Illuminate\Support\Facades\Storage::size($path),
            ];

            // Update user avatar for testing
            $user = \Illuminate\Support\Facades\Auth::user();
            if ($user) {
                $oldAvatar = $user->avatar;
                $user->avatar = $filename;
                $user->save();

                $data['user_update'] = [
                    'success' => true,
                    'old_avatar' => $oldAvatar,
                    'new_avatar' => $filename,
                ];
            }

        } catch (\Exception $e) {
            $data['upload_result'] = [
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ];
        }

        return response()->json($data);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Fatal error occurred',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
})->middleware('auth');

// Debug page
Route::get('/debug-upload', function () {
    return view('debug-upload');
})->middleware('auth');

// Test tạo education record thủ công
Route::get('/test-create-education', function () {
    $user = \Illuminate\Support\Facades\Auth::user();

    if ($user->role !== 'tutor' || !$user->tutor) {
        return response()->json(['error' => 'User is not a tutor']);
    }

    try {
        $education = $user->tutor->education()->create([
            'degree' => 'Test Degree ' . time(),
            'institution' => 'Test University',
            'year' => '2024',
            'image' => null
        ]);

        return response()->json([
            'success' => true,
            'education' => $education,
            'tutor_id' => $user->tutor->id,
            'total_education_count' => $user->tutor->education()->count()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
})->middleware('auth');

// Debug route for education
Route::get('/debug-education', function () {
    $user = \Illuminate\Support\Facades\Auth::user();
    $educationData = $user->tutor ? $user->tutor->education : collect();

    return response()->json([
        'user_id' => $user->id,
        'is_tutor' => $user->role === 'tutor',
        'has_tutor_record' => !!$user->tutor,
        'tutor_id' => $user->tutor ? $user->tutor->id : null,
        'education_count' => $educationData ? $educationData->count() : 0,
        'education_records' => $educationData,
        'education_storage_path' => storage_path('app/public/education/'),
        'education_public_path' => public_path('storage/education/'),
        'education_dir_exists' => is_dir(storage_path('app/public/education')),
        'education_files' => \Illuminate\Support\Facades\Storage::files('public/education'),
        'storage_link_exists' => is_link(public_path('storage')),
    ]);
})->middleware('auth');

// Clear education records
Route::get('/clear-education', function () {
    $user = \Illuminate\Support\Facades\Auth::user();

    if ($user->role !== 'tutor' || !$user->tutor) {
        return response()->json(['error' => 'User is not a tutor']);
    }

    try {
        $count = $user->tutor->education()->count();
        $user->tutor->education()->delete();

        return response()->json([
            'success' => true,
            'message' => 'All education records cleared',
            'deleted_count' => $count,
            'remaining_count' => $user->tutor->education()->count()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
})->middleware('auth');

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

// Test route to check if POST works
Route::post('/test-post', function (\Illuminate\Http\Request $request) {
    return response()->json([
        'success' => true,
        'message' => 'POST route works',
        'method' => $request->method(),
        'headers' => $request->headers->all(),
        'input' => $request->all(),
        'files' => $request->hasFile('avatar') ? 'Has file' : 'No file',
        'timestamp' => now()->toISOString(),
    ]);
})->middleware('auth');

// Debug route cho profile update
Route::post('/debug-profile-update', function (\Illuminate\Http\Request $request) {
    try {
        $user = \Illuminate\Support\Facades\Auth::user();

        return response()->json([
            'success' => true,
            'user_id' => $user->id,
            'user_role' => $user->role,
            'is_tutor' => $user->role === 'tutor',
            'has_tutor_record' => !!$user->tutor,
            'method' => $request->method(),
            'has_education' => $request->has('education'),
            'education_data' => $request->input('education', []),
            'education_count' => count($request->input('education', [])),
            'all_data' => $request->all(),
            'files' => $request->files->all(),
            'content_type' => $request->header('Content-Type'),
            'timestamp' => now()->toISOString(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
})->middleware('auth');
