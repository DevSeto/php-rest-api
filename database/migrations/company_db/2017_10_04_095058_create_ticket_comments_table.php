<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket_comments', function (Blueprint $table) {
            $table->collation = 'utf8mb4_bin';
            $table->charset = 'utf8mb4';
            $table->increments('id');
            $table->integer('ticket_id')->nullable();
            $table->string('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->longText('body')->nullable();
            $table->Text('forwarding_addresses')->nullable();
            $table->integer('author_id')->nullable();
            $table->enum('is_forwarded', ['0','1'])->default('0');
            $table->string('transmission_id')->nullable();
            $table->string('email_status')->nullable();
            $table->softDeletes();
            $table->timestamp('event_time')->nullable();
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
        Schema::dropIfExists('ticket_comments', function ($table){
            $table->integer('ticket_id')->unsigned();
            $table->foreign('ticket_id')
                ->references('id')
                ->on('tickets')
                ->onDelete('cascade');
        });
    }
}
