<?php

use App\Enums\ComplaintStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComplaintsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * A complaint is filed by exactly one of the two parties: a homeowner from
     * the portal (`resident_id`, the same record the portal account is issued
     * for) or an officer recording something reported to the office
     * (`filed_by_admin_id`). Both are nullable because only one is ever set, and
     * `submitter_name` snapshots the name the complaint was filed under so the
     * record still reads correctly after an account is retired.
     *
     * When it was filed is its own `created_at` — a complaint is submitted once
     * and never re-dated. How it was answered lives on the record too: the
     * office's response, who wrote it and when, so a submitter reads the update
     * in the portal rather than a separate messages table.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->nullable()->constrained('residents')->nullOnDelete();
            $table->foreignId('filed_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('submitter_name', 150)->nullable();

            $table->string('category', 50);
            $table->string('subject', 200);
            $table->text('description');
            $table->string('status', 20)->default(ComplaintStatus::PENDING);

            $table->text('response')->nullable();
            $table->foreignId('responded_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('responded_at')->nullable();

            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index('status', 'idx_complaints_status');
            $table->index('category', 'idx_complaints_category');
            $table->index('resident_id', 'idx_complaints_resident');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('complaints');
    }
}
