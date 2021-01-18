<?php

namespace Furic\Leaderboards\Models;

use Illuminate\Database\Eloquent\Model;

class LeaderboardPlayerReward extends Model
{

    protected $guarded = [];

    public static function find($leaderbarodTimescopeId, $playerId)
    {
        return SELF::where('leaderboard_timescope_id', $leaderbarodTimescopeId)->where('player_id', $playerId)->first();
    }
    
    public function timescope()
    {
        return $this->belongsTo(LeaderboardTimescope::class);
    }
    
}