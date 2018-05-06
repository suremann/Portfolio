<?php
/**
 * Created by PhpStorm.
 * User: Sammy
 * Date: 5/5/2018
 * Time: 9:22 PM
 */

namespace App\Utils;


class DumpUtil
{

  public static function prettyVarDump($toDump, $die=true){
    echo "<pre>";
    var_dump($toDump);
    echo "</pre>";
    if($die) die;
  }
}