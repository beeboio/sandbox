<?php
namespace Beebo\Concerns;

use Beebo\SocketIO\Server;
use Illuminate\Support\Facades\Log;
use React\Socket\Connector as Socket;
use Ratchet\Client\Connector as Client;
use Ratchet\Client\WebSocket as Connection;
use Beebo\Pusher\Socket as Pusher;

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

    // TODO: make this configurable
    $options = [
      'dns' => '127.0.0.1',
      'tls' => [
        'verify_peer' => false,
      ]
    ];

    // TODO: make this configurable
    $url = 'wss://box.beebo.test:6001/app/box.beebo?protocol=7&client=js&version=7.0.2&flash=false';

    $client = new Client(Server::loop(), new Socket(Server::loop(), $options));

    $client($url)
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