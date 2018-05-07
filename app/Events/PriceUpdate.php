<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PriceUpdate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

  private $coin;
  /**
   * Create a new event instance.
   *
   * @return void
   */
  public function __construct($coin)
  {
    $this->coin = $coin;
  }

  /**
   * Get the channels the event should broadcast on.
   *
   * @return \Illuminate\Broadcasting\Channel|array
   */
  public function broadcastOn()
  {
    //* is not a valid character in channel names.
    $symbol = preg_replace('/\*/', 'ALT', $this->coin->symbol);
    return new Channel('coin.' . $symbol);
  }

  /**
   * Get the data to broadcast.
   *
   * @return array
   */
  public function broadcastWith()
  {
    return [
        'price_usd_cc' => $this->coin->price_usd_cc,
        'price_usd_wci' => $this->coin->price_usd_wci
    ];
  }
}
