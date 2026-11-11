<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAmenityBookingsTable extends Migration
{
    /**
     * A reservation of one amenity for one window of time.
     *
     * A booking may be filed by a homeowner from the portal or entered by the
     * office for a resident or a guest, so `user_id` and `guest_name` are both
     * optional — one of them names who the reservation is for. The account, not
     * the unit, is who it is filed against; the unit is reached through the
     * account (`users -> households -> properties`).
     *
     * `deposit_amount` is the refundable deposit the officer enters when the
     * reservation is taken, and the deposit itself is charged to the homeowner
     * as a line item on the booking's bill — refunding or forfeiting it
     * afterwards is the booking's own lifecycle (`deposit_status`,
     * `deposit_settled_at`), not a billing concern.
     *
     * The booking no longer carries billing columns of its own (`amount`,
     * `invoice_number`, `invoice_id`, `paid_at`). It names the bill it was
     * settled through once one is raised; what it owes and what has been paid is
     * read from that bill (`status`, `paid_amount`), never duplicated here. The
     * reference is nullable because a booking is created before its bill — or
     * may never need one at all, when the facility is free.
     *
     * The office-visible timeline — submission, approval, deposit, completion —
     * is recorded as the timestamps below, next to the status those transitions
     * land in.
     *
     * `reference` is the number the transaction slip and the office's search
     * use, `BKG-2026-000012`; it is written once when the booking is filed.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('amenity_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 30)->unique()
                ->comment('Slip reference, BKG-2026-000012');
            $table->foreignId('amenity_id')->constrained('amenities')->onDelete('restrict');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('created_by_admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->foreignId('bill_id')->nullable()->constrained('bills')->onDelete('set null')
                ->comment('The bill this reservation was settled through, once raised');
            $table->string('guest_name', 150)->nullable()->comment('Set when the reservation is for a non-resident');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->decimal('deposit_amount', 10, 2)->default(0)->comment('Refundable deposit the reservation asks for');
            $table->string('deposit_status', 20)->default('none')->comment('App\Enums\DepositStatus');
            $table->string('status', 20)->default('pending')->comment('App\Enums\BookingStatus');
            $table->text('purpose')->nullable()->comment('What the amenity is being booked for');
            $table->text('remarks')->nullable()->comment('Office notes');
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('deposit_settled_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['starts_at', 'ends_at'], 'idx_amenity_bookings_window');
            $table->index('status', 'idx_amenity_bookings_status');
            $table->index('deposit_status', 'idx_amenity_bookings_deposit_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('amenity_bookings');
    }
}
