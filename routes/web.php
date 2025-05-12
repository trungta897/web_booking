<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TutorProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TutorController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\FavoriteController;

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/find-tutors', [TutorController::class, 'index'])->name('tutors.index');
Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
Route::get('/how-it-works', [PageController::class, 'howItWorks'])->name('how-it-works');
Route::get('/pricing', [PageController::class, 'pricing'])->name('pricing');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);
});

// Protected routes (require authentication)
Route::middleware(['auth'])->group(function () {
    // Dashboard routes
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/tutor/dashboard', function () {
        return view('tutor.dashboard');
    })->middleware('role:tutor')->name('tutor.dashboard');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Tutor profile routes
    Route::middleware('role:tutor')->group(function () {
        Route::get('/tutor/profile', [TutorProfileController::class, 'show'])->name('tutor.profile.show');
        Route::get('/tutor/profile/edit', [TutorProfileController::class, 'edit'])->name('tutor.profile.edit');
        Route::put('/tutor/profile', [TutorProfileController::class, 'update'])->name('tutor.profile.update');
    });

    // Booking routes
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::put('/bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update');
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');

    // Message routes
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{user}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{user}', [MessageController::class, 'store'])->name('messages.store');

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Favorite routes
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/tutors/{tutor}/favorite', [TutorController::class, 'toggleFavorite'])->name('tutors.favorite');

    // Payment routes
    Route::post('/bookings/{booking}/payment-intent', [PaymentController::class, 'createIntent'])->name('payments.create-intent');
    Route::post('/webhook/stripe', [PaymentController::class, 'handleWebhook'])->name('payments.webhook');

    // Tutor availability routes
    Route::get('/tutors/{tutor}/availability/{day}', [TutorController::class, 'checkAvailability'])->name('tutors.availability');
    Route::middleware('role:tutor')->group(function () {
        Route::get('/availability', [TutorController::class, 'availability'])->name('tutor.availability');
        Route::post('/availability', [TutorController::class, 'updateAvailability'])->name('tutor.availability.update');
    });

    // Review routes
    Route::post('/tutors/{tutor}/reviews', [TutorController::class, 'storeReview'])->name('tutors.reviews.store');
    Route::put('/reviews/{review}', [TutorController::class, 'updateReview'])->name('tutors.reviews.update');
    Route::delete('/reviews/{review}', [TutorController::class, 'destroyReview'])->name('tutors.reviews.destroy');
});

// Test route for Tutor and Subject relationships
Route::get('/test-relationships', function () {
    $tutor = \App\Models\Tutor::with('subjects')->first();
    $subject = \App\Models\Subject::with('tutors')->first();

    return response()->json([
        'tutor' => $tutor,
        'subject' => $subject
    ]);
})->name('test.relationships');

// Rate limited routes
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/tutors', [TutorController::class, 'index'])->name('tutors.index');
    Route::get('/tutors/{tutor}', [TutorController::class, 'show'])->name('tutors.show');
    Route::post('/tutors/{tutor}/favorite', [TutorController::class, 'toggleFavorite'])->name('tutors.favorite');
    Route::get('/tutors/{tutor}/availability/{day}', [TutorController::class, 'checkAvailability'])->name('tutors.availability');
});

require __DIR__.'/auth.php';
