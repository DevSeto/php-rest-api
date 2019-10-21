<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersRegistrationStepsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_registration_steps', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->string('first_name')->default('');
            $table->string('last_name')->nullable();
            $table->string('company_name')->nullable();
            $table->string('company_url')->nullable();
            $table->string('password')->nullable();
            $table->string('subdomain')->nullable();
            $table->string('demo_mailbox_name')->nullable();
            $table->string('mailbox_email')->nullable();
            $table->string('mailbox_name')->nullable();
            $table->string('mailbox_forwarding')->nullable();
            $table->string('step')->nullable();
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
        Schema::dropIfExists('users_registration_steps');
    }
}
