<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFailedEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('failed_emails', function (Blueprint $table) {
            $table->increments('id');
            $table->string('toEmail');
            $table->string('toName');
            $table->string('fromEmail');
            $table->string('fromName');
            $table->string('token');
            $table->string('subject');
            $table->string('messageId');
            $table->string('sub_domain')->nullable();
            $table->enum('status', ['open', 'pending', 'closed', 'spam','draft']);
            $table->text('commentText');
            $table->longText('attachedFiles');
            $table->string('replyEmail');
            $table->string('reply_to');
            $table->integer('attempts')->default(0);
            $table->integer('track')->default(0);
            $table->enum('sent_status', ['pending','undefined_error','done'])->default('pending');
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
        Schema::dropIfExists('failed_emails');
    }
}
