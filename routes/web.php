<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/test', function(){
  $current = array_flip(\App\Models\Currency::pluck('symbol')->all());

  $client = new \GuzzleHttp\Client();
  $cc_coins = $client->get("https://min-api.cryptocompare.com/data/all/coinlist");
  $cc_coins = json_decode($cc_coins->getBody(), true);

  if($cc_coins['Response'] === 'Success'){
    \App\Models\State::firstOrCreate(
        ['key' => 'cc_base_image_url'],
        ['value' => $cc_coins['BaseImageUrl']]
    );

    \App\Models\State::firstOrCreate(
        ['key' => 'cc_base_link_url'],
        ['value' => $cc_coins['BaseLinkUrl']]
    );

    $symbol_chunk = '';
    $chunk_length = 299; //Characters
    $chunk_index = 0;


    foreach($cc_coins['Data'] as $coin){
      $symbol = strtoupper($coin['Symbol']);
      if(!isset($current[$symbol])){
        \App\Models\Currency::create([
            'symbol' => $symbol,
            'name' => $coin['Name']
        ]);
      }
      //If adding this symbol to the chunk makes the chunk too big. Save it and create a new one with the new symbol.
      if(strlen($symbol_chunk) + strlen($symbol) >= $chunk_length){
        \App\Models\State::create([
            'key' => 'symbols' . $chunk_index,
            'value' => $symbol_chunk
        ]);
        $symbol_chunk = '';
        $chunk_index++;
      }
      if($symbol_chunk !== '')
        $symbol_chunk .= ',';
      $symbol_chunk .= $coin['Symbol'];
    }
  }
  return "Done";
});
//Routes for the Crypto Asset Monitor popup
Route::group(['prefix'=>'popup', 'middleware'=>'popup.basic'], function(){
  //Routes that do not go through additional middleware
  Route::get('/', 'ViewController@popup'); //Home page
  Route::post('/login', 'AccessController@login'); //Login
  Route::post('/subscriber', 'SubscriberController@store'); //Sign up

  Route::get('/info', 'ViewController@info');

  Route::group(['prefix'=>'update'], function(){
    Route::get('/cryptocompare', 'PollController@cryptoCompare');
    Route::get('/refresh', 'PollController@refresh');
    Route::get('/worldcoinindex', 'PollController@worldCoinIndex');
  });

  //Routes that MUST go through popup auth middleware
  Route::group(['middleware'=>'auth.popup'], function(){
    Route::resource('currency', 'CurrencyController');
    Route::resource('settings', 'SettingsController');
    Route::resource('subscriber', 'SubscriberController', ['except' => 'store']);
    Route::resource('subscription', 'SubscriptionController');
    Route::get('match/{word}','CurrencyController@match');
    Route::post('/logout', 'AccessController@logout');
    Route::get('/logout', 'AccessController@logout');
  });
});