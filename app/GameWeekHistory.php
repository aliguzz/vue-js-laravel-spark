<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameWeekHistory extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'player_gameweek_history';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['player_id', 'team_id', 'club', 'week_number', 'injured', 'bench_boost', 'triple_captain','position_used_for','suspended', 'missing', 'c_v_c', 'on_bench', 'points','created_at','updated_at'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['id'];
}
