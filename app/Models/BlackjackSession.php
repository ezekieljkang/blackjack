<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlackjackSession extends Model
{
    /** @use HasFactory<\Database\Factories\BlackjackSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'player_balance',
        'total_wins',
        'total_losses',
        'total_hands_played'
    ];

    public function hands() {
        return $this->hasMany(BlackjackHand::class, 'session_id');
    }
}
