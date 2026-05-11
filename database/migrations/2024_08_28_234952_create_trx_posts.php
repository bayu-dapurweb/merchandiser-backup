<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrxPosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trx_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->text('thum_image')->nullable();
            $table->text('main_image')->nullable();
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->longtext('body')->nullable();
            $table->longtext('meta')->nullable();
            $table->string('post_type')->nullable()->default('tours');
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
        Schema::drop('trx_posts');
    }
}
