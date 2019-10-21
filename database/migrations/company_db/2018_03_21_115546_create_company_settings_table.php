<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanySettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('logo')->nullable();
            $table->string('company_name')->nullable();
            $table->string('website')->nullable();
            $table->string('subdomain')->nullable();
            $table->string('country')->nullable();
            $table->string('country_code')->nullable();
            $table->string('flag')->nullable();
            $table->string('phone')->nullable();
            $table->string('timezone')->nullable()->default('');
            $table->string('timezone_offset')->default(0);
            $table->timestamp('last_trash_clear')->default(\DB::raw('CURRENT_TIMESTAMP'));
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
        Schema::dropIfExists('company_settings');
    }
}
