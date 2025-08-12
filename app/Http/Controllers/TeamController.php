<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TeamController extends Controller
{
    public function createTeam(Request $request)
    {
        try {
            if (!$request->auction_id) {
                return response()->json([
                    'message' => 'Auction Required!',
                    'error' => true,
                ], 500);
            }
            $auction = Auction::where('_id', $request->auction_id)->first();

            $request->validate([
                'team_logo' => 'required|file|mimes:jpeg,png,jpg,gif',
                'team_name' => 'required|string',
            ]);

            $image = $request->file('team_logo');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('images/teams');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            $image->move($destinationPath, $imageName);
            $imageUrl = url('images/teams/' . $imageName);
            $min_bid = (int) $auction->minimum_bid;

            $team = new Team();
            $team->auction_id = $request->auction_id;
            $team->team_logo = $imageUrl;
            $team->team_name = $request->team_name;
            $team->team_balance = (int) $auction->point_perteam;
            $team->remember_balance = (int) $team->team_balance;
            $team->reserve_balance = (int) $min_bid * $auction->player_perteam;
            $team->player_allow = (int) $auction->player_perteam;
            $team->player_buy = 0;
            $team->player_remember = (int) $auction->player_perteam;
            $team->save();

            return response()->json([
                'success' => true,
                'message' => 'Team created successfully.',
                'data' => $team,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function getTeams(Request $request)
    {
        try {
            $auctionId = $request->query('auction_id');
            if (!$auctionId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Auction not found!',
                ], 400);
            }

            $allTeams = Team::where('auction_id', $auctionId)
                ->orderBy('created_at', 'desc')
                ->get();
            return response()->json([
                'success' => true,
                'data' =>  $allTeams,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' =>  $e->getMessage(),
            ], 500);
        }
    }


    public function getTeamById($teamId)
    {
        try {
            $team = Team::where('_id', $teamId)->first();

            if (!$team) {
                return response()->json([
                    'success' => false,
                    'message' => 'Player not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $team
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function updateTeam(Request $request)
    {
        try {
            $teamId = $request->input('team_id');
            $team = Team::where('_id', $teamId)->first();
            if (!$team) {
                return response()->json([
                    'message' => 'Team Not Found!',
                    'error' => true,
                ], 404);
            }

            $request->validate([
                'team_logo' => 'nullable|file|mimes:jpeg,png,jpg,gif',
                'team_name' => 'required|string',
            ]);

            if ($request->hasFile('team_logo')) {
                $image = $request->file('team_logo');
                $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('images/teams');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                $image->move($destinationPath, $imageName);
                $team->team_logo = url('images/teams/' . $imageName);
            }

            $team->team_name = $request->team_name;
            $team->save();

            return response()->json([
                'success' => true,
                'message' => 'Team updated successfully.',
                'data' => $team,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function deleteTeam($teamId)
    {
        try {
            $team = Team::where('_id', $teamId)->first();
            if (!$team) {
                return response()->json([
                    'success' => false,
                    'message' => 'Team not found'
                ], 404);
            }

            $team->delete();

            return response()->json([
                'success' => true,
                'message' => 'Team deleted successfully',
                'data' => $team,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
