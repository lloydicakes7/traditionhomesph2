<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductCategoriesTable extends Migration
{
    /**
     * The groups the association's products and services are filed under.
     *
     * A category is a label the office keeps: it groups the items a homeowner
     * can be billed for (Fees, IDs, Clearances) so a long price list stays
     * navigable. It is a table rather than an enum because the office names its
     * own categories, and renames or drops them as it goes.
     *
     * `account_id` names the revenue account (see `create_accounts_table`,
     * which runs before this migration) this category earns — left
     * unmapped, an officer may leave it null and posting falls back to the
     * association's default revenue account.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_categories');
    }
}
