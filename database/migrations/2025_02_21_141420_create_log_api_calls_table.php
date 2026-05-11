<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogApiCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_api_calls', function (Blueprint $table) {
            $table->increments('id');
            $table->string('related_module')->nullable();
            $table->string('related_reff_id')->nullable();
            $table->text('api_url')->nullable();
            $table->longtext('request_body')->nullable();
            $table->longtext('response_body')->nullable();
            $table->string('response_code')->nullable();
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
        Schema::dropIfExists('log_api_calls');
    }
}
