<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\Request;

class AuctionStartController extends Controller
{
    public function soldPlayer(Request $request)
    {
        try {
            $auction = Auction::findOrFail($request->auction_id);
            $player = Player::where('id', $request->player_id)
                ->where('auction_id', $auction->id)
                ->firstOrFail();
            $team = Team::where('id', $request->sold_team_id)
                ->where('auction_id', $auction->id)
                ->firstOrFail();

            $player->update([
                'sold_team_id' => $request->sold_team_id,
                'final_bid' => (int) $request->final_bid,
                'sold_team' => $team->team_name,
                'status' => 'sold',
            ]);
            $playerBuyCount = Player::where('sold_team_id', $request->sold_team_id)
                ->where('auction_id', $auction->id)
                ->where('status', 'sold')
                ->count();
            $team->remember_balance = $team->remember_balance - $request->final_bid;
            $team->player_remember = $team->player_allow - $playerBuyCount;
            $team->reserve_balance = $team->player_remember * $auction->minimum_bid;
            $team->player_buy = $playerBuyCount;

            $player->save();
            $team->save();

            return response()->json([
                'success' => true,
                'message' => 'Player sold successfully.',
                'data' => [
                    'player' => $player,
                    'team' => $team,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function unsoldPlayer(Request $request)
    {
        try {
            $auction = Auction::findOrFail($request->auction_id);
            $player = Player::where('id', $request->player_id)
                ->where('auction_id', $auction->id)
                ->firstOrFail();

            $player->update([
                'status' => 'unsold',
            ]);

            $player->save();

            return response()->json([
                'success' => true,
                'message' => 'Unsold',
                'data' => $player,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function unsoldToSold(Request $request)
    {
        $auction_id = $request->query('auction_id');

        try {
            $unsoldCount = Player::where('auction_id', $auction_id)
                ->where('status', 'unsold')
                ->count();

            if ($unsoldCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No unsold players found for this auction.',
                ], 404);
            }
            Player::where('auction_id', $auction_id)
                ->where('status', 'unsold')
                ->update(['status' => 'pending']);

            return response()->json([
                'success' => true,
                'message' => 'All unsold players are now available for auction.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
