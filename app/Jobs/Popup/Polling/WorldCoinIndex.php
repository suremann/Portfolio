<?php

namespace App\Jobs\Popup\Polling;

use App\Models\Currency;
use App\Utils\CurrencyUtils;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class WorldCoinIndex implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  private $url;
  /**
   * Create a new job instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->url = 'https://www.worldcoinindex.com/apiservice/getmarkets?key=EIEvtUtrHbBQlHVi6PIkpngxOZzW6e&fiat=usd';
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    //Send GET request to cryptocompare.com api to get coin prices.
    $client = new \GuzzleHttp\Client();
    $wci_prices = $client->get($this->url);
    //Decode the body of the response as a JSON object.
    $wci_prices = json_decode($wci_prices->getBody(), true);
    //Must do a RAW SQL query. Need to do a batch INSERT / UPDATE
    if(isset($wci_prices['Markets'][0])) {
      //Pass the USD market values and the current coin symbols.
      CurrencyUtils::handleBatchWCI($wci_prices['Markets'][0]);
    }
    //Poll from WorldCoinIndex
    $wcindex = new WorldCoinIndex();
    //Delay for 5 minutes.
    dispatch($wcindex)->delay(now()->addMinutes(1));
  }
}
