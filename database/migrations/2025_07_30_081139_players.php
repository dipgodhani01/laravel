<?php

use Illuminate\Database\Migrations\Migration;
use MongoDB\Laravel\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mongodb';
    public function up(): void
    {
        if (!Schema::hasCollection('players')) {
            Schema::create('players', function (Blueprint $collection) {
                $collection->id();
                $collection->integer('index');
                $collection->string('auction_id')->index();
                $collection->string('sold_team_id')->index();
                $collection->string('sold_team')->index();
                $collection->string('player_logo')->nullable();
                $collection->string('player_name');
                $collection->integer('minimum_bid');
                $collection->integer('final_bid');
                $collection->string('category');
                $collection->integer('phone');
                $collection->string('tshirt_size');
                $collection->string('trouser_size')->nullable();
                $collection->string('tshirt_name');
                $collection->integer('tshirt_number');
                $collection->string('status')->default('pending');
                $collection->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};