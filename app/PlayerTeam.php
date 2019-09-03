<?php

namespace App;

use Laravel\Spark\Spark;
use Laravel\Spark\Team as SparkTeam;
use Illuminate\Database\Eloquent\Model;

class PlayerTeam extends Model
{
    
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'player_to_team';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['player_id', 'team_id', 'player_cost', 'on_bench', 'position_used_for', 'player_club'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

}
