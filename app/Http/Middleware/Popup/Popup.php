<?php

namespace App\Http\Middleware\Popup;

use Closure;
use App;
use App\Models\State;

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
    $state = State::firstOrNew(['key' => 'queue_ready']);
    if($state->value == null){
      $state->value = 'initializing';
      $state->save();
      //exec(getcwd() . '/bash/laravel/start_queue.sh > null &');
      dispatch((new App\Jobs\Popup\Initialize()));
    }
    return $next($request);
  }
}
