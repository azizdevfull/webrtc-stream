<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/start-screen-share', function () {
    $streamId = 'okaygotaxi-' . Str::random(10);
    Cache::put('stream:' . $streamId, true, now()->addHours(1)); // 1 soat saqlash
    $streamUrl = route('stream.show', ['streamId' => $streamId]);
    return response()->json([
        'streamId' => $streamId,
        'streamUrl' => $streamUrl,
    ]);
});

Route::post('/offer', function () {
    $data = request()->all();
    Cache::put('offer:' . $data['streamId'], $data['offer'], now()->addMinutes(10));
    return response()->json(['status' => 'Offer received']);
});

Route::post('/answer', function () {
    $data = request()->all();
    Cache::put('answer:' . $data['streamId'], $data['answer'], now()->addMinutes(10));
    return response()->json(['status' => 'Answer received']);
});

Route::get('/answer/{streamId}', function ($streamId) {
    return response()->json(['answer' => Cache::get('answer:' . $streamId)]);
});

Route::post('/ice-candidate', function () {
    $data = request()->all();
    $candidates = Cache::get('candidates:' . $data['streamId'], []);
    $candidates[] = $data['candidate'];
    Cache::put('candidates:' . $data['streamId'], $candidates, now()->addMinutes(10));
    return response()->json(['status' => 'Candidate received']);
});

Route::get('/ice-candidates/{streamId}', function ($streamId) {
    return response()->json(['candidates' => Cache::get('candidates:' . $streamId, [])]);
});