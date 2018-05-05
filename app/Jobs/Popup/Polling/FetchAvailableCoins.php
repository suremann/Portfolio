<?php

namespace App\Jobs\Popup\Polling;

use App\Utils\CurrencyUtils;
use App\Utils\StateUtils;
use App\Utils\StringUtils;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Currency;

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
    //Get the current symbols as an array. Flip the indices with values so we can access the array by symbol.
    $current = array_flip(Currency::pluck('symbol')->all());
    //Send GET request to cryptocompare.com api to get all their coins.
    $client = new \GuzzleHttp\Client();
    $cc_coins = $client->get($this->url);
    //Decode the body of the response as a JSON object.
    $cc_coins = json_decode($cc_coins->getBody(), true);
    //If the request was successful, parse the data.
    if($cc_coins['Response'] === 'Success'){
      StateUtils::updateOrCreate('cc_base_image_url', $cc_coins['BaseImageUrl']);
      StateUtils::updateOrCreate('cc_base_link_url', $cc_coins['BaseLinkUrl']);

      $symbols_chunk = '';
      $chunk_length = 299; //Characters
      $chunk_index = 0;

      foreach($cc_coins['Data'] as $coin){
        $symbol = strtoupper($coin['Symbol']);
        //Create an entry in the DB for the coin if its not in the current array
        CurrencyUtils::createIfNotInArray($current, $symbol, $coin['Name']);
        //If adding this symbol to the chunk makes the chunk too big. Save it and create a new one with the new symbol.
        if(strlen($symbols_chunk) + strlen($symbol) >= $chunk_length){
          StateUtils::updateOrCreate('symbol_chunk_' . $chunk_index, $symbols_chunk);
          $symbols_chunk = '';
          $chunk_index++;
        }
        $symbols_chunk = StringUtils::appendWithDelimiter($symbols_chunk, $coin['Symbol']);
      }
      StateUtils::updateOrCreate('symbol_chunk_count', $chunk_index);
    }
    $fetch = new FetchAvailableCoins();
    //Delay for 24 hours.
    dispatch($fetch)->delay(now()->addDay());
  }
}
