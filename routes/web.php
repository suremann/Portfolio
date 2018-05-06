<?php


use App\Models\Currency;
use App\Models\State;
use App\Utils\StringUtils;

Route::get('/test', function(){
  $dump = null;

  $dump = DB::table('popup.currencies')->select('symbol', 'price_usd_cc', 'price_usd_wci')->where('symbol','BCH')->get()[0];


//  $options = array(
//      'cluster' => 'us2',
//      'encrypted' => true
//  );
//  $pusher = new Pusher\Pusher(
//      '97e0bc3b7c60a3b97196',
//      '77661ddc66d6484d849d',
//      '504291',
//      $options
//  );
//
//  $pusher->trigger('test', 'test', $dump);
  event(new \App\Events\PriceUpdate($dump));


  \App\Utils\DumpUtil::prettyVarDump($dump);
});
/*
|------------------------------------------------------------------7--------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
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