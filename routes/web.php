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