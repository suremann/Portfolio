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
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      dispatch((new Polling\FetchAvailableCoins())->chain([
          new Polling\CryptoCompare(),
          new Polling\WorldCoinIndex(),
      ]));
      State::where('key','queue_state')->update(['value' => 'initialized']);
    }
}
