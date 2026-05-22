<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => '5_9_TMDT Backend',
        'status' => 'ok',
        'api_base' => '/api',
    ]);
});
