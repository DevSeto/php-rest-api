<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->collation = 'utf8mb4_bin';
            $table->charset = 'utf8mb4';
            $table->increments('id');
            $table->integer('owner_id')->default(0);
            $table->integer('mailbox_id')->default(0);
            $table->string('customer_name');
            $table->integer('customer_id');
            $table->string('customer_email');
            $table->string('subject');
            $table->longText('body');
            $table->integer('assign_agent_id')->default(0);
            $table->enum('status', ['open', 'pending', 'closed', 'spam', 'draft'])->default('open');
            $table->string('message_id')->nullable();
            $table->string('ticket_id_hash')->nullable();
            $table->longText('all_email_data')->nullable();
            $table->integer('merged')->default(0);
            $table->integer('is_demo')->default(0);
            $table->string('color')->nullable();
            $table->timestamp('snooze')->nullable();
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
        Schema::dropIfExists('tickets', function ($table){
            $table->integer('mailbox_id')->unsigned();
            $table->foreign('mailbox_id')
                ->references('id')
                ->on('mailboxes')
                ->onDelete('cascade');
        });

        Schema::dropIfExists('tickets', function ($table){
            $table->integer('owner_id')->unsigned();
            $table->foreign('owner_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }
}
