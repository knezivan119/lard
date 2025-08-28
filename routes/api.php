<?php

use App\Http\Controllers\AuthController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix( 'v1' )->group( function () {
    Route::get( '/ping', fn () => [ 'ok' => true, 'time' => now()->toISOString() ] );

    Route::post( '/auth/login', [ AuthController::class, 'issueToken' ] );
    Route::middleware( 'auth:sanctum' )->get( '/me', fn () => auth()->user() );
});
