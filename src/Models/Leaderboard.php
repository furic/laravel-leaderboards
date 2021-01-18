<?php

namespace Furic\Leaderboards\Models;

use Illuminate\Database\Eloquent\Model;
use Furic\Leaderboards\Models\LeaderboardTimescope;

class Leaderboard extends Model
{

    protected $guarded = [];

    protected $appends = ['currentTimescope'];

    public static function findByGameId($gameId)
    {
        return SELF::where('game_id', $gameId)->firstOrFail();
    }

    public function timescopes()
    {
        return $this->hasMany(LeaderboardTimescope::class);
    }

    public function scoreSumRewards()
    {
        return $this->hasMany(LeaderboardReward::class)->whereNotNull('score_sum')->orderBy('score_sum', 'desc')->get();
    }
    
    public function scoreSumRankRewards()
    {
        return $this->hasMany(LeaderboardReward::class)->whereNotNull('score_sum_rank')->orderBy('score_sum_rank')->get();
    }
    
    public function highscoreRankRewards()
    {
        return $this->hasMany(LeaderboardReward::class)->whereNotNull('highscore_rank')->orderBy('highscore_rank')->get();
    }

    public function getCurrentTimescopeAttribute()
    {
        return LeaderboardTimescope::findByLeaderboard($this);
    }

}