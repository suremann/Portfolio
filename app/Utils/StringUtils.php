<?php
/**
 * Created by PhpStorm.
 * User: Sammy
 * Date: 5/4/2018
 * Time: 6:00 PM
 */

namespace App\Utils;

class StringUtils
{
  public static function appendWithDelimiter($base, $add, $delimiter=','){
    if($base == null)
      return $add;
    if($base !== '')
      $base .= $delimiter;
    return $base . $add;
  }
}