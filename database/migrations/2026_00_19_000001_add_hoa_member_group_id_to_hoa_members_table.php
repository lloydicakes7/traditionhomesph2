<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddHoaMemberGroupIdToHoaMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hoa_members', function (Blueprint $table) {
            $table->foreignId('hoa_member_group_id')
                ->nullable()
                ->constrained('hoa_member_groups')
                ->nullOnDelete();
        });

        $staff = DB::table('hoa_member_groups')->where('name', 'Staff')->value('id');
        $board = DB::table('hoa_member_groups')->where('name', 'Board of Directors')->value('id');

        if ($staff) {
            DB::table('hoa_members')
                ->whereRaw('LOWER(position) IN (?, ?, ?, ?, ?)', ['president', 'vice president', 'secretary', 'treasurer', 'auditor'])
                ->update(['hoa_member_group_id' => $staff]);
        }

        if ($board) {
            DB::table('hoa_members')
                ->whereRaw('LOWER(position) LIKE ?', ['%committee%'])
                ->update(['hoa_member_group_id' => $board]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hoa_members', function (Blueprint $table) {
            $table->dropForeign(['hoa_member_group_id']);
            $table->dropColumn('hoa_member_group_id');
        });
    }
}
