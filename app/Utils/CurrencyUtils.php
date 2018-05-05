<?php
/**
 * Created by PhpStorm.
 * User: Sammy
 * Date: 5/4/2018
 * Time: 6:17 PM
 */

namespace App\Utils;
use App\Models\Currency;
use DB;

class CurrencyUtils
{
  public static function createIfNotInArray($array, $symbol, $name, $fill=array()){
    $fill['name'] = $name;
    $fill['symbol'] = $symbol;
    if(!isset($array[$symbol])){
      return Currency::create($fill);
    }
    return null;
  }

  public static function updateIfInArray($array, $symbol, $fill){
    if(isset($array[$symbol])){
      Currency::where('symbol', $symbol)->update($fill);
    }
  }

  public static function handleBatchCC($batch, $current){
    $update_values = array();
    $update = null;

    foreach ($batch as $symbol => $price) {
      $symbol = strtoupper($symbol);
      if(isset($current[$symbol])){ //UPDATE
        $update = StringUtils::appendWithDelimiter($update, ' WHEN symbol = ? THEN ? ', '');
        array_push($update_values, $symbol, $price);
      }
    }
    if($update != null){
      $update = 'UPDATE popup.currencies SET price_usd_cc = CASE ' . $update . ' ELSE price_usd_cc END;';
      DB::statement($update, $update_values);
    }
  }

  public static function handleBatchWCI($batch, $current){
    $insert_values = array();
    $update_values = array();
    $insert = null;
    $update = null;

    foreach ($batch as $coin) {
      $symbol = strtoupper(substr($coin['Label'], 0, -4));
      if(isset($current[$symbol])){ //UPDATE
        $update = StringUtils::appendWithDelimiter($update, ' WHEN symbol = ? THEN ? ', '');
        array_push($update_values, $symbol, $coin['Price']);
      }else{ //INSERT
        $insert = StringUtils::appendWithDelimiter($insert, '(?, ?, ?)');
        array_push($insert_values, $symbol, $coin['Name'], $coin['Price']);
      }
    }
    if($insert != null){
      $insert = 'INSERT INTO popup.currencies (symbol, name, price_usd_wci) VALUES ' . $insert;
      DB::statement($insert, $insert_values);
    }
    if($update != null){
      $update = 'UPDATE popup.currencies SET price_usd_wci = CASE ' . $update . ' ELSE price_usd_wci END;';
      DB::statement($update, $update_values);
    }
  }
}