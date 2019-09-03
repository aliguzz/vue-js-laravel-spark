<?php

namespace App;

use Laravel\Spark\Spark;
use Laravel\Spark\Team as SparkTeam;
use Illuminate\Database\Eloquent\Model;

class Players extends Model
{
   

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'player';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'position', 'cost', 'points', 'club', 'injured_available', 'injured_out','missing', 'suspended'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    
    /**
     * Get the users's email address.
     *
     * @return string
     */
    public function getEmailAttribute()
    {
        return $this->owner->email;
    }

    /**
     * Get the owner of the team.
     */
    public function owner()
    {
        return $this->belongsTo(Spark::userModel(), 'user_id');
    }

    public function playergameweek()
    {
        return $this->hasMany(TeamSubscription::class, 'player_id')->orderBy('created_at', 'desc');
    }


    /**
     * Get all of the users that belong to the team.
     */
    public function users()
    {
        return $this->belongsToMany(
            Spark::userModel(), 'team_users', 'team_id', 'user_id'
        )->withPivot('role');
    }

   /**
     * Get all of the users that belong to the team.
     */
    public function sparkteam()
    {
        return $this->belongsToMany(
            SparkTeam::class, 'team_users','team_id', 'team_id' 
        );
    }


    /**
     * Get the total number of users and pending invitations.
     *
     * @return int
     */
    public function totalPotentialUsers()
    {
        return $this->users()->count() + $this->invitations()->count();
    }

    /**
     * Get all of the team's invitations.
     */
    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }

    /**
     * Get all of the subscriptions for the team.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function subscriptions()
    {
        return $this->hasMany(TeamSubscription::class, 'team_id')->orderBy('created_at', 'desc');
    }

    /**
     * Get the available billing plans for the given entity.
     *
     * @return \Illuminate\Support\Collection
     */
    public function availablePlans()
    {
        return Spark::teamPlans();
    }

    /**
     * Get the team photo URL attribute.
     *
     * @param  string|null  $value
     * @return string|null
     */
    public function getPhotoUrlAttribute($value)
    {
        return empty($value)
                ? 'https://www.gravatar.com/avatar/'.md5($this->name.'@spark.laravel.com').'.jpg?s=200&d=identicon'
                : url($value);
    }
}
