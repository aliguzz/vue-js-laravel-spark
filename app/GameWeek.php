<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameWeek extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'player_gameweek';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['player_id', 'week_number', 'clean_sheet', 'number_of_goals', 'number_of_assists', 'penalty_miss', 'penalty_save', 'number_of_yellow_cards', 'number_of_red_cards', 'number_of_goals_conceded', 'match_start', 'played_for_60_mins', 'best_player', 'second_best_player', 'third_best_player', 'hattrick', 'points'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['player_id', 'id'];
}
