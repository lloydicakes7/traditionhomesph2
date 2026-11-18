<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateHoaMemberGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * Groups file HOA members under Staff, the Board of Directors or any
     * group the office adds. This table is created before `hoa_members`,
     * which references it. Clearing a group keeps its members and leaves
     * them ungrouped.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hoa_member_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
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
        Schema::dropIfExists('hoa_member_groups');
    }
}
