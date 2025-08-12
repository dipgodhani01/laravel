<?php

use Illuminate\Database\Migrations\Migration;
use MongoDB\Laravel\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mongodb';
    public function up(): void
    {
        if (!Schema::hasCollection('auctions')) {
            Schema::create('auctions', function (Blueprint $collection) {
                $collection->id();
                $collection->string('user_id')->index();
                $collection->string('auction_logo')->nullable();
                $collection->string('auction_name');
                $collection->date('auction_date');
                $collection->string('sports_type');
                $collection->integer('point_perteam');
                $collection->integer('minimum_bid');
                $collection->integer('bid_increment');
                $collection->integer('player_perteam');
                $collection->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('auctions');
    }
};
