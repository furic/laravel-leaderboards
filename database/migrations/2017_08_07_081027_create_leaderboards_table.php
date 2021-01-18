<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateLeaderboardsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leaderboards', function(Blueprint $table) {
            $table->increments('id');

            $table->integer('game_id')->unsigned();
            $table->tinyInteger('timescope')->unsigned()->default('0'); // 0 - all time, 1 - daily, 2 - weekly, 3 - monthly
            $table->boolean('sum_score')->default(false); // sum score on each report, if set to false then only highscore_rank will be available

            $table->timestamps();

            $table->foreign('game_id')->references('id')->on('games')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leaderboards');
    }

}