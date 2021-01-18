<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateLeaderboardScoresTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leaderboard_scores', function(Blueprint $table) {
            $table->increments('id');

            $table->integer('leaderboard_timescope_id')->unsigned();
            $table->integer('player_id')->unsigned();
            $table->integer('score_sum')->unsigned()->default('0');
            $table->integer('highscore')->unsigned()->default('0');

            $table->timestamps();

            $table->index(['leaderboard_timescope_id', 'player_id']); // To find a player's score directly
            $table->index(['leaderboard_timescope_id', 'score_sum']); // To show score-sum leaderboard
            $table->index(['leaderboard_timescope_id', 'highscore']); // To show highscore leaderboard
            $table->foreign('leaderboard_timescope_id')->references('id')->on('leaderboard_timescopes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leaderboard_scores');
    }

}