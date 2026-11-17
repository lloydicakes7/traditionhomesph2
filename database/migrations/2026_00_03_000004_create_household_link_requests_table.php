<?php

use App\Enums\HouseholdLinkStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHouseholdLinkRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('household_link_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained('households')->cascadeOnDelete();
            $table->foreignId('resident_id')->constrained('residents')->cascadeOnDelete();
            $table->string('relationship', 100)->nullable();
            $table->string('status', 20)->default(HouseholdLinkStatus::PENDING);
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->unique(['household_id', 'resident_id'], 'uq_household_link_requests_pair');
            $table->index('status', 'idx_household_link_requests_status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('household_link_requests');
    }
}
