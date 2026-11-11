<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvanceDepositsTable extends Migration
{
    /**
     * Money a homeowner has paid ahead of a bill.
     *
     * A deposit is credit held for a homeowner account (`user_id`): it is
     * received once (`amount`) and drawn down as bills are settled from it
     * (`balance`), which is what the status follows.
     *
     * The account is the holder, not the unit — the unit an account occupies is
     * reached through `users -> households -> properties`, the same way a bill
     * resolves it. Credit is held against the account, so deleting an account
     * that still holds credit is refused rather than silently dropping it.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advance_deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->decimal('amount', 10, 2)->default(0)->comment('Received');
            $table->decimal('balance', 10, 2)->default(0)->comment('Still available to apply');
            $table->string('status', 20)->default('available')
                ->comment('App\Enums\AdvanceDepositStatus');
            $table->date('deposited_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status', 'idx_advance_deposits_status');
            $table->index('user_id', 'idx_advance_deposits_user');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advance_deposits');
    }
}
