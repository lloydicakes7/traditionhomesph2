<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillItemsTable extends Migration
{
    /**
     * The lines of a bill — what is being charged, for how much.
     *
     * Every bill is made of lines now, whichever type it is: a one-off invoice's
     * charges, an amenity reservation's rate and deposit, and a month's dues.
     * Dues used to carry their own `billing_method`, `rate` and `sqm` columns;
     * they are an item like everything else now — the rate snapshot lives in
     * `unit_price` and the area it was charged against in `quantity` (a flat
     * rate is one unit), so the pricing rule is stated once for all three.
     *
     * A line keeps the name and unit price it was billed at as its own copy,
     * even when it came from a product: editing or removing a product later
     * must never rewrite a bill that is already on the books. `product_id` is
     * kept so the office can see where a line came from, and it is dropped (set
     * null) rather than cascading if the product goes.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bill_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained('bills')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->string('name')->comment('Snapshot of the product name, or a free-text charge');
            $table->text('description')->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('amount', 10, 2)->default(0);
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
        Schema::dropIfExists('bill_items');
    }
}
