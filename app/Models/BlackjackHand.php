<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlackjackHand extends Model
{
    /** @use HasFactory<\Database\Factories\BlackjackHandFactory> */
    use HasFactory;

    protected $fillable = [
        'session_id',
        'player_hand',
        'dealer_hand',
        'bet_amount',
        'status',
    ];

    protected $casts = [
        'player_hand' => 'array',
        'dealer_hand' => 'array'
    ];

    public function session() {
        return $this->belongsTo(BlackjackSession::class, 'session_id');
    }
}
