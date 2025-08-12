<?php

namespace App\Models;

use App\Models\Auction;
use MongoDB\Laravel\Eloquent\Model;

class Player extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'players';

    protected $fillable = [
        'auction_id',
        'player_logo',
        'player_name',
        'category',
        'phone',
        'tshirt_size',
        'tshirt_name',
        'tshirt_number',
        'sold_team_id',
        'sold_team',
        'final_bid',
        'status',
    ];

    public function auction()
    {
        return $this->belongsTo(Auction::class);
    }
}