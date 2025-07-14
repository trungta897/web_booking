<?php

namespace App\Repositories;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $user)
    {
        parent::__construct($user);
    }

    /**
     * Find user by email.
     */
    public function findByEmail(string $email): ?User
    {
        return $this->query()->where('email', $email)->first();
    }

    /**
     * Get users by role.
     */
    public function getUsersByRole(string $role): Collection
    {
        return $this->query()->where('role', $role)
            ->latest()
            ->get();
    }

    /**
     * Get active users.
     */
    public function getActiveUsers(): Collection
    {
        return $this->query()->where('account_status', 'active')
            ->latest()
            ->get();
    }

    /**
     * Get students with bookings.
     */
    public function getStudentsWithBookings(): Collection
    {
        return $this->query()->where('role', 'student')
            ->whereHas('bookings')
            ->with(['bookings.tutor', 'bookings.subject'])
            ->get();
    }

    /**
     * Get tutors with profiles.
     */
    public function getTutorsWithProfiles(): Collection
    {
        return $this->query()->where('role', 'tutor')
            ->whereHas('tutor')
            ->with(['tutor.subjects', 'tutor.reviews'])
            ->get();
    }

    /**
     * Search users by name or email.
     */
    public function searchUsers(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()->where(function ($q) use ($query) {
            $q->where('name', 'like', '%' . $query . '%')
                ->orWhere('email', 'like', '%' . $query . '%');
        })
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get user's favorite tutors.
     */
    public function getUserFavoriteTutors(int $userId): Collection
    {
        $user = $this->findById($userId);

        if (!$user) {
            return collect();
        }

        return $user->favoriteTutors()
            ->with(['user', 'subjects', 'reviews'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->get();
    }

    /**
     * Toggle favorite tutor for user.
     */
    public function toggleFavoriteTutor(int $userId, int $tutorId): bool
    {
        $user = $this->findById($userId);

        if (!$user) {
            return false;
        }

        if ($user->favoriteTutors()->where('tutor_id', $tutorId)->exists()) {
            $user->favoriteTutors()->detach($tutorId);

            return false; // Removed from favorites
        } else {
            $user->favoriteTutors()->attach($tutorId);

            return true; // Added to favorites
        }
    }

    /**
     * Get users registered in date range.
     */
    public function getUsersInDateRange(\DateTime $startDate, \DateTime $endDate): Collection
    {
        return $this->query()->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Get user statistics.
     */
    public function getUserStatistics(int $userId): array
    {
        $user = $this->findById($userId);

        if (!$user) {
            return [];
        }

        // Get user-specific statistics
        $stats = [
            'user_id' => $userId,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'account_status' => $user->account_status,
            'created_at' => $user->created_at,
            'email_verified' => $user->email_verified_at !== null,
        ];

        // Add role-specific statistics
        if ($user->role === 'student') {
            $stats['total_bookings'] = $user->bookings()->count();
            $stats['completed_bookings'] = $user->bookings()->where('is_completed', true)->count();
            $stats['favorite_tutors_count'] = $user->favoriteTutors()->count();
        } elseif ($user->role === 'tutor' && $user->tutor) {
            $stats['total_bookings'] = $user->tutor->bookings()->count();
            $stats['completed_bookings'] = $user->tutor->bookings()->where('is_completed', true)->count();
            $stats['average_rating'] = $user->tutor->reviews()->avg('rating') ?? 0;
            $stats['total_reviews'] = $user->tutor->reviews()->count();
        }

        return $stats;
    }

    /**
     * Get general user statistics (for admin use).
     */
    public function getGeneralUserStatistics(): array
    {
        return [
            'total_users' => $this->query()->count(),
            'active_users' => $this->query()->where('account_status', 'active')->count(),
            'students' => $this->query()->where('role', 'student')->count(),
            'tutors' => $this->query()->where('role', 'tutor')->count(),
            'admins' => $this->query()->where('role', 'admin')->count(),
            'verified_users' => $this->query()->whereNotNull('email_verified_at')->count(),
        ];
    }

    /**
     * Update user profile.
     */
    public function updateProfile(int $userId, array $data): bool
    {
        $user = $this->findById($userId);

        if (!$user) {
            return false;
        }

        return $user->update($data);
    }

    /**
     * Deactivate user account.
     */
    public function deactivateUser(int $userId): bool
    {
        $user = $this->findById($userId);

        if (!$user) {
            return false;
        }

        return $user->update(['account_status' => 'inactive']);
    }

    /**
     * Activate user account.
     */
    public function activateUser(int $userId): bool
    {
        $user = $this->findById($userId);

        if (!$user) {
            return false;
        }

        return $user->update(['account_status' => 'active']);
    }
}
