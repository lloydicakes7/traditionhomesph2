<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJournalTables extends Migration
{
    /**
     * The general ledger — `journal_entries` and their `journal_lines`.
     *
     * This is the shared double-entry primitive the rest of the accounting
     * system posts into: a recorded payment and an approved expense post an
     * entry, so does a transfer. Raising a bill does not. Kept as its own
     * migration — rather than bundled into whichever module happened to need
     * it first — so it can be created before any of its consumers and altered
     * independently as the accounting module grows.
     *
     * `source_type`/`source_id` are a loose polymorphic pointer back to the
     * record that produced the entry, not a foreign key, since an entry may
     * come from any of several unrelated modules.
     *
     * `classification`/`status`/`reference_no` say what kind of entry it is,
     * whether it is posted, and what paper it came from. All of these describe
     * a journal entry, so they are declared here, on the table itself, rather
     * than bolted on later by whichever migration happened to introduce them.
     *
     * `journal_lines.account_id` points at the chart account (see
     * `create_accounts_table`, which runs before this one) the line posts
     * against; `account_name` stays as the snapshot of that account's name
     * at posting time, so renaming or retiring an account never rewrites an
     * entry already written.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_no', 50)->unique()->comment('JE-2026-0001');
            $table->date('entry_date');
            $table->string('memo')->nullable();
            $table->string('status', 20)->default('posted')->comment('App\Enums\JournalEntryStatus');
            $table->string('reference_no', 100)->nullable()->comment('OR / voucher / document number');

            // Source ID
            $table->unsignedBigInteger('source_id')->nullable()->comment('Record the entry came from');
            $table->string('source_type', 50)->nullable()->comment('e.g. expense, payment');

            // Admin ID
            $table->unsignedBigInteger('posted_by_admin_id')->nullable();

            // Accounting Type
            $table->string('classification', 20)->default('standard')->comment('App\Enums\JournalClassification');

            $table->timestamps();

            $table->index(['source_type', 'source_id'], 'idx_journal_entries_source');
        });

        Schema::create('journal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();
            $table->string('account_name')->comment('Snapshot of the account name at posting time');
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->string('side', 10)->comment('debit or credit');
            $table->decimal('amount', 12, 2);
            $table->string('memo')->nullable();
            $table->timestamps();

            $table->index('journal_entry_id', 'idx_journal_lines_entry');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('journal_lines');
        Schema::dropIfExists('journal_entries');
    }
}
