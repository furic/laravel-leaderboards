<?php

use Furic\Leaderboards\Models\Leaderboard;
use Furic\Leaderboards\Models\LeaderboardReward;
use Illuminate\Database\Seeder;

class LeaderboardTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Leaderboard::class, 3)
            ->create()
            ->each(function (Leaderboard $leaderboard, $index) {
                $leaderboard->game_id = 1;
                $leaderboard->timescope = $index; // Create all-time, daily and weekly leaderboards

                // Create rewards for leaderboard
                if ($index > 0) {
                    factory(LeaderboardReward::class, 5)
                        ->create()
                        ->each(function (LeaderboardReward $leaderboardReward, $index) {
                            $leaderboardReward->leaderboard_id = $leaderboard->id;
                            $leaderboardReward->highscore_rank = $index + 1;
                            $leaderboardReward->amount = (6 - $index) * 100;
                        });
                }
            });
    }
}