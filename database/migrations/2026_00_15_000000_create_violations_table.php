<?php

use App\Enums\ViolationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateViolationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * An officer issues a violation against a resident: what it was for, when
     * and where it happened, and what the office wrote about it. The record it
     * hangs off is the resident — the same record the portal account is issued
     * for, so what the office files and what the homeowner reads are one row.
     *
     * `status` is the record's own state, not its history: a violation stays
     * ACTIVE until the office resolves it, at which point it leaves the member's
     * current list and stays in their history. Nothing is ever deleted to make
     * that happen.
     *
     * `resident_id` is nullable with a nulling delete — the same shape
     * `hoa_members` uses — because the office can remove a resident record, and
     * a citation must not block that; the name snapshotted onto the violation at
     * issue time (`resident_name`) keeps the record readable afterwards, the way
     * a journal line keeps its account's name.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->nullable()->constrained('residents')->nullOnDelete();
            $table->string('resident_name', 150)->nullable();
            $table->string('violation_type', 50);
            $table->date('occurred_on');
            $table->time('occurred_time')->nullable();
            $table->string('location', 255);
            $table->text('description');
            $table->string('status', 20)->default(ViolationStatus::ACTIVE);
            $table->text('office_report')->nullable();

            $table->foreignId('issued_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();

            $table->text('resolution_notes')->nullable();
            $table->foreignId('resolved_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();

            $table->index('status', 'idx_violations_status');
            $table->index('violation_type', 'idx_violations_type');
            $table->index('occurred_on', 'idx_violations_occurred_on');
            $table->index('resident_id', 'idx_violations_resident');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('violations');
    }
}
