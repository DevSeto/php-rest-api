<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMailboxAvailableHoursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mailbox_available_hours', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('mailbox_id');
            $table->text('Monday');
            $table->text('Tuesday');
            $table->text('Wednesday');
            $table->text('Thursday');
            $table->text('Friday');
            $table->text('Saturday');
            $table->text('Sunday');
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
        Schema::dropIfExists('mailbox_available_hours');
    }
}
