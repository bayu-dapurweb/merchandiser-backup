<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogIsellerTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_iseller_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->text('access_token')->nullable();
            $table->string('token_type')->nullable();
            $table->integer('expires_in')->nullable();
            $table->text('refresh_token')->nullable();
            $table->text('resource_url')->nullable();
            $table->text('status')->nullable();
            $table->text('initial_refresh_token')->nullable();
            $table->longtext('meta_res')->nullable();
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
        Schema::dropIfExists('log_iseller_tokens');
    }
}
