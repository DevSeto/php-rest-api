<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDataConditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_conditions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('workflow_id')->nullable();
            $table->integer('operator_id')->nullable();
            $table->integer('condition_id')->nullable();
            $table->integer('condition_value_id')->nullable();
            $table->string('condition_value')->nullable();
            $table->enum('relation', [null, 'and', 'or'])->nullable();
            $table->integer('relative_condition_id')->nullable();
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
        Schema::dropIfExists('data_conditions');
    }
}
