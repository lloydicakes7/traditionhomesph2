<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClaimingAtToRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * The officer approving a request names the date and time the homeowner may
     * claim the document at the counter; the moment is kept on the request so
     * the portal can show it back. A request that was never approved keeps it
     * null.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->timestamp('claiming_at')->nullable()->after('document_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn('claiming_at');
        });
    }
}
