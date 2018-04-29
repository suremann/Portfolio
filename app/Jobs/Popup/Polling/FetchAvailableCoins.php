<?php

namespace App\Jobs\Popup\Polling;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\State;

class FetchAvailableCoins implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  private $state;
  /**
   * Create a new job instance.
   *
   * @return void
   */
  public function __construct(State $state)
  {
    if($state->value == null){
      $state->value = 0;
      $state->save();
    }
    $this->state = $state;
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    $this->state->value++;
    $this->state->save();

    $fetch = new FetchAvailableCoins($this->state);
    //Delay for 24 hours.
    dispatch($fetch)->delay(now()->addDay());
  }
}
