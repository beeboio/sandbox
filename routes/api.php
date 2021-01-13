<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api'])->namespace('Api')->prefix('v1')->group(function() {

  Route::get('/me', function(Request $request) {
    return $request->user();
  });

});