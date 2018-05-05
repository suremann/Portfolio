<?php
/**
 * Created by PhpStorm.
 * User: Sammy
 * Date: 5/4/2018
 * Time: 6:07 PM
 */

namespace App\Utils;


use App\Models\State;

class StateUtils
{
  public static function updateOrCreate($key, $val){
    State::updateOrCreate(
        ['key' => $key],
        ['value' => $val]
    );
  }
}