<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSoaEmailsTable extends Migration
{
    /**
     * The statements of account the office has prepared and sent.
     *
     * A row is one homeowner account's statement for one period: it is queued
     * when the office prepares a batch, sent when the mail leaves the system,
     * and marked failed — with the reason — when it could not be delivered, so
     * the office can retry exactly the ones that did not go out.
     *
     * The statement is addressed to the account (`user_id`), not the unit; the
     * unit it is about is reached through `users -> households -> properties`,
     * the same way a bill resolves it. The account is nullable and set null if
     * it goes, because a sent statement is a record of what went out and must
     * outlive the account it named.
     *
     * This migration runs after `amenity_bookings` because a statement's bill
     * totals read the bills table, which the collections tables create first.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('soa_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('email');
            $table->string('period', 7)->nullable()->comment('Statement period, Y-m');
            $table->string('status', 20)->default('pending')
                ->comment('App\Enums\SoaStatus');
            $table->dateTime('sent_at')->nullable();
            $table->text('error')->nullable()->comment('Why the mail could not be delivered');
            $table->foreignId('sent_by_admin_id')->nullable()
                ->constrained('admins')->onDelete('set null');
            $table->timestamps();

            $table->index('status', 'idx_soa_emails_status');
            $table->index('period', 'idx_soa_emails_period');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('soa_emails');
    }
}
