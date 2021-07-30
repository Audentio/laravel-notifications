<?php

use Audentio\LaravelBase\Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateUserPushSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->remoteId('user_id');
            $table->string('handler_class');
            $table->string('token');
            $table->json('data')->nullable();
            $table->integer('fail_count')->default(0);
            $table->timestamp('last_fail_at')->nullable();
            $table->timestamp('last_success_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'handler_class', 'token']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_push_subscriptions');
    }
}
