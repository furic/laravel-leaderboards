<?php

use Furic\Leaderboards\Models\Leaderboard;
use Illuminate\Database\Seeder;

class WorldsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Leaderboard::class, 5)
            ->create()
            ->each(function (Leaderboard $leaderboard) {
                $leaderboard->game_id = rand(1, 3);
            });
    }
}
