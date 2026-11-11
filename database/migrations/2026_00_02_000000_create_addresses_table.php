<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('label', 100)->unique();
            $table->string('street', 255)->nullable();
            $table->string('subdivision', 255);
            $table->string('barangay', 255);
            $table->string('city', 255);
            $table->string('province', 255);
            $table->string('region', 255);
            $table->string('zip_code', 20)->nullable();
            $table->string('country', 100)->default('Philippines');
            $table->boolean('is_primary')->default(false);
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
        Schema::dropIfExists('addresses');
    }
}
