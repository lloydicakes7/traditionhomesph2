<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillsTable extends Migration
{
    /**
     * One row = one thing a homeowner owes, whatever raised it.
     *
     * `invoices` and `monthly_dues` used to be separate tables carrying the same
     * billing columns — a reference, the money, a status, a posting — and every
     * page that read one had to remember the other existed. They are one table
     * now: a bill is a bill, and `type` says where it came from — a one-off
     * invoice, a month's recurring dues, or an amenity reservation.
     *
     * Who owes it is the homeowner account (`user_id`), not the unit. The unit
     * an account lives in is reached through `users -> households ->
     * properties`, so the account is the single link every collection surface
     * shares. `user_id` is nullable because the office may book an amenity for a
     * non-resident guest, and `billed_to_name` snapshots who the bill was
     * written for so the record stays readable if the account goes away.
     *
     * A monthly due is the only type that carries a period, and a homeowner is
     * billed only once per type per period — the unique index says so, and the
     * generator checks it rather than relying on the index alone. NULLs repeat
     * freely in MySQL, so invoices and bookings (which carry no period) are
     * unconstrained.
     *
     * `subtotal` is what the bill's own lines come to; `discount` and `penalty`
     * are the adjustments the office applied; `paid_amount` and `balance`
     * follow the payments recorded against it. Raising a bill does not touch
     * the journal — income is recorded when a payment is received.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 30)->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('billed_to_name', 150)->nullable()->comment('Snapshot of who the bill was written for');
            $table->string('type', 30)->comment('App\Enums\BillType');
            $table->string('billing_period', 7)->nullable()->comment('Billing month, Y-m — monthly dues only');
            $table->date('bill_date');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('penalty', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('balance', 10, 2)->default(0);
            $table->string('status', 20)->default('unpaid')
                ->comment('App\Enums\BillStatus');
            $table->text('notes')->nullable();
            $table->foreignId('created_by_admin_id')->nullable()
                ->constrained('admins')->onDelete('set null');
            $table->timestamp('reminder_sent_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'type', 'billing_period'], 'uq_bills_user_type_period');
            $table->index('type', 'idx_bills_type');
            $table->index('status', 'idx_bills_status');
            $table->index('bill_date', 'idx_bills_bill_date');
            $table->index('user_id', 'idx_bills_user');
            $table->index('billing_period', 'idx_bills_billing_period');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bills');
    }
}
