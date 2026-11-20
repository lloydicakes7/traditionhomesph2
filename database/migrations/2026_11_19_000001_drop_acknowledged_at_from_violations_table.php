<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DropAcknowledgedAtFromViolationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * Acknowledgement was removed from the Violation module, so the column
     * that stamped it is no longer written or read. SQLite is skipped because
     * the schema builder cannot drop a column there without doctrine/dbal 2.
     *
     * @return void
     */
    public function up()
    {
        if ($this->isSqlite()) {
            return;
        }

        Schema::table('violations', function (Blueprint $table) {
            $table->dropColumn('acknowledged_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if ($this->isSqlite()) {
            return;
        }

        Schema::table('violations', function (Blueprint $table) {
            $table->timestamp('acknowledged_at')->nullable()->after('issued_at');
        });
    }

    /**
     * Whether this connection is SQLite.
     */
    private function isSqlite(): bool
    {
        return DB::connection()->getDriverName() === 'sqlite';
    }
}
