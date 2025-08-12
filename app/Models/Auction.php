<?php

namespace App\Models;

use App\Models\User;
use MongoDB\Laravel\Eloquent\Model;

class Auction extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'auctions';

    protected $fillable = [
        'user_id',
        'auction_logo',
        'auction_name',
        'auction_date',
        'sports_type',
        'point_perteam',
        'minimum_bid',
        'bid_increment',
        'player_perteam',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
