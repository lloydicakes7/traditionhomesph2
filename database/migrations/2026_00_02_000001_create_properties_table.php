<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('block');
            $table->unsignedInteger('lot');
            $table->foreignId('address_id')->constrained('addresses')->onDelete('cascade');
            $table->decimal('sqm', 10, 2)->nullable();
            $table->string('type', 50)->nullable();
            $table->string('picture')->nullable();
            $table->unique(['block', 'lot'], 'properties_block_lot_unique');
            $table->timestamps();

            $table->index('block', 'idx_properties_block');
            $table->index('type',  'idx_properties_type');
            $table->index('created_at', 'idx_properties_created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('properties');
    }
}
