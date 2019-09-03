<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Clubs extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'clubs';
    protected $primaryKey = 'id';

    protected $fillable = ['name','club_shirt','address'];

    
}
