<?php
namespace Beebo\Concerns;

use Beebo\SocketIO\Server;
use Illuminate\Support\Facades\Log;
use React\Socket\Connector as Socket;
use Ratchet\Client\Connector as Client;
use Ratchet\Client\WebSocket as Connection;
use Beebo\Pusher\Socket as Pusher;
use Spatie\Url\Url;
use Illuminate\Support\Facades\URL as Routes;

/**
 * Adding the PubSub trait to a Server creates a Bus from which
 * the Server can receive signals from the Origin and broadcast
 * messages to the entire network.
 * Trait PubSub
 * @package Beebo\Traits
 */
trait PubSub
{
  /**
   * @var Pusher
   */
  protected $network;

  /**
   * @throws \Exception
   */
  function initializePubSub()
  {
    $this->network = Pusher::make($this);

    if (!$this instanceof Server) {
      throw new \Exception("This Trait can only be applied to a Beebo\SocketIO\Server.");
    }

    if (!$options = config('websockets.pubsub.options')) {
      throw new \Exception("Missing PubSub configuration: websockets.pubsub.options");
    }

    if (!$appKey = config('websockets.pubsub.key')) {
      throw new \Exception("Missing PubSub configuration: websockets.pubsub.key");
    }

    // TODO: stash the secret, for authorizing channels

    if (!$host = config('websockets.pubsub.host')) {
      $host = Url::fromString(Routes::to('/'))->getHost();
    }

    if (!$port = config('websockets.pubsub.port')) {
      $port = config('websockets.dashboard.port');
    }

    $pubSubUrl = (new Url())
      ->withScheme('https')
      ->withHost($host)
      ->withPath("/app/{$appKey}")
      ->withPort($port)
      ->withQuery(http_build_query([
        'protocol' => 7,
        'client' => 'js',
        'version' => '7.0.2',
        'flash' => 'false',
      ]));

    $client = new Client(Server::loop(), new Socket(Server::loop(), $options));

    $client(str_replace('https://', 'wss://', (string) $pubSubUrl))
      ->then(function(Connection $conn) {
        $this->network->setConnection($conn);
      }, function(\Exception $e){
        Log::error($e);

        // TODO: implement some sort of retry loop
        throw $e;
      });
  }

  /**
   * @param string $channelName
   * @param bool $private
   * @return Pusher
   */
  function subscribe($channelName, $private = false)
  {
    return $this->network->subscribe($channelName, $private);
  }

}