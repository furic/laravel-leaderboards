<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateLeaderboardPlayerRewardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leaderboard_player_rewards', function(Blueprint $table) {
            $table->increments('id');

            $table->integer('leaderboard_timescope_id')->unsigned();
            $table->integer('player_id')->unsigned();
            $table->integer('score_sum')->unsigned()->nullable();
            $table->integer('score_sum_rank')->unsigned()->nullable();
            $table->integer('highscore_rank')->unsigned()->nullable();
            
            $table->timestamps();
            
            $table->unique(['leaderboard_timescope_id', 'player_id', 'score_sum']);
            $table->unique(['leaderboard_timescope_id', 'player_id', 'score_sum_rank']);
            $table->unique(['leaderboard_timescope_id', 'player_id', 'highscore_rank']);
            $table->foreign('player_id')->references('id')->on('players')->onDelete('cascade');
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
        Schema::dropIfExists('leaderboard_player_rewards');
    }
}
