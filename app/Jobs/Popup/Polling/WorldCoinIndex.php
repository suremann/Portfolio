<?php

namespace App\Jobs\Popup\Polling;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\State;

class WorldCoinIndex implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  private $state;
  /**
   * Create a new job instance.
   *
   * @return void
   */
  public function __construct(State $state=null)
  {
    if($state == null)
      $state = State::firstOrNew(['key' => 'wci_count']);
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
    //Poll from WorldCoinIndex
    $wcindex = new WorldCoinIndex($this->state);
    //Delay for 5 minutes.
    dispatch($wcindex)->delay(now()->addMinutes(5));
  }
}
