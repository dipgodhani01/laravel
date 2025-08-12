<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuctionController extends Controller
{
    public function createAuction(Request $request)
    {
        try {
            if (!$request->user_id) {
                return response()->json([
                    'message' => 'User not found!',
                    'error' => true,
                ], 500);
            }

            $request->validate([
                'auction_logo' => 'required|file|mimes:jpeg,png,jpg,gif',
                'auction_name' => 'required|string',
                'auction_date' => 'required|date',
                'sports_type' => 'required|string',
                'point_perteam' => 'required|numeric',
                'minimum_bid' => 'required|numeric',
                'bid_increment' => 'required|numeric',
                'player_perteam' => 'required|numeric',
            ]);

            $image = $request->file('auction_logo');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('images/auctions');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            $image->move($destinationPath, $imageName);
            $imageUrl = url('images/auctions/' . $imageName);

            $auction = new Auction();
            $auction->user_id = $request->user_id;
            $auction->auction_logo = $imageUrl;
            $auction->auction_name = $request->auction_name;
            $auction->auction_date = $request->auction_date;
            $auction->sports_type = $request->sports_type;
            $auction->point_perteam = (int) $request->point_perteam;
            $auction->minimum_bid = (int) $request->minimum_bid;
            $auction->bid_increment = (int) $request->bid_increment;
            $auction->player_perteam = (int) $request->player_perteam;

            $auction->save();

            return response()->json([
                'success' => true,
                'message' => 'Auction created successfully.',
                'data' => $auction,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function getAuctions(Request $request)
    {
        $userId = $request->query('user_id');
        if (!$userId) {
            return response()->json(['message' => 'User not found!'], 404);
        }

        $allUserAuctions = Auction::where('user_id', $request->user_id)
            ->orderBy('created_at', 'desc')
            ->get();

        try {
            return response()->json([
                'success' => true,
                'data' =>  $allUserAuctions,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' =>  $e->getMessage(),
            ], 500);
        }
    }

    public function deleteAuction($auctionId)
    {
        try {
            $auction = Auction::where('_id', $auctionId)->first();
            if (!$auction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Auction not found'
                ], 404);
            }
            Player::where('auction_id', $auctionId)->delete();
            Team::where('auction_id', $auctionId)->delete();
            $auction->delete();

            return response()->json([
                'success' => true,
                'message' => 'Auction deleted successfully',
                'data' => $auction,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getAuctionById($auctionId)
    {
        try {
            $auction = Auction::where('_id', $auctionId)->first();

            if (!$auction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Auction not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $auction
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }



    public function updateAuction(Request $request)
    {
        try {
            $auctionId = $request->input('auction_id');
            $auction = Auction::where('_id', $auctionId)->first();
            if (!$auction) {
                return response()->json(['message' => 'Auction not found'], 404);
            }

            $request->validate([
                'auction_name' => 'required|string',
                'auction_date' => 'required|date',
                'sports_type' => 'required|string',
                'point_perteam' => 'required|numeric',
                'minimum_bid' => 'required|numeric',
                'bid_increment' => 'required|numeric',
                'player_perteam' => 'required|numeric',
                'auction_logo' => 'nullable|file|mimes:jpeg,png,jpg,gif',
            ]);

            if ($request->hasFile('auction_logo')) {
                $image = $request->file('auction_logo');
                $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('images/auctions');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                $image->move($destinationPath, $imageName);
                $auction->auction_logo = url('images/auctions/' . $imageName);
            }

            $auction->auction_name = $request->auction_name;
            $auction->auction_date = $request->auction_date;
            $auction->sports_type = $request->sports_type;
            $auction->point_perteam = $request->point_perteam;
            $auction->minimum_bid = $request->minimum_bid;
            $auction->bid_increment = $request->bid_increment;
            $auction->player_perteam = $request->player_perteam;

            $auction->save();

            return response()->json([
                'success' => true,
                'message' => 'Auction updated successfully.',
                'data' => $auction,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}