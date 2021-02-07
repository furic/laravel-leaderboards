<?php

namespace Furic\Leaderboards\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Furic\GameEssentials\Models\Player;
use Furic\Leaderboards\Models\Leaderboard;
use Furic\Leaderboards\Models\LeaderboardPlayerReward;
use Furic\Leaderboards\Models\LeaderboardReward;
use Furic\Leaderboards\Models\LeaderboardScore;
use Furic\Leaderboards\Models\LeaderboardTimescope;

class LeaderboardController extends Controller
{

    /**
     * Display the specified leaderboard resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            return response(Leaderboard::findOrFail($id), 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response(['error' => 'Leaderboard not found.'], 400);
        }
    }

    /**
     * Display the specified leaderboard current timescope data array.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showCurrent(Request $request, $id)
    {
        try {
            $leaderboard = Leaderboard::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response(['error' => 'Leaderboard not found.'], 400);
        }

        $result = ['end_at' => $leaderboard->currentTimescope->end_at];
        $result['score_sum_rewards'] = $leaderboard->scoreSumRewards();
        $result['score_sum_rank_rewards'] = $leaderboard->scoreSumRankRewards();
        $result['highscore_rank_rewards'] = $leaderboard->highscoreRankRewards();
        $result['utc'] = date('Y-m-d H:i:s');

        return response($result, 200);
    }

    /**
     * Display the an array of score-sums of the current leaderboard timescope.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showScoreSums(Request $request, $id)
    {
        try {
            $leaderboard = Leaderboard::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response(['error' => 'Leaderboard not found.'], 400);
        }

        $scoresCol = $leaderboard->currentTimescope->orderedScoresByScoreSum;

        $limit = 20;
        if ($request->input('limit'))
            $limit = $request->limit;

        if ($request->filled('player_id')) { // If specific player
            
            $scoreCount = $scoresCol->count();
            $myScoreCol = $scoresCol->where('player_id', $request->player_id);

            if ($myScoreCol->count() > 0) { // If my score is previously uploaded
                
                $myRank = 1;

                for ($i = 0; $i < $scoreCount; $i++) {
                    if ($scoresCol[$i]->player_id == $request->player_id) {
                        $myRank = $i + 1;
                        break;
                    }
                }

                $myScoreCol->first()->rank = $myRank;
                
                $aboveScoresCol = $scoresCol->slice(max(0, $myRank - $limit + 1), min($myRank - 1, $limit));
                $aboveRank = ($aboveScoresCol->count() >= $limit) ? ($myRank - $limit) : 1;
                foreach ($aboveScoresCol as $aboveScore) {
                    $aboveScore->rank = $aboveRank;
                    $aboveRank++;
                }

                $belowScoresCol = $scoresCol->slice($myRank, $limit);
                $belowRank = $myRank + 1;
                foreach ($belowScoresCol as $belowScore) {
                    $belowScore->rank = $belowRank;
                    $belowRank++;
                }
                $scores = $aboveScoresCol->merge($myScoreCol)->merge($belowScoresCol);
                
            } else { // If no my score yet, just show the last 20 rows
                
                $scoresCol = $scoresCol->slice(-$limit, $limit);
                $rank = max(1, $scoreCount - limit - 1);
                foreach ($scoresCol as $score) {
                    $score->rank = $rank;
                    $rank++;
                }

                $scores = $scoresCol->toArray();
                
                $player = Player::find($request->player_id);
                $myScore = ["player_id" => $request->player_id, "name" => $player->name, "score" => "0"];
                
                $scores = array_merge($scores, [$myScore]);
                
            }
            
        } else { // If no specific player, show the first <limit> rows
            
            $scoresCol = $scoresCol->slice(0, $limit);
            // Manually add rank 1 ~ <limit>
            $rank = 1;
            foreach ($scoresCol as $score) {
                $score->rank = $rank;
                $rank++;
            }
            
            $scores = $scoresCol->toArray();
            
        }

        return $scores;
    }
    
    /**
     * Display the an array of highscores of the current leaderboard timescop.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showHighscores(Request $request, $id)
    {
        try {
            $leaderboard = Leaderboard::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response(['error' => 'Leaderboard not found.'], 400);
        }

        $scoresCol = $leaderboard->currentTimescope->orderedScoresByHighscore;
        $scoresCount = $scoresCol->count();

        $limit = 20;
        if ($request->input('limit'))
            $limit = $request->limit;
        
        $topScoresCol = $scoresCol->slice(0, $limit);
        $rank = 1;
        foreach ($topScoresCol as $score) {
            $score->rank = $rank;
            $rank++;
        }
        $scores = $topScoresCol->toArray();
            
        if ($request->filled('player_id')) { // If specific player, add he/she at the end
            
            $myScoreCol = $scoresCol->where('player_id', $request->player_id);

            $myHighscore = 0;
            $myRank = $scoresCount + 1;
            
            if ($myScoreCol->count() > 0) { // If my score is previously uploaded
                
                for ($i = 0; $i < $scoresCount; $i++) {
                    if ($scoresCol[$i]->player_id == $request->player_id) {
                        $myHighscore = $scoresCol[$i]->highscore;
                        $myRank = $i + 1;
                        break;
                    }
                }
                
                if ($myRank <= $limit) { // Ignore attach my highscore row if my rank is within <limit> already
                    return $scores;
                }
            }
            
            $player = Player::find($request->player_id);
            $myHighscoreRow = ["player_id" => $request->player_id, "name" => $player->name, "highscore" => $myHighscore, "rank" => $myRank];
            $scores = array_merge($scores, [$myHighscoreRow]);
            
        } else { // If no specific player, show the first <limit> rows
            
            $scoresCol = $scoresCol->slice(0, $limit);
            // Manually add rank 1 ~ <limit>
            $rank = 1;
            foreach ($scoresCol as $score) {
                $score->rank = $rank;
                $rank++;
            }
            
            $scores = $scoresCol->toArray();
            
        }
        
        return $scores;
    }
    
    /**
     * Display the rewards of highscores of the current leaderboard timescope.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showRewards(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'player_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return response($validator->messages(), 400);
        }

        try {
            $leaderboard = Leaderboard::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response(['error' => 'Leaderboard not found.'], 400);
        }
        $leaderboardTimescope = $leaderboard->currentTimescope;
        
        $result = [];
        
        // Get rewarded score
        $leaderboardPlayerReward = LeaderboardPlayerReward::find($leaderboardTimescope->id, $request->player_id);
        if ($leaderboardPlayerReward) { // Show the score of the obtained score reward (current timescope period)
            $result['rewarded_score_sum'] = $leaderboardPlayerReward->score_sum;
        }

        $lastScoreSumRank = 0; $lastHighscoreRank = 0;
        $lastLeaderboardTimescope = $leaderboardTimescope->previous();
        if ($lastLeaderboardTimescope != null) { // Never has the previous leaderboard timescope? ignore
            // Check if already redeemed
            $playerReward = LeaderboardPlayerReward::find($lastLeaderboardTimescope->id, $request->player_id);
            if (!$playerReward) { // Not yet obtained player reward (last timescope period), do a score check
                $lastScore = LeaderboardScore::find($lastLeaderboardTimescope->id, $request->player_id);
                if ($lastScore) { // Has last my score
                    if ($lastScore->score_sum > 0) {
                        $lastScoreSumRank = LeaderboardScore::where('leaderboard_timescope_id', $lastLeaderboardTimescope->id)->where('score_sum', '>', $lastScore->score_sum)->count() + 1;
                    }
                    if ($lastScore->highscore > 0) {
                        $lastHighscoreRank = LeaderboardScore::where('leaderboard_timescope_id', $lastLeaderboardTimescope->id)->where('highscore', '>', $lastScore->highscore)->count() + 1;
                    }
                }
            }
            if ($playerReward) { // Unset the ranks if already redeemed
                if ($playerReward->score_sum_rank) {
                    $lastScoreSumRank = 0;
                }
                if ($playerReward->highscore_rank) {
                    $lastHighscoreRank = 0;
                }
            }
        }
        
        if ($lastScoreSumRank > 0) {
            $result['last_score_sum_rank'] = $lastScoreSumRank;
        }
        if ($lastHighscoreRank > 0) {
            $result['last_highscore_rank'] = $lastHighscoreRank;
        }
        
        if (empty($result)) {
            $result = ['success' => 1];
        }
        
        return response($result, 200);
    }

    /**
     * Update or add a specified leaderboard score resource in storage for a player .
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateScore(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'player_id' => 'required|numeric',
            'score' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return response($validator->messages(), 400);
        }

        try {
            $leaderboard = Leaderboard::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response(['error' => 'Leaderboard not found.'], 400);
        }
        $leaderboardTimescope = $leaderboard->currentTimescope;

        $score = LeaderboardScore::firstOrNew(['leaderboard_timescope_id' => $leaderboardTimescope->id, 'player_id' => $request->player_id]);
        
        if ($leaderboard->sum_score) { // Only perform score sum if leaderboard sum_score set to true
            $score->score_sum += $score->score_sum;
            if ($request->score > $score->highscore) { // Update the highscore only when the score is higher
                $score->highscore = $request->score;
            }
            $score->save();
        } else if ($request->score > $score->highscore) { // Only perform highscore check when score is higher than highscore
            $score->highscore = $request->score;
            $score->save();
        }

        return response($score, 200);
    }

    /**
     * Update or add a specified leaderboard reward resource in storage for a player.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateReward(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'player_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return response($validator->messages(), 400);
        }

        try {
            $leaderboard = Leaderboard::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response(['error' => 'Leaderboard not found.'], 400);
        }
        $leaderboardTimescope = $leaderboard->currentTimescope;
        
        $score = LeaderboardScore::where('leaderboard_timescope_id', $leaderboardTimescope->id)->where('player_id', $request->player_id)->first();

        // Score-sum reward report to current LeaderboardTimescope

        if ($request->has('score_sum')) {
            if ($score && $score->score_sum > 0) { // If player score is already existed and having score_sum
                $scoreSumRewards = $leaderboard->scoreSumRewards();
                $rewardedScoreSum = 0;
                foreach ($scoreSumRewards as $scoreSumReward) {
                    if ($scoreSumReward->score_sum <= $score->score_sum) {
                        $rewardedScoreSum = $scoreSumReward->score;
                        break;
                    }
                }
                $playerReward = LeaderboardPlayerReward::where('player_id', $request->player_id)->where('leaderboard_timescope_id', $leaderboardTimescope->id)->whereNotNull('score_sum');
                if (!$playerReward) {
                    $playerReward = LeaderboardPlayerReward::make(['leaderboard_timescope_id' => $leaderboardTimescope->id, 'player_id' => $request->player_id]);
                }
                $playerReward->score_sum = $rewardedScoreSum;
                $playerReward->save();
            }
        }

        // Ranking rewards only report to last LeaderboardTimescope

        if ($leaderboardTimescope->previous() != null) {
            $leaderboardTimescope = $leaderboardTimescope->previous();
        }

        $playerReward = LeaderboardPlayerReward::firstOrNew(['player_id' => $request->player_id, 'leaderboard_timescope_id' => $leaderboardTimescope->id]);
        if ($request->has('score_sum_rank')) {
            $playerReward->score_sum_rank = $request->rank;
        }
        if ($request->has('highscore_rank')) {
            $playerReward->highscore_rank = $request->highscore_rank;
        }
        $playerReward->save();

        return response($playerReward, 200);
    }

}
