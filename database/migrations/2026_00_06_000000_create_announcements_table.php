<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnnouncementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * Announcements are blog-style posts published on the public home page:
     * a title, a body, an optional banner image and the date they were posted.
     * `published_at` is fixed when the post is created — editing a post never
     * re-dates it — so the public page can list posts by publish date.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->longText('body');
            $table->string('image_path')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index('title', 'idx_announcements_title');
            $table->index('published_at', 'idx_announcements_published_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('announcements');
    }
}
