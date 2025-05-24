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
    return view('show', ['streamId' => $streamId]);
})->name('stream.show');

