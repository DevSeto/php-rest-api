<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMailboxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mailboxes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('creator_user_id');
            $table->string('name');
            $table->string('email');
            $table->integer('default')->default(0);
            $table->text('signature')->nullable();
            $table->integer('auto_reply')->default(3);
            $table->string('auto_reply_subject')->nullable();
            $table->text('auto_reply_body')->nullable();
            $table->integer('auto_bcc')->default(2);
            $table->string('dns_name')->nullable();
            $table->text('dns_value')->nullable();
            $table->integer('dns_verified')->default(0);
            $table->string('forward_address')->nullable();
            $table->integer('forwarding_verified')->default(0);
            $table->softDeletes();
            $table->timestamp('created_at')->default(\DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(\DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mailboxes');
    }
}
