<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentsTable extends Migration
{
    public function up()
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->string('slug', 170)->unique();
            $table->string('group', 100)->nullable();
            $table->string('image_path')->nullable();
            $table->longText('details');
            $table->timestamps();

            $table->index('group');
        });
    }

    public function down()
    {
        Schema::dropIfExists('contents');
    }
}
