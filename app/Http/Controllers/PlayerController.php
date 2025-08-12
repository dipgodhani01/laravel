<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlayerController extends Controller
{
    public function createPlayer(Request $request)
    {
        try {
            if (!$request->auction_id) {
                return response()->json([
                    'message' => 'Auction Required!',
                    'error' => true,
                ], 500);
            }

            $request->validate([
                'player_logo' => 'required|file|mimes:jpeg,png,jpg,gif',
                'player_name' => 'required|string',
                'category' => 'required|string',
                'phone' => 'required|numeric',
                'tshirt_size' => 'required|string',
                'trouser_size' => 'nullable|string',
                'tshirt_name' => 'required|string',
                'tshirt_number' => 'required|numeric',
            ]);

            $image = $request->file('player_logo');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('images/players');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            $image->move($destinationPath, $imageName);
            $imageUrl = url('images/players/' . $imageName);

            $lastPlayer = Player::where('auction_id', $request->auction_id)
                ->orderBy('index', 'desc')
                ->first();

            $nextIndex = $lastPlayer ? $lastPlayer->index + 1 : 1;

            $player = new Player();
            $player->index = $nextIndex;
            $player->auction_id = $request->auction_id;
            $player->player_logo = $imageUrl;
            $player->player_name = $request->player_name;
            $player->category = $request->category;
            $player->phone = $request->phone;
            $player->tshirt_size = $request->tshirt_size;
            $player->trouser_size = $request->trouser_size ?? "";
            $player->tshirt_name = $request->tshirt_name;
            $player->tshirt_number = $request->tshirt_number;
            $player->minimum_bid = 100000;
            $player->status = 'pending';
            $player->sold_team_id = "";
            $player->sold_team = "";
            $player->final_bid = 0;

            $player->save();

            return response()->json([
                'success' => true,
                'message' => 'Player added successfully.',
                'data' => $player,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getPlayers(Request $request)
    {
        $auctionId = $request->query('auction_id');
        if (!$auctionId) {
            return response()->json([
                'success' => false,
                'message' => 'Auction not found!',
            ], 400);
        }

        $allPlayers = Player::where('auction_id', $auctionId)
            ->orderBy('index')
            ->get();
        try {
            return response()->json([
                'success' => true,
                'data' =>  $allPlayers,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' =>  $e->getMessage(),
            ], 500);
        }
    }
    public function getPlayersByTeam(Request $request)
    {
        $team_id = $request->query('team_id');
        if (!$team_id) {
            return response()->json([
                'success' => false,
                'message' => 'Team not found!',
            ], 400);
        }

        try {
            $players = Player::where('sold_team_id', $team_id)
                ->orderBy('index')
                ->get();

            return response()->json([
                'success' => true,
                'data' =>  $players,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' =>  $e->getMessage(),
            ], 500);
        }
    }

    public function getPlayerById($playerId)
    {
        try {
            $player = Player::where('_id', $playerId)->first();
            if (!$player) {
                return response()->json([
                    'success' => false,
                    'message' => 'Player not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $player
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updatePlayer(Request $request)
    {
        try {
            $playerId = $request->input('player_id');
            $player = Player::where('_id', $playerId)->first();

            if (!$player) {
                return response()->json([
                    'message' => 'Player Not Found!',
                    'error' => true,
                ], 404);
            }

            $request->validate([
                'player_logo' => 'nullable|file|mimes:jpeg,png,jpg,gif',
                'player_name' => 'required|string',
                'category' => 'required|string',
                'phone' => 'required|numeric',
                'tshirt_size' => 'required|string',
                'tshirt_name' => 'required|string',
                'tshirt_number' => 'required|numeric',
            ]);

            if ($request->hasFile('player_logo')) {
                $image = $request->file('player_logo');
                $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('images/players');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                $image->move($destinationPath, $imageName);
                $player->player_logo = url('images/players/' . $imageName);
            }


            $player->player_name = $request->player_name;
            $player->category = $request->category;
            $player->phone = $request->phone;
            $player->tshirt_size = $request->tshirt_size;
            $player->trouser_size = $request->trouser_size ?? "";
            $player->tshirt_name = $request->tshirt_name;
            $player->tshirt_number = $request->tshirt_number;
            $player->minimum_bid = 100000;

            $player->save();

            return response()->json([
                'success' => true,
                'message' => 'Player updated successfully.',
                'data' => $player,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateMinimumBid(Request $request)
    {
        try {
            $playerId = $request->input('player_id');
            $player = Player::where('_id', $playerId)->first();
            if (!$player) {
                return response()->json([
                    'message' => 'Player Not Found!',
                    'error' => true,
                ], 404);
            }

            $request->validate([
                'minimum_bid' => 'required|numeric',
            ]);

            $player->minimum_bid = $request->minimum_bid;
            $player->save();

            return response()->json([
                'success' => true,
                'message' => 'Minimum bid updated successfully.',
                'data' => $player,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function deletePlayer($playerId)
    {
        try {
            $player = Player::where('_id', $playerId)->first();

            if (!$player) {
                return response()->json([
                    'success' => false,
                    'message' => 'Player not found'
                ], 404);
            }
            $player->delete();

            return response()->json([
                'success' => true,
                'message' => 'Player deleted successfully',
                'data' => $player,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}