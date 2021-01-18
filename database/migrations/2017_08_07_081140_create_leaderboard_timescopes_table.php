<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateLeaderboardTimescopesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leaderboard_timescopes', function(Blueprint $table) {
            $table->increments('id');

            $table->integer('leaderboard_id')->unsigned();
            $table->date('start_at');
            $table->date('end_at');
            $table->integer('previous_id')->unsigned()->nullable();

            $table->timestamps();

            $table->index(['start_at', 'end_at']);
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
        Schema::dropIfExists('leaderboard_timescopes');
    }

}