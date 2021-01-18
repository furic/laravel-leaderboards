<?php

namespace Furic\Leaderboards\Models;

use Illuminate\Database\Eloquent\Model;

class LeaderboardTimescope extends Model
{

    protected $guarded = [];

    public static function findByLeaderboard($leaderboard)
    {
        $leaderboardTimescope = SELF::where('leaderboard_id', $leaderboard->id)
                ->whereDate('start_at', '<=', date('Y-m-d'))
                ->whereDate('end_at', '>=', date('Y-m-d'))
                ->first();
        if (!$leaderboardTimescope) { // Create a new leaderboard timescope entry if not found
            $data['leaderboard_id'] = $leaderboard->id;
            switch ($leaderboard->timescope) {
                case 1: // Daily
                    $data['start_at'] = date('Y-m-d');
                    $data['end_at'] = date('Y-m-d');
                    break;
                case 2: // Weekly
                    $data['start_at'] = date('Y-m-d', strtotime('monday this week'));
                    $data['end_at'] = date('Y-m-d', strtotime('sunday this week'));
                    break;
                case 3: // Monthly
                    $data['start_at'] = date('Y-m-01');
                    $data['end_at'] = date('Y-m-t');
                    break;
                default:
                case 0: // All time
                    $data['start_at'] = date('Y-m-d');
                    $data['end_at'] = date('2099-12-31');
                    break;
            }
            $previousLeaderboardTimescope = SELF::where('start_at', '<', $data['start_at'])->orderBy('start_at', 'desc')->first();
            if ($previousLeaderboardTimescope)
                $data['previous_id'] = $previousLeaderboardTimescope->id;
            $leaderboardTimescope = SELF::create($data);
        }
        return $leaderboardTimescope;
    }

    public function leaderboard()
    {
        return $this->belongsTo(Leaderboard::class);
    }

    public function scores()
    {
        return $this->hasMany(LeaderboardScore::class);
    }
    
    public function highscores()
    {
        return $this->hasMany(LeaderboardScore::class);
    }
    
    public function orderedScoresByScoreSum()
    {
        return $this->hasMany(LeaderboardScore::class)->orderBy('score_sum', 'desc');
    }
    
    public function orderedScoresByHighscore()
    {
        return $this->hasMany(LeaderboardScore::class)->orderBy('highscore', 'desc');
    }
    
    public function getHighscoreRank($highscore)
    {
        return $this->hasMany(LeaderboardScore::class)->filter(function ($score) {
            return $score['highscore'] > $highscore;
        });
    }
    
    public function previous()
    {
        return SELF::find($this->previous_id);
    }

}