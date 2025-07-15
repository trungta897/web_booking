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
     * Update tutor education records using smart update/create logic.
     * This preserves existing images when no new image is uploaded.
     */
    public function updateTutorEducation($tutor, array $educationData): void
    {
        Log::info('Starting smart education update logic', [
            'tutor_id' => $tutor->id,
            'incoming_data_count' => count($educationData),
            'incoming_data_keys' => array_keys($educationData),
            'incoming_data_structure' => array_map(function ($item) {
                return [
                    'id' => $item['id'] ?? 'no_id',
                    'degree' => $item['degree'] ?? 'no_degree',
                    'has_new_images' => isset($item['new_images']),
                    'new_images_count' => isset($item['new_images']) ? count($item['new_images']) : 0,
                    'new_images_types' => isset($item['new_images']) ? array_map('get_class', $item['new_images']) : [],
                ];
            }, $educationData),
        ]);

        $existingEducations = $tutor->education()->get()->keyBy('id');
        $processedIds = [];

        // Process each education entry from the form
        foreach ($educationData as $index => $eduData) {
            $educationId = $eduData['id'] ?? null;

            Log::info('Processing education entry', [
                'index' => $index,
                'education_id' => $educationId,
                'degree' => $eduData['degree'] ?? 'no_degree',
                'has_new_images' => isset($eduData['new_images']),
                'new_images_count' => isset($eduData['new_images']) ? count($eduData['new_images']) : 0,
            ]);

            if ($educationId && $existingEducations->has($educationId)) {
                // Update existing education record
                $education = $existingEducations->get($educationId);
                $updateData = [
                    'degree' => $eduData['degree'],
                    'institution' => $eduData['institution'],
                    'year' => $eduData['year'] ?? null,
                ];

                // Handle multiple image uploads for existing record
                if (isset($eduData['new_images']) && is_array($eduData['new_images'])) {
                    Log::info('Processing new images for existing education', [
                        'education_id' => $educationId,
                        'new_images_count' => count($eduData['new_images']),
                        'new_images_details' => array_map(function ($img) {
                            return [
                                'type' => get_class($img),
                                'is_uploaded_file' => $img instanceof UploadedFile,
                                'original_name' => $img instanceof UploadedFile ? $img->getClientOriginalName() : 'not_uploaded_file',
                                'size' => $img instanceof UploadedFile ? $img->getSize() : 'not_uploaded_file',
                            ];
                        }, $eduData['new_images']),
                    ]);

                    try {
                        $newImageFilenames = [];
                        foreach ($eduData['new_images'] as $imageFile) {
                            if ($imageFile instanceof UploadedFile) {
                                $filename = $this->handleEducationImageUpload($imageFile);
                                $newImageFilenames[] = $filename;
                            }
                        }

                        if (!empty($newImageFilenames)) {
                            // Get existing images array
                            $existingImages = $education->images ?? [];

                            // Merge with new images
                            $allImages = array_merge($existingImages, $newImageFilenames);
                            $updateData['images'] = $allImages;

                            Log::info('Added new images to existing education record.', [
                                'education_id' => $educationId,
                                'new_images_count' => count($newImageFilenames),
                                'existing_images_count' => count($existingImages),
                                'total_images_count' => count($allImages),
                                'new_filenames' => $newImageFilenames,
                                'all_filenames' => $allImages,
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Multiple image upload failed for existing record.', [
                            'education_id' => $educationId,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                }

                $education->update($updateData);
                $processedIds[] = $educationId;

                Log::info('Updated existing education record.', [
                    'education_id' => $educationId,
                    'degree' => $eduData['degree'],
                    'final_images' => $education->fresh()->images,
                ]);
            } else {
                // Create new education record
                $createData = [
                    'degree' => $eduData['degree'],
                    'institution' => $eduData['institution'],
                    'year' => $eduData['year'] ?? null,
                ];

                Log::info('Creating new education record', [
                    'index' => $index,
                    'degree' => $eduData['degree'],
                    'has_new_images' => isset($eduData['new_images']),
                ]);

                // Handle multiple image uploads for new record
                if (isset($eduData['new_images']) && is_array($eduData['new_images'])) {
                    try {
                        $imageFilenames = [];
                        foreach ($eduData['new_images'] as $imageFile) {
                            if ($imageFile instanceof UploadedFile) {
                                $filename = $this->handleEducationImageUpload($imageFile);
                                $imageFilenames[] = $filename;
                            }
                        }

                        if (!empty($imageFilenames)) {
                            $createData['images'] = $imageFilenames;
                            Log::info('Uploaded multiple images for new education record.', [
                                'images_count' => count($imageFilenames),
                                'filenames' => $imageFilenames,
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Multiple image upload failed for new record.', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                }

                try {
                    $newEducation = $tutor->education()->create($createData);
                    Log::info('Created new education record.', [
                        'id' => $newEducation->id,
                        'degree' => $newEducation->degree,
                        'final_images' => $newEducation->images,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to create education record.', [
                        'tutor_id' => $tutor->id,
                        'degree' => $eduData['degree'],
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }
        }

        // Delete education records that were not included in the form submission
        $toDelete = $existingEducations->whereNotIn('id', $processedIds);
        foreach ($toDelete as $education) {
            // Delete multiple images
            if ($education->images && is_array($education->images)) {
                foreach ($education->images as $imageName) {
                    $this->deleteEducationImage($imageName);
                }
            }

            $education->delete();
            Log::info('Deleted orphaned education record.', ['education_id' => $education->id]);
        }

        Log::info('Education update completed.', [
            'tutor_id' => $tutor->id,
            'final_education_count' => $tutor->education()->count(),
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
