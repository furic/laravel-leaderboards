# laravel-leaderboards

[![Packagist](https://img.shields.io/packagist/v/furic/leaderboards)](https://packagist.org/packages/furic/leaderboards)
[![Packagist](https://img.shields.io/packagist/dt/furic/leaderboards)](https://packagist.org/packages/furic/leaderboards)
[![License](https://img.shields.io/github/license/furic/laravel-leaderboards)](https://packagist.org/packages/furic/leaderboards)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/furic/laravel-leaderboards/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/furic/laravel-leaderboards/?branch=main)
[![Build Status](https://scrutinizer-ci.com/g/furic/laravel-leaderboards/badges/build.png?b=main)](https://scrutinizer-ci.com/g/furic/laravel-leaderboards/build-status/main)

> Leaderboards management for [Laravel 5.*](https://laravel.com/). This package is developed while working for a leaderboard solution in [Sweaty Chair Studio](https://www.sweatychair.com), serving leaderboards to players. This contains RESTful API for reading the leaderboard in daily, weekly, monthly or all-time period, reporting players' scores to server, as well as getting reward for top ranks. 


> There are two type of leaderboard:
- Highscore: The standard leaderboard, each player has ONE highest score within the period.
- Score-sum: The leaderboard that summing up all scores reported by each player, e.g. seasonal event for collecting the most in-game items within the period.

> The web console is in the TODO list. Meanwhile, you will need to setup the leaderboards in the database manually.

## Table of Contents
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
    - [Web Console](#web-console)
    - [Redeem Code Parameters](#redeem-code-parameters)
    - [Redeem Validator API](#redeem-validator-api)
    - [Unity Client Repo](#unity-client-repo)
- [TODO](#todo)
- [License](#license)

## Installation

Install this package via Composer:
```bash
$ composer require furic/leaderboards
```

> If you are using Laravel 5.5 or later, then installation is done. Otherwise follow the next steps.

#### Open `config/app.php` and follow steps below:

Find the `providers` array and add our service provider.

```php
'providers' => [
    // ...
    Furic\Leaderboards\LeaderboardsServiceProvider::class
],
```

## Configuration

To create table for redeem codes in database run:
```bash
$ php artisan migrate
```

## Usage

### Leaderboards Table

```
| Name            | Type      | Not Null |
|-----------------|-----------|----------|
| id              | integer   |     ✓    |
| game_id         | integer   |     ✓    |
| timescope       | tinyint   |     ✓    |
| sum_score       | boolean   |     ✓    |
```

Leaderboard settings, you would need to set up this mannually.
- Game ID: Which game this leaderboard belongs to.
- Timescope: The repeating period of the leaderboard: 0: all time, 1 - daily, 2 - weekly, 3 - monthly.
- Sum Score: Should we sum the scores for this leaderboard, set to true only if this is a "Score-sum Leaderboard".

### Leaderboard Timescopes Table

```
| Name            | Type      | Not Null |
|-----------------|-----------|----------|
| id              | integer   |     ✓    |
| leaderboard_id  | integer   |     ✓    |
| start_at        | date      |     ✓    |
| end_at          | date      |     ✓    |
| previous_id     | integer   |          |
```

Leaderboard Timescopes are the entries for each leaderboard period, for example, a daily leaderboard will have one entry per day. Each entry is created automatically when the first player report score in a new period.
- Leaderboard ID: Which leaderboard this leaderboard timescope belongs to.
- Start At: The date that this leaderboard timescope start.
- End At: The date that this leaderboard timescope end.
- Previous ID: The previous leaderboard timescope ID, used to give the ranking reward of last period.

### Leaderboard Scores Table

```
| Name                     | Type      | Not Null |
|--------------------------|-----------|----------|
| id                       | integer   |     ✓    |
| leaderboard_timescope_id | integer   |     ✓    |
| player_id                | integer   |     ✓    |
| score_sum                | integer   |     ✓    |
| highscore                | integer   |     ✓    |
```

Leaderboard Scores are the score entries of the players within the leaderboard timescope period. Each entry is created automatically when the player report score.
- Leaderboard Timescope ID: Which leaderboard time scope this leaderboard score belongs to.
- Player ID: Which player this leaderboard score belongs to.
- Score Sum: The score sum of this leaderboard score, used for score-sum leaderboard only.
- Highscore: The highscore of this leaderboard score, used for highscore leaderboard only.

### Leaderboard Rewards Table

```
| Name            | Type      | Not Null |
|-----------------|-----------|----------|
| id              | integer   |     ✓    |
| leaderboard_id  | integer   |     ✓    |
| score_sum       | integer   |          |
| score_sum_rank  | integer   |          |
| highscore_rank  | integer   |          |
| type            | tinyint   |     ✓    |
| amount          | integer   |     ✓    |
| item_id         | integer   |          |
```

Leaderboard Rewards are the rewards for players achriving given rank or score sum, optional only if you have rewards for leaderboard and you would need to set up this mannually.
- Leaderboard ID: Which leaderboard this leaderboard reward belongs to.
- Score Sum: The score sum required for this reward, for example, 100 coins for collecting 5 candies in game.
- Score Sum Rank: The score sum rank required for this reward, for example, 100 coins for 5 most collected players.
- Highscore Rank: The highscore rank required for this reward, for example, 100 coins for 5 top score players.
- Type: The reward item type.
- Amount: The reward item amount.
- Item ID: The reward item ID, optional.
Notes: Only one number would be set within Score Sum, Score Sum Rank and Highscore Rank; the rest two should be null. The lower ranks would ignore the higher ranks, for example entries of 5 rank and 10 rank, the 10 rank entry would ignore the first 5 rank and only giving reward for rank 5~10.

### Leaderboard Player Rewards Table

```
| Name                     | Type      | Not Null |
|--------------------------|-----------|----------|
| id                       | integer   |     ✓    |
| leaderboard_timescope_id | integer   |     ✓    |
| player_id                | integer   |     ✓    |
| score_sum                | integer   |     ✓    |
| score_sum_rank           | integer   |     ✓    |
| highscore_rank           | integer   |     ✓    |
```

Leaderboard Player Rewards are the reward entries for each player. Each entry is created automatically when the player obtain a reward.
- Leaderboard Timescope ID: Which leaderboard time scope this leaderboard player reward belongs to.
- Player ID: Which player this leaderboard player reward belongs to.
- Score Sum: The score sum that the player achived, used for score-sum leaderboard only.
- Score Sum Rank: The score sum ranking that the player achived, used for score-sum leaderboard only.
- Highscore: The highscore that the player achived, used for highscore leaderboard only.

### API URLs

GET `<server url>/api/leaderboards`
Returns a JSON array containing all valid leaderboards, for debug purpose only.

GET `<server url>/api/leaderboards/{id}`
Returns a JSON data containing the leaderboards with given ID, for debug purpose only.

GET `<server url>/api/leaderboards/{id}/current`
Returns a JSON data containing the current leaderboard timescope and reward info.

GET `<server url>/api/leaderboards/{id}/score-sums`
Returns a JSON array containing all score-sum entries around the player's score-sum entry.

GET `<server url>/api/leaderboards/{id}/highscores`
Returns a JSON array containing all highscore entries around the player's highscore entry.

GET `<server url>/api/leaderboards/{id}/rewards`
Returns a JSON data containing all rewards the player can be obtained in current (score-sum) and previous (rank) leaderboard timescope period.

POST `<server url>/api/leaderboards/{id}/score`
Reports a score of the player and creates the relevant database entries.

POST `<server url>/api/leaderboards/{id}/reward`
Reports a reward obtain of the player and creates the relevant database entries.

API Document can be found [here](https://documenter.getpostman.com/view/2560814/TVmV6tm8#4b1f48ab-8d8a-4c6c-a19c-3fa7ae1cd371).

### Unity Client Repo
You can simply import this repo in Unity to communicate with your Laravel server with this package:
`<to be added>`

## TODO

- Create the web console to add/edit leaderboards and upload images.
- Add admin login for web console.
- Add tests and factories.

## License

laravel-leaderboards is licensed under a [MIT License](https://github.com/furic/laravel-leaderboards/blob/main/LICENSE).
