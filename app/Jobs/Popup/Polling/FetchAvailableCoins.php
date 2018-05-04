<?php

namespace App\Jobs\Popup\Polling;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\State;
use App\Models\Currency;

class FetchAvailableCoins implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  private $state, $url;
  /**
   * Create a new job instance.
   *
   * @return void
   */
  public function __construct(State $state=null)
  {
    if($state == null)
      $state = State::firstOrNew(['key' => 'fac_count']);
    if($state->value == null){
      $state->value = 0;
      $state->save();
    }
    $this->state = $state;
    $this->url = "https://min-api.cryptocompare.com/data/all/coinlist";
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    //Initialize symbol_index -> 0
    $state = State::firstOrNew(['key' => 'symbol_index']);
    $state->value = 0;
    $state->save();

    $currecy = Currency::all();
    //Get all coins, break up symbols into proper sized chunks, add new coins to db
    $client = new \GuzzleHttp\Client();
    $res = $client->get("https://min-api.cryptocompare.com/data/all/coinlist");
    $coins = $res->getBody()->getContents();



    $this->state->value++;
    $this->state->save();
    $fetch = new FetchAvailableCoins($this->state);
    //Delay for 24 hours.
    dispatch($fetch)->delay(now()->addDay());
  }
}
