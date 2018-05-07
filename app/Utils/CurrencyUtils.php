<?php
/**
 * Created by PhpStorm.
 * User: Sammy
 * Date: 5/4/2018
 * Time: 6:17 PM
 */

namespace App\Utils;
use App\Events\PriceUpdate;
use App\Models\Currency;
use DB;

class CurrencyUtils
{
  public static function fixArray($array){
    $fixed = array();
    foreach($array as $coin){
      $symbol = (get_class($coin) === "stdClass") ? $coin->symbol : $coin['symbol'];
      $fixed[$symbol] = $coin;
    }
    return $fixed;
  }

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

  public static function handleBatchCCUpdate($batch, $symbols){
    $select = null;
    foreach($symbols as $symbol){
      $select = StringUtils::appendWithDelimiter($select, 'symbol = "' . $symbol . '"', ' OR ');
    }
    if($select != null){
      $current = CurrencyUtils::fixArray(
          DB::table('popup.currencies')
              ->select('symbol', 'price_usd_cc', 'price_usd_wci')
              ->whereRaw($select)
              ->get()
      );

      $subscribed = CurrencyUtils::fixArray(
          DB::table('popup.subscriptions')
              ->join('popup.currencies', 'popup.subscriptions.currency_id', '=', 'popup.currencies.id')
              ->select('symbol')
              ->get()
      );

      $update = null;
      $update_values = array();

      foreach ($batch as $symbol => $data) {
        $symbol = strtoupper($symbol);
        if(isset($current[$symbol])){ //UPDATE
          $inDB = $current[$symbol];
          $price = $data['USD'];
          if($inDB->price_usd_cc <>  $price) {
            $update = StringUtils::appendWithDelimiter($update, ' WHEN symbol = ? THEN ? ', '');
            $inDB->price_usd_cc = $price;
            array_push($update_values, $symbol, $price);
            CurrencyUtils::broadcastUpdate($inDB, $subscribed);
          }
        }
      }
      if($update != null){
        $update = 'UPDATE popup.currencies SET price_usd_cc = CASE ' . $update . ' ELSE price_usd_cc END;';
        DB::statement($update, $update_values);
      }
    }
  }

  public static function broadcastUpdate($coin, $subscribed=null){
    if($subscribed == null) {
      $subscribed = CurrencyUtils::fixArray(
          DB::table('popup.subscriptions')
              ->join('popup.currencies', 'popup.subscriptions.currency_id', '=', 'popup.currencies.id')
              ->select('symbol')
              ->get()
      );
    }
    if(isset($subscribed[$coin->symbol])){
      event(new PriceUpdate($coin));
    }
  }

  public static function handleBatchCCInsert($batch){
    $current = array_flip(Currency::pluck('symbol')->all());
    $symbols_chunk = '';
    $chunk_length = 299; //Characters
    $chunk_index = 0;

    $insert_values = array();
    $insert = null;

    foreach($batch as $coin){
      $symbol = strtoupper($coin['Symbol']);
      //Create an entry in the DB for the coin if its not in the current array
      if(!isset($current[$symbol])){
        $insert = StringUtils::appendWithDelimiter($insert, '(?, ?)');
        array_push($insert_values, $symbol, $coin['Name']);
      }
      //If adding this symbol to the chunk makes the chunk too big. Save it and create a new one with the new symbol.
      if(strlen($symbols_chunk) + strlen($symbol) >= $chunk_length){
        StateUtils::updateOrCreate('symbol_chunk_' . $chunk_index, $symbols_chunk);
        $symbols_chunk = '';
        $chunk_index++;
      }
      $symbols_chunk = StringUtils::appendWithDelimiter($symbols_chunk, $coin['Symbol']);
    }
    if($insert != null){
      $insert = 'INSERT INTO popup.currencies (symbol, name) VALUES ' . $insert;
      DB::statement($insert, $insert_values);
    }
    StateUtils::updateOrCreate('symbol_chunk_count', $chunk_index);
  }

  public static function handleBatchWCI($batch){
    $current = DB::table('popup.currencies')->select('symbol', 'price_usd_cc', 'price_usd_wci')->get();
    $current = CurrencyUtils::fixArray($current);

    $subscribed = CurrencyUtils::fixArray(DB::table('popup.subscriptions')->join('popup.currencies', 'popup.subscriptions.currency_id', '=', 'popup.currencies.id')->select('symbol')->get());

    $insert_values = array();
    $update_values = array();
    $insert = null;
    $update = null;

    foreach ($batch as $coin) {
      $symbol = strtoupper(substr($coin['Label'], 0, -4));
      if(isset($current[$symbol])){ //UPDATE
        $inDB = $current[$symbol];
        if($inDB->price_usd_cc <>  $coin['Price']){
          $inDB->price_usd_cc = $coin['Price'];
          CurrencyUtils::broadcastUpdate($inDB, $subscribed);
          $update = StringUtils::appendWithDelimiter($update, ' WHEN symbol = ? THEN ? ', '');
          array_push($update_values, $symbol, $coin['Price']);
        }
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