<?php

namespace Furic\Leaderboards\Models;

use Illuminate\Database\Eloquent\Model;
use Furic\GameEssentials\Models\Player;

class LeaderboardScore extends Model
{

    protected $guarded = [];
    protected $hidden = ['leaderboard_timescope_id', 'player', 'created_at', 'updated_at'];
    protected $appends = ['name'];

    public static function find($leaderbarodTimescopeId, $playerId)
    {
        return SELF::where('leaderboard_timescope_id', $leaderbarodTimescopeId)->where('player_id', $playerId)->first();
    }
    
    public function timescope()
    {
        return $this->belongsTo(LeaderboardTimescope::class);
    }

    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    public function getNameAttribute()
    {
        if ($this->player != null)
            return $this->player->name;
        return NULL;
        // return $this->player()->firstOrFail()->name;
    }
    
}