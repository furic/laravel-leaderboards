<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateLeaderboardRewardsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leaderboard_rewards', function(Blueprint $table) {
            $table->increments('id');

            $table->integer('leaderboard_id')->unsigned();
            $table->integer('score_sum')->unsigned()->nullable();
            $table->integer('score_sum_rank')->unsigned()->nullable();
            $table->integer('highscore_rank')->unsigned()->nullable();
            $table->tinyInteger('type')->unsigned()->default('1');
            $table->integer('amount')->unsigned()->default('1');
            $table->integer('item_id')->unsigned()->nullable();

            $table->timestamps();

            $table->index(['leaderboard_id', 'score_sum']);
            $table->index(['leaderboard_id', 'score_sum_rank']);
            $table->index(['leaderboard_id', 'highscore_rank']);
            $table->foreign('leaderboard_id')->references('id')->on('leaderboards')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leaderboard_rewards');
    }

}