<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingFees extends Model
{
    use HasFactory;

    public $table = 'pending_fees';
    protected $fillable = [
        'user_id',
        'charge_id',
        'customer_id',
        'amount',
        'created_at',
        'updated_at',
    ];
}