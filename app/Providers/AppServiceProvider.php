<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\Message;
use App\Models\Subject;
// Models
use App\Models\Tutor;
use App\Models\User;
use App\Repositories\BookingRepository;
use App\Repositories\MessageRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\SubjectRepository;
// Repositories
use App\Repositories\TutorRepository;
use App\Repositories\UserRepository;
use App\Services\AdminService;
use App\Services\BookingService;
use App\Services\MessageService;
use App\Services\NotificationService;
// Services
use App\Services\PaymentService;
use App\Services\SubjectService;
use App\Services\TutorService;
use App\Services\VnpayService;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind Repository Interfaces to Implementations
        $this->app->bind(
            \App\Contracts\Repositories\UserRepositoryInterface::class,
            function ($app) {
                return new UserRepository(new User);
            }
        );

        $this->app->bind(
            \App\Contracts\Repositories\BookingRepositoryInterface::class,
            function ($app) {
                return new BookingRepository(new Booking);
            }
        );

        $this->app->bind(
            \App\Contracts\Repositories\TutorRepositoryInterface::class,
            function ($app) {
                return new TutorRepository(new Tutor);
            }
        );

        $this->app->bind(
            \App\Contracts\Repositories\SubjectRepositoryInterface::class,
            function ($app) {
                return new SubjectRepository(new Subject);
            }
        );

        $this->app->bind(
            \App\Contracts\Repositories\MessageRepositoryInterface::class,
            function ($app) {
                return new MessageRepository(new Message);
            }
        );

        $this->app->bind(
            \App\Contracts\Repositories\NotificationRepositoryInterface::class,
            function ($app) {
                return new NotificationRepository(new DatabaseNotification);
            }
        );

        // Register concrete Repositories for backward compatibility
        $this->app->singleton(UserRepository::class, function ($app) {
            return new UserRepository(new User);
        });

        $this->app->singleton(BookingRepository::class, function ($app) {
            return new BookingRepository(new Booking);
        });

        $this->app->singleton(TutorRepository::class, function ($app) {
            return new TutorRepository(new Tutor);
        });

        $this->app->singleton(SubjectRepository::class, function ($app) {
            return new SubjectRepository(new Subject);
        });

        $this->app->singleton(MessageRepository::class, function ($app) {
            return new MessageRepository(new Message);
        });

        $this->app->singleton(NotificationRepository::class, function ($app) {
            return new NotificationRepository(new DatabaseNotification);
        });

        // Bind Service Interfaces to Implementations
        $this->app->bind(
            \App\Contracts\Services\BookingServiceInterface::class,
            BookingService::class
        );

        $this->app->bind(
            \App\Contracts\Services\TutorServiceInterface::class,
            TutorService::class
        );

        $this->app->bind(
            \App\Contracts\Services\PaymentServiceInterface::class,
            PaymentService::class
        );

        $this->app->bind(
            \App\Contracts\Services\SubjectServiceInterface::class,
            SubjectService::class
        );

        $this->app->bind(
            \App\Contracts\Services\MessageServiceInterface::class,
            MessageService::class
        );

        $this->app->bind(
            \App\Contracts\Services\NotificationServiceInterface::class,
            NotificationService::class
        );

        $this->app->bind(
            \App\Contracts\Services\AdminServiceInterface::class,
            AdminService::class
        );

        // Register concrete Services for backward compatibility
        $this->app->singleton(BookingService::class, function ($app) {
            return new BookingService;
        });

        $this->app->singleton(TutorService::class, function ($app) {
            return new TutorService;
        });

        $this->app->singleton(PaymentService::class, function ($app) {
            return new PaymentService($app->make(VnpayService::class));
        });

        $this->app->singleton(SubjectService::class, function ($app) {
            return new SubjectService;
        });

        $this->app->singleton(MessageService::class, function ($app) {
            return new MessageService;
        });

        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService;
        });

        $this->app->singleton(AdminService::class, function ($app) {
            return new AdminService;
        });

        $this->app->singleton(VnpayService::class, function ($app) {
            return new VnpayService;
        });

        // Register helper aliases for easier access
        $this->app->alias(UserRepository::class, 'user.repository');
        $this->app->alias(BookingRepository::class, 'booking.repository');
        $this->app->alias(TutorRepository::class, 'tutor.repository');
        $this->app->alias(SubjectRepository::class, 'subject.repository');
        $this->app->alias(MessageRepository::class, 'message.repository');
        $this->app->alias(NotificationRepository::class, 'notification.repository');

        $this->app->alias(BookingService::class, 'booking.service');
        $this->app->alias(TutorService::class, 'tutor.service');
        $this->app->alias(PaymentService::class, 'payment.service');
        $this->app->alias(SubjectService::class, 'subject.service');
        $this->app->alias(MessageService::class, 'message.service');
        $this->app->alias(NotificationService::class, 'notification.service');
        $this->app->alias(AdminService::class, 'admin.service');
        $this->app->alias(VnpayService::class, 'vnpay.service');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Note: Laravel's Notifiable trait already provides unreadNotifications for notifications
        // Custom unread() macro only for messages that have is_read column

        // Set default pagination view
        \Illuminate\Pagination\Paginator::defaultView('pagination::tailwind');
        \Illuminate\Pagination\Paginator::defaultSimpleView('pagination::simple-tailwind');

        // Register global view composers if needed
        $this->registerViewComposers();

        // Register custom validation rules if needed
        $this->registerCustomValidationRules();
    }

    /**
     * Register view composers
     */
    protected function registerViewComposers(): void
    {
        // Composer for navigation - inject unread counts
        view()->composer('layouts.navigation', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();
                $notificationService = app(NotificationService::class);
                $messageService = app(MessageService::class);

                $view->with([
                    'unreadNotifications' => $notificationService->getUnreadCount($user),
                    'unreadMessages' => $messageService->getUnreadCount($user->id),
                ]);
            }
        });

        // Composer for admin layout
        view()->composer('layouts.admin', function ($view) {
            if (Auth::check() && Auth::user()->role === 'admin') {
                $adminService = app(AdminService::class);
                $stats = $adminService->getDashboardStats();

                $view->with([
                    'adminStats' => $stats,
                ]);
            }
        });
    }

    /**
     * Register custom validation rules
     */
    protected function registerCustomValidationRules(): void
    {
        // Custom validation rule for checking tutor availability
        \Illuminate\Support\Facades\Validator::extend('tutor_available', function ($attribute, $value, $parameters, $validator) {
            if (count($parameters) < 2) {
                return false;
            }

            $tutorId = $parameters[0];
            $day = $parameters[1];

            $tutorService = app(TutorService::class);
            $tutor = Tutor::find($tutorId);

            if (! $tutor) {
                return false;
            }

            $result = $tutorService->checkTutorAvailability($tutor, $day);

            return $result['available'];
        });

        // Custom validation rule for checking if user can book with tutor
        \Illuminate\Support\Facades\Validator::extend('can_book_tutor', function ($attribute, $value, $parameters, $validator) {
            if (count($parameters) < 2) {
                return false;
            }

            $studentId = $parameters[0];
            $tutorId = $parameters[1];

            $bookingRepository = app(BookingRepository::class);

            return ! $bookingRepository->hasPendingBookingWithTutor($studentId, $tutorId);
        });

        // Add validation messages
        \Illuminate\Support\Facades\Validator::replacer('tutor_available', function ($message, $attribute, $rule, $parameters) {
            return __('booking.errors.tutor_not_available');
        });

        \Illuminate\Support\Facades\Validator::replacer('can_book_tutor', function ($message, $attribute, $rule, $parameters) {
            return __('booking.validation.pending_booking_exists');
        });
    }
}
