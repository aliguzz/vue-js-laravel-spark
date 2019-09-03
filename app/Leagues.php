<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Leagues extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'league';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['league_code', 'name'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
}
