<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasCollection('admins')) {
            Schema::create('admins', function (Blueprint $collection) {
                $collection->id();
                $collection->string('username');
                $collection->string('password');
                $collection->timestamp('login_at')->nullable();
                $collection->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
