<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
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
      'key',
      'value',
  ];
  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
  protected $hidden = [
      'created_at',
      'updated_at',
  ];
}
