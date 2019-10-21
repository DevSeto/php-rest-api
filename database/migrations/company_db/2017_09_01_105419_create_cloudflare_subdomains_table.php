<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCloudflareSubdomainsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cloudflare_subdomains', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('company_url');
            $table->string('cloudflare_subdomain_id');
            $table->longText('subdomain_details');
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
        Schema::dropIfExists('cloudflare_subdomains');
    }
}
