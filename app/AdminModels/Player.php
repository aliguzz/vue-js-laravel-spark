<?php

namespace App\AdminModels;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    protected $table = 'player';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'colours',
        'injured_available',
        'injured_out',
        'missing',
        'suspended',
        'cost',
        'position',
        'club',
        'points',

    ];

}
