<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
  public $timestamps = false;
  /**
   * The connection name for the model.
   *
   * @var string
   */
  protected $connection = 'popup';
  /**
   * The attributes that are NOT mass assignable.
   *
   * @var array
   */
  protected $guarded = [
    'id',
  ];

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'symbol',
    'name',
    'price_usd_cc',
    'price_usd_wci',
  ];

  public function subscriptions()
  {
    return $this->belongsTo('App\Models\Subscription', 'id', 'currency_id');
  }
}
