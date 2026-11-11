<?php

use App\Enums\RequestStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * A request is filed by exactly one resident — the account's own, never
     * somebody else's — and is one of the printable documents the association
     * issues (see \App\Enums\RequestType: an RBI Form, a Membership Form, a
     * Business Permit, an HOA Permit, an Endorsement Letter or a
     * Certification). Each type asks for its own fields, so those columns are
     * nullable and a request fills only the ones its type needs. `purpose` is
     * nullable for the same reason.
     *
     * How it was answered lives on the record too: the remarks, the officer who
     * acted and the moment they did, so the submitter reads the outcome where
     * they read the request itself. An approved request is completed once the
     * document has been released, which the record keeps as its own officer and
     * moment (`completed_by_admin_id`, `completed_at`).
     *
     * The invoice the office raised for the request is named by `bill_id`, so
     * the office's list can tell a request that still has to be billed from one
     * that has been, and the portal knows which bill has to be settled before
     * the document is released. It is nullable — a request is filed before any
     * invoice exists, and a request the office never bills keeps it null — and
     * it clears itself if the bill is removed.
     *
     * The document is handed over in person: the office prints it from the admin
     * side once it is approved, so the record keeps only the day the certification
     * stops being valid (`document_expires_at`, three months), never a download
     * count — the portal issues a transaction slip, not the document.
     *
     * `reference` is the number the slip and the office's search use,
     * `REQ-2026-000045`; it is written once when the request is filed.
     *
     * When it was filed is its own `created_at` — a request is submitted once
     * and never re-dated.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 30)->unique()->comment('Slip reference, REQ-2026-000045');
            $table->foreignId('resident_id')->constrained('residents')->cascadeOnDelete();
            $table->string('type', 50);
            $table->string('purpose', 50)->nullable();

            $table->string('resident_since', 100)->nullable();
            $table->string('business_name', 255)->nullable();
            $table->string('nature_of_business', 255)->nullable();
            $table->string('hours_of_operation', 255)->nullable();
            $table->string('purpose_other', 255)->nullable();
            $table->string('permit_type', 255)->nullable();
            $table->text('scope_of_work')->nullable();
            $table->string('location', 200)->nullable();
            $table->string('addressee', 255)->nullable();
            $table->date('letter_date')->nullable();
            $table->json('membership_data')->nullable();

            $table->string('status', 20)->default(RequestStatus::PENDING);
            $table->text('remarks')->nullable();
            $table->foreignId('processed_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('completed_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();

            $table->foreignId('bill_id')->nullable()->constrained('bills')->nullOnDelete();
            $table->timestamp('document_expires_at')->nullable()->comment('When a printed certification stops being valid; three months');

            $table->timestamps();

            $table->index('status', 'idx_requests_status');
            $table->index('type', 'idx_requests_type');
            $table->index('resident_id', 'idx_requests_resident');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('requests');
    }
}
