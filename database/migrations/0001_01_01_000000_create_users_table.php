<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasCollection('users')) {
            Schema::create('users', function (Blueprint $collection) {
                $collection->index('user_id');
                $collection->index('name');
                $collection->unique('email');
                $collection->index('image');
                $collection->index('email_verified_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
