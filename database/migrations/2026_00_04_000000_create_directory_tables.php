<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDirectoryTables extends Migration
{
    /**
     * Run the migrations.
     *
     * The directory is an internal officer tool: no public/private or
     * active/inactive state on entries, and phone numbers are just an
     * optional free-text label + the number.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('directory_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->timestamps();
        });

        Schema::create('directory_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('directory_group_id')->constrained('directory_groups')->onDelete('cascade');
            $table->string('name', 150);
            $table->string('role', 100)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('directory_phones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('directory_entry_id')->constrained('directory_entries')->onDelete('cascade');
            $table->string('label', 100)->nullable()->comment('Optional free-text label');
            $table->string('number', 50);
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
        Schema::dropIfExists('directory_phones');
        Schema::dropIfExists('directory_entries');
        Schema::dropIfExists('directory_groups');
    }
}
