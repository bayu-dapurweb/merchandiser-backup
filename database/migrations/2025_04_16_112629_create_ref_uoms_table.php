<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateRefUomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ref_uoms', function (Blueprint $table) {
            $table->increments('id');
            $table->string("name")->nullable();
            $table->string("code")->nullable();
            $table->string("abs_entry")->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        DB::statement("
        INSERT INTO `ref_uoms` (`id`, `name`, `deleted_at`, `created_at`, `updated_at`, `code`, `abs_entry`) VALUES
        (1, 'Manual', NULL, '2024-04-01 16:43:58', '2024-04-01 16:43:58', 'Manual', '-1'),
        (2, 'PCS', NULL, '2024-04-01 16:43:58', '2024-04-01 16:43:58', 'PCS', '1'),
        (3, 'PACK', NULL, '2024-04-01 16:43:58', '2024-04-01 16:43:58', 'PACK', '2'),
        (4, 'KG', NULL, '2024-04-01 16:43:58', '2024-04-01 16:43:58', 'KG', '3'),
        (5, 'SAK', NULL, '2024-04-01 16:43:58', '2024-04-01 16:43:58', 'SAK', '4'),
        (6, 'BAL', NULL, '2024-04-01 16:43:58', '2024-04-01 16:43:58', 'BAL', '5'),
        (7, 'BOTOL', NULL, '2024-04-01 16:43:58', '2024-04-01 16:43:58', 'BTL', '6'),
        (8, 'BOX', NULL, '2024-04-01 16:43:58', '2024-04-01 16:43:58', 'BOX', '7'),
        (9, 'BTG', NULL, '2024-04-01 16:43:59', '2024-04-01 16:43:59', 'BTG', '8'),
        (10, 'DUS', NULL, '2024-04-01 16:43:59', '2024-04-01 16:43:59', 'DUS', '9'),
        (11, 'PAIL', NULL, '2024-04-01 16:43:59', '2024-04-01 16:43:59', 'PAIL', '15'),
        (12, 'ROLL', NULL, '2024-04-01 16:43:59', '2024-04-01 16:43:59', 'ROLL', '16'),
        (13, 'JERIGEN', NULL, '2024-04-01 16:43:59', '2024-04-01 16:43:59', 'JERIGEN', '17'),
        (14, 'KARTON', NULL, '2024-04-01 16:43:59', '2024-04-01 16:43:59', 'KARTON', '18'),
        (15, 'LITER', NULL, '2024-04-01 16:43:59', '2024-04-01 16:43:59', 'L', '19'),
        (16, 'DUS24KLG', NULL, '2024-04-01 16:44:00', '2024-04-01 16:44:00', 'DUS24KLG', '20'),
        (17, 'DUS48KLG', NULL, '2024-04-01 16:44:00', '2024-04-01 16:44:00', 'DUS48KLG', '21'),
        (18, 'DUS8POUCH', NULL, '2024-04-01 16:44:00', '2024-04-01 16:44:00', 'DUS8POUCH', '22'),
        (19, 'SCOOP', NULL, '2024-04-01 16:44:00', '2024-04-01 16:44:00', 'SCOOP', '23');
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ref_uoms');
    }
}
