<?php

namespace App\Jobs\Popup\Polling;

use App\Utils\CurrencyUtils;
use App\Utils\StateUtils;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class FetchAvailableCoins implements ShouldQueue
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
    StateUtils::updateOrCreate('symbol_index', 0);
    //Send GET request to cryptocompare.com api to get all their coins.
    $client = new \GuzzleHttp\Client();
    $cc_coins = $client->get($this->url);
    //Decode the body of the response as a JSON object.
    $cc_coins = json_decode($cc_coins->getBody(), true);
    //If the request was successful, parse the data.
    if($cc_coins['Response'] === 'Success'){
      StateUtils::updateOrCreate('cc_base_image_url', $cc_coins['BaseImageUrl']);
      StateUtils::updateOrCreate('cc_base_link_url', $cc_coins['BaseLinkUrl']);
      CurrencyUtils::handleBatchCCInsert($cc_coins['Data']);
    }
    $fetch = new FetchAvailableCoins();
    //Delay for 24 hours.
    dispatch($fetch)->delay(now()->addDay());
  }
}
