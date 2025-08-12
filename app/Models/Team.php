<?php

namespace App\Models;

use App\Models\Auction;
use MongoDB\Laravel\Eloquent\Model;

class Team extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'teams';

    protected $fillable = [
        'auction_id',
        'team_logo',
        'team_name',
    ];

    public function auction()
    {
        return $this->belongsTo(Auction::class);
    }
}