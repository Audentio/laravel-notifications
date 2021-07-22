<?php

use Audentio\LaravelBase\Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateUserNotificationPreferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->remoteId('user_id');
            $table->remoteId('notification_preference_id');
            $table->json('disabled_channels')->nullable();
            $table->timestamps();

            $table->unique('user_id', 'notification_preference_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_notification_preferences');
    }
}
