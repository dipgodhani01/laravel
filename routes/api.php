<?php

use App\Http\Controllers\AuctionController;
use App\Http\Controllers\AuctionStartController;
use App\Http\Controllers\Auth\AuthenticatedUserController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

Route::get('/auth/google-login', [GoogleAuthController::class, 'handleGoogleCallback']);
Route::post('/auth/logout', [GoogleAuthController::class, 'logout']);

Route::middleware(['auth'])->group(function () {
    Route::get('/auth/user', [AuthenticatedUserController::class, 'getUser']);

    Route::post('/auction/create-auction', [AuctionController::class, 'createAuction']);
    Route::get('/auction/get/all', [AuctionController::class, 'getAuctions']);
    Route::get('/auction/get-one/{auctionId}', [AuctionController::class, 'getAuctionById']);
    Route::put('/auction/update', [AuctionController::class, 'updateAuction']);
    Route::delete('/auction/delete/{auctionId}', [AuctionController::class, 'deleteAuction']);

    Route::post('/player/create-player', [PlayerController::class, 'createPlayer']);
    Route::get('/player/get/all', [PlayerController::class, 'getPlayers']);
    Route::get('/player/get-by-team', [PlayerController::class, 'getPlayersByTeam']);
    Route::get('/player/get-one/{playerId}', [PlayerController::class, 'getPlayerById']);
    Route::put('/player/update', [PlayerController::class, 'updatePlayer']);
    Route::put('/player/update/minimum_bid', [PlayerController::class, 'updateMinimumBid']);
    Route::delete('/player/delete/{playerId}', [PlayerController::class, 'deletePlayer']);

    Route::post('/team/create-team', [TeamController::class, 'createTeam']);
    Route::get('/team/get/all', [TeamController::class, 'getTeams']);
    Route::get('/team/get-one/{teamId}', [TeamController::class, 'getTeamById']);
    Route::put('/team/update', [TeamController::class, 'updateTeam']);
    Route::delete('/team/delete/{teamId}', [TeamController::class, 'deleteTeam']);

    Route::put('/auction/sold-player', [AuctionStartController::class, 'soldPlayer']);
    Route::put('/auction/unsold-player', [AuctionStartController::class, 'unsoldPlayer']);
    Route::post('/auction/unsold-to-sold', [AuctionStartController::class, 'unsoldToSold']);
});
