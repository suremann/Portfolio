<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subscription;
use Validator;
use Auth;
use DB;

class SubscriptionController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    return response(
        DB::table('popup.subscriptions')
            ->where('subscriber_id',Auth::id())
            ->join('popup.currencies', 'popup.currencies.id', '=', 'popup.subscriptions.currency_id')
            ->select('balance', 'symbol', 'name', 'price_usd_cc', 'price_usd_wci')
            ->get()
    );
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
        'currency_id' => 'required',
        'balance' => 'required',
    ]);
    if($validator->fails())
      return response($validator->failed());

    $sub = Subscription::create([
        'subscriber_id' => Auth::id(),
        'currency_id' => $request->input('currency_id'),
        'balance' => $request->input('balance'),
    ]);
    return response($sub->id);
  }

  /**
   * Display the specified resource.
   *
   * @param  string $symbol
   *   The id of the currency.
   * @return \Illuminate\Http\Response
   */
  public function show($symbol)
  {
    return response(
        DB::table('popup.subscriptions')
            ->where('subscriber_id',Auth::id())
            ->join('popup.currencies', 'popup.currencies.id', '=', 'popup.subscriptions.currency_id')
            ->where('symbol', '=', $symbol)
            ->select('balance', 'symbol', 'name', 'price_usd_cc', 'price_usd_wci')
            ->get()
    );
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   *   The id of the subscription.
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id)
  {
    $sub = Subscription::where('subscriber_id','=',Auth::id())->where('id','=',$id)->first();
    if($request->has('balance'))
      $sub->balance = $request->input('balance');

    return response($sub->save());
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  string $symbol
   * @return \Illuminate\Http\Response
   */
  public function destroy($symbol)
  {
    $id = DB::table('popup.currencies')->select('id')->where('symbol', '=', $symbol)->first()->id;
    $destroy = Subscription::where('subscriber_id','=',Auth::id())->where('currency_id','=',$id)->delete();
    return response($destroy === 1); //True if successfully deleted.
  }
}
