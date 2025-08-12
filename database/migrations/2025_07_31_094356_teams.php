<?php

use Illuminate\Database\Migrations\Migration;
use MongoDB\Laravel\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mongodb';
    public function up(): void
    {
        if (!Schema::hasCollection('teams')) {
            Schema::create('teams', function (Blueprint $collection) {
                $collection->id();
                $collection->string('auction_id')->index();
                $collection->string('team_logo')->nullable();
                $collection->string('team_name');
                $collection->integer('team_balance');
                $collection->integer('remember_balance');
                $collection->integer('reserve_balance');
                $collection->integer('player_allow');
                $collection->integer('player_buy');
                $collection->integer('player_remember');
                $collection->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};