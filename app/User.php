<?php

namespace App;

use Laravel\Spark\User as SparkUser;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Two\User as SocialiteUser;
use Laravel\Spark\Token;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends SparkUser
{

    use Notifiable;
    use SoftDeletes;
    
    public function __construct(array $attributes = [],
                                SocialiteUser $socialiteUser = null)
    {
        parent::__construct($attributes);
        $socialiteUser === null
            ?: $this->prepareUserBySocialite($socialiteUser);
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'authy_id',
        'country_code',
        'phone',
        'two_factor_reset_code',
        'card_brand',
        'card_last_four',
        'card_country',
        'billing_address',
        'billing_address_line_2',
        'billing_city',
        'billing_zip',
        'billing_country',
        'extra_billing_information',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'trial_ends_at' => 'datetime',
        'uses_two_factor_auth' => 'boolean',
    ];

    public function hashPassword(string $password): User{
        $this->password = Hash::make($password);
        return $this;
    }

    public function revokeToken(): User{
        $this->remember_token = null;
        return $this;
    }

    public function createToken(): User{
        $this->api_token = str_random(60);
        return $this;
    }

    public function getToken(){
        return $this->hasOne(Token::class, 'user_id', 'id');
    }

    private function prepareUserBySocialite($social): void
    {
        $this->name = $social->name;
        $this->email = $social->email;
        $this->hashPassword($social->email . $social->id);
        $this->createToken();
    }
}
