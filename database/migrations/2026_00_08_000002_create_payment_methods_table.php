<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentMethodsTable extends Migration
{
    /**
     * How the association takes money — cash, GCash, a bank transfer.
     *
     * A table rather than an enum: the office adds an account, renames it,
     * switches it off, or uploads its QR code while the system is running. The
     * details are the instructions printed beside the method ("Scan QR or
     * manually type 0945…"), and `qr_path` is the uploaded code on the public
     * disk, when the method has one.
     *
     * `account_id` names the chart account (see `create_accounts_table`,
     * which runs before this migration) that money paid this way lands in —
     * left unmapped, an officer may leave it null and posting falls back to
     * the association's default cash account.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('details')->nullable()->comment('How to pay with this method');
            $table->string('qr_path')->nullable()->comment('Uploaded QR code (public disk)');
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete()
                ->comment('Where money paid this way lands');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('is_active', 'idx_payment_methods_active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_methods');
    }
}
