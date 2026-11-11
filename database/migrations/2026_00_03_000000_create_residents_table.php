<?php

use App\Enums\Sex;
use App\Enums\CivilStatus;
use App\Enums\EducationalAttainment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResidentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('residents', function (Blueprint $table) {
            $table->id();
            $table->string('philsys_card_no', 19)->nullable();
            $table->string('surname');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('suffix')->nullable();
            $table->date('birth_date');
            $table->string('birth_place')->nullable();
            $table->enum('sex', Sex::values());
            $table->enum('civil_status', CivilStatus::values());
            $table->string('spouse_name')->nullable();
            $table->string('citizenship');
            $table->string('religion')->nullable();
            $table->string('occupation')->nullable();
            $table->string('mobile_no')->nullable();
            $table->enum('educational_attainment', EducationalAttainment::values())->nullable();
            $table->json('sectors')->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->string('valid_id_path')->nullable();
            $table->timestamps();

            $table->index('surname',    'idx_residents_surname');
            $table->index('first_name', 'idx_residents_first_name');
            $table->index('mobile_no', 'idx_residents_mobile_no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('residents');
    }
}
