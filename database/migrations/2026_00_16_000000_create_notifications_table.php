<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * The bell's own store: one row per event per account, written by the
     * database channel of the app's notifications (see
     * App\Notifications\ThhaNotification). Both account types keep their rows in
     * this one table and are told apart by `notifiable_type` — the two account
     * systems stay separate, but a notification is a notification, and the
     * bell, the list and the retention window are the same on both sides.
     *
     * `data` holds only what a line needs: the kind of event, what it says and
     * the page it opens. Its icon and colour are read from the catalogue
     * (App\Enums\NotificationType), so nothing presentational is stored.
     *
     * `read_at` is the read/unread state and is the only column a reader
     * writes. `created_at` is indexed because that is how the table is pruned:
     * the office keeps a month of history, and a command deletes what is older
     * (see App\Console\Commands\PruneNotifications) rather than letting the
     * table grow for the life of the install.
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}
