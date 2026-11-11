<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Money received — settled against exactly one bill.
     *
     * Every bill is a row in `bills` now, so a payment names one `bill_id`
     * instead of the old three-way choice (`invoice_id` / `monthly_due_id` /
     * `advance_deposit_id`) where two columns were always NULL. The key is
     * required and restricts: money received is a record of its own, so a bill
     * that has been paid cannot be deleted out from under its receipts — the
     * services refuse that deletion too, and the two rules meet in the middle.
     *
     * The account (`user_id`) is who paid, copied off the bill; it is nullable
     * and set null if the account goes, because the receipt is history and must
     * outlive the account it names. `advance_deposit_id` stays as the one
     * optional source marker: it is set when the money was made by drawing on
     * credit rather than handed over, so the trail shows where it came from.
     *
     * `journal_entry_id` names the entry that recorded the money received (see
     * `create_journal_tables`, which runs before this migration).
     *
     * `status` says whether the office has taken the money onto the books yet:
     * a receipt filed from the homeowner portal waits as `pending` and settles
     * nothing until an officer records it (see \App\Enums\PaymentStatus), while
     * a receipt the office takes itself is `recorded` from the start — which is
     * why the column defaults to it.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained('bills')->onDelete('restrict');
            $table->foreignId('advance_deposit_id')->nullable()->constrained('advance_deposits')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('payment_method_id')->nullable()
                ->constrained('payment_methods')->onDelete('set null');
            $table->decimal('amount', 10, 2);
            $table->string('receipt_no', 30)->nullable()->unique()
                ->comment('Official receipt number, OR-000001; recorded payments only');
            $table->string('reference_no', 100)->nullable()->comment('OR / transaction number');
            $table->string('proof_path')->nullable()->comment('Uploaded proof of payment (public disk)');
            $table->dateTime('paid_at');
            $table->text('notes')->nullable();
            $table->foreignId('received_by_admin_id')->nullable()
                ->constrained('admins')->onDelete('set null');
            $table->foreignId('journal_entry_id')->nullable()
                ->constrained('journal_entries')->nullOnDelete()
                ->comment('The entry recording the money received');
            $table->string('status', 20)->default('recorded')
                ->comment('App\\Enums\\PaymentStatus: pending approval, or recorded by the office');
            $table->timestamps();

            $table->index('paid_at', 'idx_payments_paid_at');
            $table->index('bill_id', 'idx_payments_bill');
            $table->index('status', 'idx_payments_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
