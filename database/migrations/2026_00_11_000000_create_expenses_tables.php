<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpensesTables extends Migration
{
    /**
     * The Expenses module — everything the association spends.
     *
     * An expense is raised through the office's own three-step wizard and made
     * of line items filed under a category (security, utilities, repairs…).
     * The category is office-managed data — a table, not an enum — exactly
     * like the products' categories, because the office names and retires its
     * own as it goes.
     *
     * The money an expense leaves from is chosen when accounting posts it —
     * the same `accounts` table the books are kept in, not a separate cash
     * registry — so the spending and the chart can never disagree.
     *
     * Posting writes the expense's double-entry journal entry into the
     * `journal_entries` and `journal_lines` tables created by the dedicated
     * journal migration, which runs before this one — the financials stay live
     * without a second record to keep in step.
     *
     * `expense_items` snapshot the category's name at billing time, so
     * renaming or retiring a category never rewrites an expense already
     * recorded — the same rule an invoice line follows.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete()
                ->comment('The account spending here is charged to');
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique()->comment('EXP-2026-0001');
            $table->date('expense_date');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->text('description')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->string('status', 30)->default('draft')->comment('App\Enums\ExpenseStatus');
            $table->unsignedBigInteger('created_by_admin_id')->nullable();
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete()
                ->comment('The entry accounting posted');
            $table->timestamps();

            $table->index('status', 'idx_expenses_status');
            $table->index('expense_date', 'idx_expenses_date');
        });

        Schema::create('expense_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained('expenses')->cascadeOnDelete();
            $table->foreignId('expense_category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
            $table->string('category_name')->comment('Snapshot of the category at billing time');
            $table->text('description');
            $table->decimal('amount', 12, 2);
            $table->timestamps();

            $table->index('expense_id', 'idx_expense_items_expense');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expense_items');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('suppliers');
    }
}
