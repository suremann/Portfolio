<?php

namespace App\Jobs\Popup\Polling;

use App\Utils\CurrencyUtils;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\State;

class CryptoCompare implements ShouldQueue
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
    //Set the URL for retrieving prices from cryptocompare.com
    $this->url = "https://min-api.cryptocompare.com/data/pricemulti?tsyms=USD&fsyms=";
  }


  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    //Get the number of chunks stored in the DB
    $symbol_chunk_count = State::where('key', 'symbol_chunk_count')->first()->value;
    //Get the current symbol_index
    $symbol_index = State::where('key','symbol_index')->first(); //Don't get value immediately. We will update its value.
    $symbol_chunk = State::where('key', 'symbol_chunk_' . $symbol_index->value)->first()->value;

    //We got the chunk, now we can increment the symbol_index in the DB
    $symbol_index->value = ($symbol_index->value + 1) % $symbol_chunk_count;
    $symbol_index->save();

    //Send GET request to cryptocompare.com api to get coin prices.
    $client = new \GuzzleHttp\Client();
    $cc_prices = $client->get($this->url . $symbol_chunk);
    //Decode the body of the response as a JSON object.
    $cc_prices = json_decode($cc_prices->getBody(), true);

    CurrencyUtils::handleBatchCCUpdate($cc_prices, explode(',', $symbol_chunk));

    $ccompare = new CryptoCompare();
    //delay for 15 seconds.
    dispatch($ccompare)->delay(now()->addSeconds(15));
  }
}
