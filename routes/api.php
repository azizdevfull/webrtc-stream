<?php

use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
// routes/api.php
Route::post('/start-screen-share', function () {
    try {
        $response = Http::post('http://localhost:3000/api/start-screen-share');
        if ($response->successful()) {
            $data = $response->json();
            $streamId = $data['streamId'];
            $streamUrl = $data['streamUrl'];
            Cache::put('stream:' . $streamId, true, now()->addHours(1));
            return response()->json([
                'streamId' => $streamId,
                'streamUrl' => $streamUrl,
            ]);
        } else {
            throw new RequestException($response);
        }
    } catch (RequestException $e) {
        return response()->json(['error' => 'Failed to connect to Node.js server'], 500);
    }
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