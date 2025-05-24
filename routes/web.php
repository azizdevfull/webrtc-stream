<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('stream');
});
// routes/web.php
Route::get('/stream/admin', function () {
    return view('stream');
});
Route::get('/stream/{streamId}', function ($streamId) {
    if (!Cache::has('stream:' . $streamId)) {
        abort(404, 'Stream not found');
    }
    $streamUrl = "http://localhost:3000/stream/{$streamId}.m3u8"; // Node.js server URL si
    return view('show', ['streamId' => $streamId, 'streamUrl' => $streamUrl]);
})->name('stream.show');