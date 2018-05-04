<?php

namespace App\Http\Middleware\Popup;

use Closure;
use App;
use App\Models\State;
use App\Jobs\Popup\Initialize;


class Popup
{

  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @return mixed
   */
  public function handle($request, Closure $next)
  {
    $state = State::firstOrNew(['key' => 'queue_state']);
    if($state->value == null){
      $state->value = 'initializing';
      $state->save();
      dispatch(new Initialize());
    }
    return $next($request);
  }
}
