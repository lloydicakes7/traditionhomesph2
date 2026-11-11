<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAmenitiesTables extends Migration
{
    /**
     * Amenities are the association's bookable facilities — clubhouse, pool,
     * courts, function halls. Each carries its own rate and its own weekly
     * opening hours; no two share them. The deposit a reservation asks for
     * belongs to the reservation, not the facility, so it lives on
     * `amenity_bookings`. Every reservation is approved by the office before
     * it can be used, so there is no per-facility approval rule either.
     *
     * The weekly schedule is one row per weekday rather than a blob of
     * columns: `amenity_schedules` holds the opening window of each ISO
     * weekday (1 = Monday … 7 = Sunday, matching Carbon), and a day with no
     * times on file is a day the amenity is closed.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('amenities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('rate_type', 20)->comment('App\Enums\AmenityRateType');
            $table->decimal('rate', 10, 2)->comment('Charged per hour, or flat');
            $table->boolean('is_bookable')->default(true)->comment('Offered in the homeowner portal');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();

            $table->index('name', 'idx_amenities_name');
        });

        Schema::create('amenity_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('amenity_id')->constrained('amenities')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week')->comment('ISO weekday: 1 = Monday … 7 = Sunday');
            $table->time('opens_at')->nullable();
            $table->time('closes_at')->nullable();
            $table->timestamps();

            $table->unique(['amenity_id', 'day_of_week'], 'uniq_amenity_schedule_day');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('amenity_schedules');
        Schema::dropIfExists('amenities');
    }
}
