<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserService extends BaseService
{
    protected UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository(new User());
    }

    /**
     * Update user profile.
     */
    public function updateProfile(User $user, array $data): User
    {
        return $this->executeTransaction(function () use ($user, $data) {
            // Handle avatar upload if present
            if (isset($data['avatar'])) {
                $data['avatar'] = $this->handleAvatarUpload($user, $data['avatar']);
            }

            // Update user
            $user->update($data);

            $this->logActivity('Profile updated', [
                'user_id' => $user->id,
            ]);

            return $user;
        });
    }

    /**
     * Update user password.
     */
    public function updatePassword(User $user, array $data): bool
    {
        return $this->executeTransaction(function () use ($user, $data) {
            $user->update([
                'password' => Hash::make($data['password']),
            ]);

            $this->logActivity('Password updated', [
                'user_id' => $user->id,
            ]);

            return true;
        });
    }

    /**
     * Upload avatar.
     */
    public function uploadAvatar(User $user, UploadedFile $avatar): string
    {
        // Delete old avatar if exists
        if ($user->avatar) {
            Storage::delete('public/avatars/' . $user->avatar);
        }

        // Store new avatar
        $avatarName = time() . '.' . $avatar->getClientOriginalExtension();
        $avatar->storeAs('public/avatars', $avatarName);

        // Update user
        $user->update(['avatar' => $avatarName]);

        $this->logActivity('Avatar uploaded', [
            'user_id' => $user->id,
            'avatar' => $avatarName,
        ]);

        return $avatarName;
    }

    /**
     * Remove avatar.
     */
    public function removeAvatar(User $user): bool
    {
        if ($user->avatar) {
            Storage::delete('public/avatars/' . $user->avatar);
            $user->update(['avatar' => null]);

            $this->logActivity('Avatar removed', [
                'user_id' => $user->id,
            ]);

            return true;
        }

        return false;
    }

    /**
     * Delete user account.
     */
    public function deleteAccount(User $user): bool
    {
        return $this->executeTransaction(function () use ($user) {
            $userId = $user->id;

            // Remove avatar
            if ($user->avatar) {
                Storage::delete('public/avatars/' . $user->avatar);
            }

            // Delete related data
            $user->notifications()->delete();
            $user->messages()->delete();
            $user->bookings()->delete();

            if ($user->tutor) {
                $user->tutor->delete();
            }

            // Delete user
            $result = $user->delete();

            if ($result) {
                $this->logActivity('Account deleted', [
                    'user_id' => $userId,
                ]);
            }

            return $result;
        });
    }

    /**
     * Handle avatar upload.
     */
    protected function handleAvatarUpload(User $user, UploadedFile $avatar): string
    {
        // Delete old avatar if exists
        if ($user->avatar) {
            Storage::delete('public/avatars/' . $user->avatar);
        }

        // Store new avatar
        $avatarName = time() . '.' . $avatar->getClientOriginalExtension();
        $avatar->storeAs('public/avatars', $avatarName);

        return $avatarName;
    }
}
