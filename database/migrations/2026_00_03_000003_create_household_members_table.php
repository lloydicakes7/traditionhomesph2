<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHouseholdMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('household_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained('households')->onDelete('cascade');
            $table->foreignId('resident_id')->constrained('residents')->onDelete('cascade');
            $table->string('relationship', 100)->nullable();
            $table->boolean('is_head')->default(false);
            $table->timestamps();

            $table->unique(['household_id', 'resident_id'], 'uq_household_members_resident');
            $table->unique('resident_id', 'uq_household_members_one_household');
            $table->index(['household_id', 'is_head'], 'idx_household_members_head');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('household_members');
    }
}
