<?php

namespace Furic\Leaderboards\Models;

use Illuminate\Database\Eloquent\Model;

class LeaderboardReward extends Model
{

    protected $guarded = [];
    protected $hidden = ['leaderboard_timescope_id', 'created_at', 'updated_at'];

    public function leaderboardTimescope()
    {
        return $this->belongsTo(LeaderboardTimeScope::class);
    }
    
}