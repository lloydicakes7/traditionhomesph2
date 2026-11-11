<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * The association's billable items — its products and services.
     *
     * Every ad-hoc charge line comes from here: a vehicle sticker, an ID, a
     * garbage fee, a clearance. `price` is the default a line is created with,
     * and a line keeps its own copy of the price it was billed at, so
     * re-pricing an item never rewrites an invoice already issued.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_category_id')->nullable()->constrained('product_categories')->onDelete('set null');
            $table->string('name');
            $table->string('type', 20)->default('product')->comment('App\Enums\ProductType');
            $table->decimal('price', 10, 2)->default(0);
            $table->string('unit', 50)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active', 'idx_products_active');
            $table->index('type', 'idx_products_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
