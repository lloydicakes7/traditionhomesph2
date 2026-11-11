<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountsTable extends Migration
{
    /**
     * The chart of accounts — the association's full double-entry structure.
     *
     * Every journal line posts against one of these, and every statement and
     * report groups them by `type` (asset, income, expense). Kept as its own
     * migration, run before any of its consumers,
     * so that every table needing an `account_id` — `journal_lines`,
     * `payment_methods`, `expense_categories`, `product_categories`,
     * `account_transfers` — can declare that column on itself, as a real
     * foreign key, at creation time, instead of another migration reaching
     * back in to alter it.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->comment('1000, 1010, 1200, 4000 …');
            $table->string('system_key', 40)->nullable()->unique()->comment('Stable key for a seeded system account');
            $table->string('name');
            $table->string('type', 20)->comment('App\Enums\AccountType');
            $table->text('description')->nullable();
            $table->boolean('is_cash')->default(false)->comment('A place money sits — a cash account');
            $table->timestamps();

            $table->index('type', 'idx_accounts_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounts');
    }
}
