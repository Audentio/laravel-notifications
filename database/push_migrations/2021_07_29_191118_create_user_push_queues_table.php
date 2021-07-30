<?php

use Audentio\LaravelBase\Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateUserPushQueuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_push_queues', function (Blueprint $table) {
            $table->id();
            $table->string('handler_class');
            $table->remoteId('user_id');
            $table->remoteId('user_push_subscription_id');
            $table->json('data');

            $table->integer('fail_count')->default(0);
            $table->timestamp('send_at')->nullable();
            $table->timestamp('last_fail_at')->nullable();
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
        Schema::dropIfExists('user_push_queues');
    }
}
