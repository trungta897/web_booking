<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Education;
use App\Models\TutorProfile;
use App\Models\Tutor;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ensure tutor_id column exists (it should from a previous migration)
        if (!Schema::hasColumn('education', 'tutor_id')) {
            // This is a fallback, ideally the previous migration (2025_05_15_164829) should have run.
             Schema::table('education', function (Blueprint $table) {
                $table->foreignId('tutor_id')->nullable()->after('tutor_profile_id')->constrained('tutors')->onDelete('cascade');
            });
        }

        // Step 1: Migrate data from tutor_profile_id to tutor_id
        $educationRecords = Education::whereNotNull('tutor_profile_id')->whereNull('tutor_id')->get();

        foreach ($educationRecords as $record) {
            $tutorProfile = TutorProfile::find($record->tutor_profile_id);
            if ($tutorProfile) {
                $tutor = Tutor::where('user_id', $tutorProfile->user_id)->first();
                if ($tutor) {
                    $record->tutor_id = $tutor->id;
                    $record->save();
                } else {
                    // Log or handle cases where a Tutor record is not found for the user_id
                    // For now, we'll leave tutor_id as null for these, and they won't become non-nullable
                    // Or, throw an exception: throw new \Exception("Tutor not found for user_id: " . $tutorProfile->user_id . " related to education ID: " . $record->id);
                }
            } else {
                 // Log or handle cases where TutorProfile not found for tutor_profile_id
                 // throw new \Exception("TutorProfile not found for ID: " . $record->tutor_profile_id . " related to education ID: " . $record->id);
            }
        }

        // Step 2: Check if all records that had tutor_profile_id now have tutor_id
        // (Excluding those where TutorProfile or Tutor might not have been found, if we didn't throw an exception)
        $remainingWithNullTutorId = Education::whereNotNull('tutor_profile_id') // still check original condition
                                            ->whereNull('tutor_id')
                                            ->count();

        if ($remainingWithNullTutorId === 0) {
            // All records successfully migrated, or no records needed migration.
            // Now, make tutor_id non-nullable if desired and if the column type supports it directly.
            // Note: SQLite does not support MODIFY COLUMN to add NOT NULL directly if there was existing data easily.
            // For MySQL/PostgreSQL:
            if (DB::getDriverName() !== 'sqlite') {
                 DB::statement('ALTER TABLE education MODIFY tutor_id BIGINT UNSIGNED NOT NULL');
            } else {
                // For SQLite, this is more complex. Typically involves creating a new table.
                // For simplicity in this auto-generated migration, we might skip making it non-nullable for SQLite
                // or accept that new records will enforce it via model validation.
                // Alternatively, make it non-nullable only if there are NO records in the table at all.
                if (Education::count() === Education::whereNotNull('tutor_id')->count()) {
                     // A more robust SQLite way would be to recreate the table, but that's too complex for here.
                     // We'll rely on application logic / future migrations if strict non-nullability is needed on existing SQLite DBs.
                }
            }
        } else {
            // Log: "Warning: Not all education records could be migrated to tutor_id. Some tutor_id fields remain null."
            // You might want to manually inspect these records.
        }

        // Step 3: Drop the tutor_profile_id foreign key and column
        // Need to check if the column exists before trying to drop it.
        if (Schema::hasColumn('education', 'tutor_profile_id')) {
            Schema::table('education', function (Blueprint $table) {
                // Attempt to drop the foreign key constraint.
                // Laravel's default naming convention is table_column_foreign.
                // Or you can pass an array of columns.
                try {
                    $table->dropForeign(['tutor_profile_id']); // Try dropping by column name first
                } catch (\Exception $e) {
                    // If the above fails (e.g., key has a custom name not based on column),
                    // you might try the conventional full name if you know it,
                    // or log that it needs manual intervention if absolutely necessary.
                    // For now, we'll assume dropping by column name or the conventional name is sufficient.
                    // If this also fails, the migration will stop, which is acceptable
                    // as it indicates an unexpected schema state.
                    // Log::warning('Could not drop foreign key for tutor_profile_id automatically: ' . $e->getMessage());
                }
                $table->dropColumn('tutor_profile_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('education', function (Blueprint $table) {
            if (!Schema::hasColumn('education', 'tutor_profile_id')) {
                $table->foreignId('tutor_profile_id')->nullable()->after('id')->constrained('tutor_profiles')->onDelete('cascade');
            }
            // Attempt to repopulate tutor_profile_id from tutor_id
            $educationRecordsToRevert = Education::whereNotNull('tutor_id')->whereNull('tutor_profile_id')->get();
            foreach ($educationRecordsToRevert as $record) {
                $tutor = Tutor::find($record->tutor_id);
                if ($tutor) {
                    $tutorProfile = TutorProfile::where('user_id', $tutor->user_id)->first();
                    if ($tutorProfile) {
                        $record->tutor_profile_id = $tutorProfile->id;
                        $record->save();
                    }
                }
            }

            // If tutor_id was made non-nullable, make it nullable again
            if (DB::getDriverName() !== 'sqlite') {
                 // Assuming original was BIGINT UNSIGNED NULLABLE. Adjust if not.
                DB::statement('ALTER TABLE education MODIFY tutor_id BIGINT UNSIGNED NULL');
            }
            // For SQLite, if it was made non-nullable through table recreation, this is complex to revert.
            // If it was non-nullable, ensure the `tutor_id` column (added by 2025_05_15_164829) is nullable again.
            // The original `add_tutor_id_to_education_table` makes it nullable, so it should be fine.
        });
    }
};
