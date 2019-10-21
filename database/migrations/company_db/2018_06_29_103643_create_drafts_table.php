<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDraftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drafts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ticket_id')->nullable();
            $table->integer('owner_id')->nullable();
            $table->integer('mailbox_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->integer('customer_id')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('subject')->nullable();
            $table->longText('body')->nullable();
            $table->longText('reply')->nullable();
            $table->longText('note')->nullable();
            $table->longText('forward')->nullable();
            $table->enum('status', ['open', 'pending', 'closed', 'spam', 'draft'])->nullable();
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
        Schema::dropIfExists('drafts');
    }

}
