<?php
namespace Beebo\Providers;

use Beebo\WebSocketApp;
use Beebo\SocketIO\Parsers\Parser;
use BeyondCode\LaravelWebSockets\Apps\App;
use BeyondCode\LaravelWebSockets\Apps\ConfigAppProvider;

class WebSocketAppProvider extends ConfigAppProvider
{
  protected function instanciate(?array $appAttributes): ?App
  {
    if (! $appAttributes) {
        return null;
    }

    $app = new WebSocketApp(
        $appAttributes['id'],
        $appAttributes['key'],
        $appAttributes['secret']
    );

    if (isset($appAttributes['name'])) {
        $app->setName($appAttributes['name']);
    }

    if (isset($appAttributes['host'])) {
        $app->setHost($appAttributes['host']);
    }

    if (isset($appAttributes['path'])) {
        $app->setPath($appAttributes['path']);
    }

    $app
        ->enableClientMessages($appAttributes['enable_client_messages'])
        ->enableStatistics($appAttributes['enable_statistics'])
        ->setCapacity($appAttributes['capacity'] ?? null)
        ->setSocketIOParser($appAttributes['socketio_parser'] ?? Parser::TYPE_DEFAULT);

    return $app;
  }


}