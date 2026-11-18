<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHoaMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * HOA members are display data, not accounts: `name`, `position` and the
     * optional phone/email/note are what the admin cards and the public HOA
     * information section show. `hoa_member_group_id` files the member under
     * Staff, the Board of Directors or any group the office adds. The groups
     * table is created first, so the foreign key is defined here directly.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hoa_members', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('position', 100);
            $table->foreignId('hoa_member_group_id')
                ->nullable()
                ->constrained('hoa_member_groups')
                ->nullOnDelete();
            $table->string('phone', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('note')->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->timestamps();

            $table->index('name', 'idx_hoa_members_name');
            $table->index('position', 'idx_hoa_members_position');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hoa_members');
    }
}
