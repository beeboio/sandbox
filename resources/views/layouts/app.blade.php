<!doctype html>
<html lang="{{ app()->getLocale() }}">
  <head>
    <title></title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/app.css', true) }}">
  </head>
  <body>
    <div id="app">
      @yield('content')
      <auth-modal></auth-modal>
    </div>
    <script src="{{ asset('js/app.js', true) }}"></script>
  </body>
</html>