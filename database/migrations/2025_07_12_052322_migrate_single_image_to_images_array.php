<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, migrate existing single image data to images array
        $educationRecords = DB::table('education')->whereNotNull('image')->get();

        foreach ($educationRecords as $record) {
            $existingImages = json_decode($record->images, true) ?? [];

            // Add the single image to the images array if not already there
            if (!in_array($record->image, $existingImages)) {
                $existingImages[] = $record->image;
            }

            // Update the images field
            DB::table('education')
                ->where('id', $record->id)
                ->update(['images' => json_encode($existingImages)]);
        }

        // Then drop the old image column
        Schema::table('education', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back the image column
        Schema::table('education', function (Blueprint $table) {
            $table->string('image')->nullable()->after('year')->comment('Education certificate/diploma image');
        });

        // Migrate first image from images array back to image field
        $educationRecords = DB::table('education')->whereNotNull('images')->get();

        foreach ($educationRecords as $record) {
            $imagesArray = json_decode($record->images, true);
            if (!empty($imagesArray)) {
                // Take the first image as the single image
                DB::table('education')
                    ->where('id', $record->id)
                    ->update(['image' => $imagesArray[0]]);
            }
        }
    }
};
