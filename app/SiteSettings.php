<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SiteSettings extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'site_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['setting_key', 'setting_value'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function getAllSettings() {
        $return = array();
        $settings = self::all();
        if(count($settings) > 0) {
            foreach($settings as $key => $setting) {
                if($setting->setting_key == 'gameweek_deadline') {
                    $return[$setting->setting_key] = Carbon::parse($setting->setting_value)->format('l, d M Y H:i');
                }
                else {
                    $return[$setting->setting_key] = $setting->setting_value;
                }
            }
        }
        return $return;
    }
}
