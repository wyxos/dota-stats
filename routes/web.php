<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/demo', function(){
    $page = request('page', 1);

    $config = json_decode(file_get_contents(storage_path('app/config.json')));
    $playerId = $config->playerId ?? null;

    if (!$playerId) {
        return response()->json(['error' => 'Player ID not found in config'], 404);
    }

   $url = "https://api.opendota.com/api/players/{$playerId}/matches";

   $request = Http::get($url, [
       'limit' => 10,
       'offset' => ($page - 1) * 10,
   ]);

   return $request->json();
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
