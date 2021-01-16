# Sandbox

This project is an exploration of Laravel, WebSockets, Socket.IO, and aframe.

---

**WARNING:** Nothing in this codebase is intended for use in production at this time. 
It is just a sandbox.
But if you do decide to use any of the code that is not otherwise licensed to some
other copyright holder, please observe the terms of the license (MIT).

---

# Beebo.io 
A framework for crafting high performance, scalable, full-duplex, interoperable networked experiences.

## What is Beebo.io?
Beebo.io is a framework that unlocks the potential of Laravel to power your real-time applications. With Beebo, you can use PHP to engineer, deploy, secure, and automatically scale full-duplex networks of and for people, places, things, and experiences.

A pure PHP implementation of the real-time Socket.io engine allows your client applications to connect to Beebo-powered networks using WebSockets. Once connected, your applications and your servers share a real-time, two-way channel of communication, increasing the speed of transmission by decreasing the overhead in each message.

With Beebo, everything you can do with Laravel you can now do in real-time. Authentication, payment processing, subscription management, remote system administration, queuing, full-text search, and many, many other solutions in the Laravel ecosystem live right alongside the core business logic in your server.

## How does it work?
Beebo.io stands on the shoulders of giants. Laravel provides a best-of-class framework for programming not only web applications, but also for creating command-line tools: super handy for system administration. Much functionality can be added to a Laravel project just by installing PHP packages via Composer.

One such package, Laravel WebSockets published by BeyondCode, makes it possible to run a WebSocket server right inside your Laravel codebase, granting that server direct access to your Laravel application container and all the functionality inside. This means that connected clients can execute code in your application container in real-time (if they follow the rules, of course).

A connected client can be virtually any kind of device: the web browser running on your laptop, a native mobile application, or a game built with Unity running on an Oculus Quest. Beebo even welcomes connections from the Internet of Things (IoT), so your refrigerator can get in the game. If a device can negotiate a WebSocket or trigger a webhook, it can talk to a Beebo network.

On top of Laravel WebSockets, we implemented the Socket.io protocol. Without a protocol to follow, a WebSocket is little more than a way to send and receive plain text, just really fast. With the addition of a protocol, clients can send and receive encoded, structured data, binary data like images and audio, and can even call remote code and receive responses asyncronously.

Here’s the world’s simplest chat client, written using Socket.io’s JavaScript client library:

```js
// connect to the server
let socket = io(':6001', {
  path: '/chat',
  query: {
    appKey: '...',
  },
  transports: ['websocket']
// when a message event is received, print the message
}).on('message', (message) => {
  console.log(message)
})
// using the console, send a message:
socket.send('Hello, world!')
```

And the code on the server? It can be equally simple in nature:

```php
<?php
namespace App\Sockets\Servers;

use Beebo\SocketIO\Event;
use Beebo\SocketIO\Server;
use Beebo\SocketIO\Socket;

class Chat extends Server
{
  // anytime a client connects to the server...
  public function onConnection(Socket $socket)
  {
    // have the client join a room called “chat”
    $socket->join('chat');
    // anytime the client sends a “message” to the server
    $socket->on('message', function(Event $request, $message) {
      // echo that message to all the other clients in the “chat” room
      $request->socket->to('chat')->send($message);
    });
  }
}
```

In the spirit of Laravel, Beebo is also “progressive:” as your skills and the needs of your projects grow, so too does Beebo. The framework lends itself to a variety of topologies, from small solutions in which the whole stack runs on a single server node to large solutions with specialized nodes and connected by the service bus.

As you begin to experiment with Beebo, you can run the entire stack on your own laptop using Docker Desktop. Then, when you’re ready to go live for the first time, you can run everything on a single server that you can even provision from your command line using Laravel Forge. As your product becomes more successful, you can scale easily and inexpensively using Laravel Vapor and Amazon AWS. 

## Getting Started
To get started with Beebo, first, follow the instructions for setting up a new Laravel project, found here.

The best way to get started is to just clone the sandbox from GitHub:

```bash
> git clone git@github.com:beeboio/sandbox
```

(To be continued.)

## License

Copyright 2021 Aaron Collegeman

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

https://github.com/beeboio
