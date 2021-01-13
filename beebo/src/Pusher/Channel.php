<?php
namespace Beebo\Pusher;

class Channel
{
  /**
   * @var Socket
   */
  protected $socket;

  /**
   * @var string
   */
  protected $channelName;

  /**
   * @var bool
   */
  protected $private;

  /**
   * @param Socket $socket
   * @param $channelName
   * @param bool $private
   * @return Channel
   */
  static function make(Socket $socket, $channelName, $private = false)
  {
    $channel = new self;
    $channel->socket = $socket;
    $channel->channelName = $channelName;
    $channel->private = !!$private;

    return $channel;
  }

}