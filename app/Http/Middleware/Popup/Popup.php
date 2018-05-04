<?php

namespace App\Http\Middleware\Popup;

use Closure;
use App;
use App\Models\State;
use App\Jobs\Popup\Initialize;
use App\Utils\MyQueue;
use Illuminate\Support\Facades\Artisan;


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
//      exec(getcwd() . '/bash/laravel/start_queue.sh >> null &');

      //dispatch(new Initialize());
      MyQueue::dispatchJob(new Initialize());
      Artisan::call('queue:work', [
          '--once' => true,
      ]);
    }
    return $next($request);
  }
}
