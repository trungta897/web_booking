<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService extends BaseService
{
    protected UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository(new User());
    }

    /**
     * Update user profile, including tutor-specific data and uploads.
     */
    public function updateProfile(User $user, array $data): User
    {
        return $this->executeTransaction(function () use ($user, $data) {
            // Handle avatar upload if a new one is provided
            if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
                $this->uploadAvatar($user, $data['avatar']);
            }
            // The user's avatar column is updated within uploadAvatar, so we unset it here.
            unset($data['avatar']);

            // Extract and handle tutor-specific data
            if ($user->role === 'tutor' && $user->tutor) {
                $tutorData = collect($data)->only(['hourly_rate', 'experience_years', 'bio', 'subjects'])->toArray();
                if (!empty($tutorData)) {
                    $this->updateTutorProfile($user->tutor, $tutorData);
                }

                if (isset($data['education'])) {
                    $this->updateTutorEducation($user->tutor, $data['education']);
                }

                // Unset tutor data to avoid conflicts with user update
                unset($data['hourly_rate'], $data['experience_years'], $data['bio'], $data['subjects'], $data['education']);
            }

            // Update user's base profile information
            $userData = collect($data)->only(['name', 'email', 'phone_number', 'address'])->toArray();
            if (!empty($userData)) {
                $user->update($userData);
            }

            $this->logActivity('Profile updated', ['user_id' => $user->id]);

            return $user;
        });
    }

    /**
     * Update user password.
     */
    public function updatePassword(User $user, array $data): bool
    {
        // This logic remains unchanged.
        return $this->executeTransaction(function () use ($user, $data) {
            $user->update(['password' => Hash::make($data['password'])]);
            $this->logActivity('Password updated', ['user_id' => $user->id]);
            return true;
        });
    }

    /**
     * Upload a new avatar, replacing the old one.
     */
    public function uploadAvatar(User $user, UploadedFile $avatar): void
    {
        $destinationPath = public_path('uploads/avatars');
        $filename = time() . '_' . uniqid() . '.' . $avatar->getClientOriginalExtension();

        // Ensure the upload directory exists
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        // Delete the old avatar file if it exists
        if ($user->avatar) {
            $oldFile = $destinationPath . '/' . $user->avatar;
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        // Move the new avatar to the public path
        $avatar->move($destinationPath, $filename);

        // Update the user record with the new avatar filename
        $user->update(['avatar' => $filename]);

        $this->logActivity('Avatar uploaded', ['user_id' => $user->id, 'avatar' => $filename]);
    }

    /**
     * Remove the user's avatar.
     */
    public function removeAvatar(User $user): bool
    {
        if ($user->avatar) {
            $file = public_path('uploads/avatars/' . $user->avatar);
            if (file_exists($file)) {
                unlink($file);
            }
            $user->update(['avatar' => null]);
            $this->logActivity('Avatar removed', ['user_id' => $user->id]);
            return true;
        }
        return false;
    }

    /**
     * Delete user account and associated files.
     */
    public function deleteAccount(User $user): bool
    {
        return $this->executeTransaction(function () use ($user) {
            $userId = $user->id;
            $this->removeAvatar($user); // Use the refactored removeAvatar method

            // Delete related data (this logic is simplified for brevity)
            $user->notifications()->delete();
            $user->messages()->delete();
            $user->bookings()->delete();
            $user->tutor?->delete();
            $user->delete();

            $this->logActivity('Account deleted', ['user_id' => $userId]);
            return true;
        });
    }

    /**
     * Update tutor profile information.
     */
    protected function updateTutorProfile($tutor, array $tutorData): void
    {
        // This logic remains largely unchanged.
        $basicData = collect($tutorData)->only(['hourly_rate', 'experience_years', 'bio'])->toArray();
        if (!empty($basicData)) {
            $tutor->update($basicData);
        }

        if (isset($tutorData['subjects']) && is_array($tutorData['subjects'])) {
            $tutor->subjects()->sync(array_map('intval', $tutorData['subjects']));
        }
    }

    /**
     * [NEW LOGIC] Deletes all existing education records and recreates them from the form submission.
     * This is a simpler, more robust approach to prevent sync issues.
     */
    public function updateTutorEducation($tutor, array $educationData): void
    {
        Log::info('Starting new education update logic (delete and recreate)', [
            'tutor_id' => $tutor->id,
            'incoming_data_count' => count($educationData)
        ]);

        // Step 1: Delete all existing education records for this tutor.
        $existingEducations = $tutor->education()->get();
        foreach ($existingEducations as $edu) {
            $this->deleteEducationImage($edu->image); // Delete associated image file
            $edu->delete();
        }

        Log::info('Deleted all old education records.', [
            'tutor_id' => $tutor->id,
            'deleted_count' => $existingEducations->count()
        ]);

        // Step 2: Recreate education records from the submitted data.
        foreach ($educationData as $index => $eduData) {
            // All entries are treated as new.
            $imageName = null;
            if (isset($eduData['image']) && $eduData['image'] instanceof UploadedFile) {
                try {
                    $imageName = $this->handleEducationImageUpload($eduData['image']);
                    Log::info('Successfully uploaded new education image.', ['filename' => $imageName]);
                } catch (\Exception $e) {
                    Log::error('Education image upload failed.', [
                        'tutor_id' => $tutor->id,
                        'error' => $e->getMessage()
                    ]);
                    // Continue without the image if upload fails
                }
            }

            try {
                $newEducation = $tutor->education()->create([
                    'degree' => $eduData['degree'],
                    'institution' => $eduData['institution'],
                    'year' => $eduData['year'] ?? null,
                    'image' => $imageName,
                ]);
                Log::info('Created new education record.', ['id' => $newEducation->id, 'degree' => $newEducation->degree]);
            } catch (\Exception $e) {
                Log::error('Failed to create education record in database.', [
                    'tutor_id' => $tutor->id,
                    'degree' => $eduData['degree'],
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('Education update completed.', [
            'tutor_id' => $tutor->id,
            'final_education_count' => $tutor->education()->count()
        ]);
    }

    /**
     * Deletes an education image file from storage.
     *
     * @param string|null $imageName
     */
    private function deleteEducationImage(?string $imageName): void
    {
        if ($imageName) {
            $filePath = public_path('uploads/education/' . $imageName);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }

    /**
     * Handle the upload of an education certificate image.
     */
    protected function handleEducationImageUpload(UploadedFile $image): string
    {
        $destinationPath = public_path('uploads/education');
        $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $image->move($destinationPath, $filename);
        return $filename;
    }
}
