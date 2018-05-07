<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Currency;
use Validator;
use DB;

class CurrencyController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    return response(Currency::all());
  }
  /**
   * Display the specified resource.
   *
   * @param  string  $symbol
   * @return \Illuminate\Http\Response
   */
  public function show($symbol)
  {
    return response(Currency::where('symbol',$symbol)->first());
  }

  public function match($word)
  {
    $word = '+'.$word.'*';
    $match = Currency::whereRaw("MATCH (Symbol,Name) AGAINST (? IN BOOLEAN MODE)", [$word])
            ->orderByRaw(DB::raw('LENGTH(Name)'))
            ->limit(10)
            ->get();
    return response($match);
  }
}
