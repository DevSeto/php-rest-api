<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name')->default('');
            $table->string('last_name')->default('');
            $table->string('username')->default('');
            $table->string('company_url')->default('');
            $table->string('email')->unique();
            $table->string('password');
            $table->integer('role_id')->default(0);
            $table->boolean('push_notification_status')->default(0);
            $table->string('display_user_role');
            $table->string('title')->default('');
            $table->string('phone')->default('');
            $table->string('country_code')->nullable();
            $table->string('country')->nullable();
            $table->string('flag')->nullable();
            $table->string('alternate_email')->default('');
            $table->string('time_zone')->default('');
            $table->string('avatar')->default('');
            $table->string('avatar_url')->default('');
            $table->string('avatar_full_path')->default('');
            $table->string('step')->nullable();
            $table->rememberToken();
            $table->softDeletes();
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
        Schema::dropIfExists('users');
    }
}
