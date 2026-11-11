<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransfersAndAccountingPeriodsTables extends Migration
{
    /**
     * The rest of the Accounting module: money moved between the association's
     * own accounts, and the periods the books are closed for.
     *
     * `account_transfers` records an internal movement — money the association
     * moved from one of its own cash boxes or bank accounts to another. It is
     * neither income nor an expense, so it is a record of its own rather than a
     * bill: what it needs is the two accounts, the amount, the day it happened
     * and the journal entry it posted, so the register can be read back and the
     * posting traced. Both account columns and the journal entry column are
     * real foreign keys — `accounts` and `journal_entries` are created by
     * earlier migrations, so this table can point at them directly rather
     * than carrying loose, unconstrained IDs.
     *
     * `accounting_periods` is a row per month the office has closed — the
     * month-end and year-end lock. A month with no row is open; a month with a
     * closed row may not be posted into until it is reopened, which is what
     * makes a set of financial statements mean something. The row keeps who
     * closed it and when, and who reopened it and when, because a lock that
     * leaves no trace is not a lock.
     *
     * This migration only creates the tables that belong to Transfers and
     * Accounting Periods.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 30)->unique()->comment('TRF-2026-0001');
            $table->date('transfer_date');
            $table->foreignId('from_account_id')->constrained('accounts')->restrictOnDelete()
                ->comment('The account the money left');
            $table->foreignId('to_account_id')->constrained('accounts')->restrictOnDelete()
                ->comment('The account the money arrived in');
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('memo')->comment('What the movement was for');
            $table->string('reference_no', 100)->nullable()->comment('Bank confirmation or document number');
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete()
                ->comment('The entry that moved the money');
            $table->unsignedBigInteger('created_by_admin_id')->nullable();
            $table->timestamps();

            $table->index('transfer_date', 'idx_transfers_date');
        });

        Schema::create('accounting_periods', function (Blueprint $table) {
            $table->id();
            $table->string('period', 7)->unique()->comment('The month the books are closed for, "Y-m"');
            $table->string('status', 20)->default('open')->comment('App\Enums\AccountingPeriodStatus');
            $table->timestamp('closed_at')->nullable();
            $table->unsignedBigInteger('closed_by_admin_id')->nullable();
            $table->timestamp('reopened_at')->nullable();
            $table->unsignedBigInteger('reopened_by_admin_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status', 'idx_accounting_periods_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounting_periods');
        Schema::dropIfExists('account_transfers');
    }
}
