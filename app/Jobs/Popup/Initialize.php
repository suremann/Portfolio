<?php

namespace App\Jobs\Popup;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\State;

class Initialize implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      $fetch = new Polling\FetchAvailableCoins(State::where('key', 'fac_count')->first());
      $ccompare = new Polling\CryptoCompare(State::where('key', 'cc_count')->first());
      $wcindex = new Polling\WorldCoinIndex(State::where('key', 'wci_count')->first());


      dispatch($fetch);
      dispatch($ccompare)->delay(now()->addSeconds(15)); //Delay for 10 seconds.
      dispatch($wcindex)->delay(now()->addSeconds(30)); //Delay for 10 seconds.
      $state = State::where('key','queue_ready')->first();
      $state->value = 'initialized';
      $state->save();
    }
}
