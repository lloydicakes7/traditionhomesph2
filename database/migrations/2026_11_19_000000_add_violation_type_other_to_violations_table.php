<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddViolationTypeOtherToViolationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * A violation filed as "Other" carries the description the officer wrote in
     * its place; every other type leaves this column empty.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('violations', function (Blueprint $table) {
            $table->string('violation_type_other', 150)->nullable()->after('violation_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('violations', function (Blueprint $table) {
            $table->dropColumn('violation_type_other');
        });
    }
}
