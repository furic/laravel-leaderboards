<?php

namespace Furic\Leaderboards\Models;

use Illuminate\Database\Eloquent\Model;

class LeaderboardReward extends Model
{

    protected $guarded = ['created_at', 'updated_at'];
    protected $hidden = ['leaderboard_timescope_id'];

    public function leaderboardTimescope()
    {
        return $this->belongsTo(LeaderboardTimeScope::class);
    }
    
}