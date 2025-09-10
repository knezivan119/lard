<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\PaginateRequest;


// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Route::prefix( 'v1' )->group( function () {
//     // Route::get( '/ping', fn () => [ 'ok'=> true, 'time' => now()->toISOString() ] );
//     // Route::post( '/auth/login', [ AuthController::class, 'issueToken' ] );

//     // Route::middleware( 'auth:sanctum', PaginateRequest::class )->group( function () {
//     //     Route::get( '/user', [ AuthController::class, 'current' ] );
//     // });
// });

Route::prefix( 'v1' )->group( function () {
    Route::get( '/ping', fn () => [ 'ok'=> true, 'time' => now()->toISOString() ] );
    Route::post( '/auth/login', [ AuthController::class, 'issueToken' ] );

    Route::middleware( 'auth:sanctum', PaginateRequest::class )->group( function () {
        Route::get( 'user', [ AuthController::class, 'current' ] );
        Route::apiResource( 'users', UserController::class );

        Route::get('/account', [ AccountController::class, 'current' ]);
        Route::post('/accounts/{account}/logo', [ AccountController::class, 'storeLogo' ]);
        Route::apiResource( 'accounts', AccountController::class );

    });
});