<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('blackjack_sessions', function (Blueprint $table) {
            $table->id();
            $table->integer('player_balance')->default(5000);
            $table->integer('total_wins')->default(0);
            $table->integer('total_losses')->default(0);
            $table->integer('total_hands_played')->default(0);
            $table->timestamps();
        });

        Schema::create('blackjack_hands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('blackjack_sessions')->onDelete('cascade');
            $table->json('player_hand'); // Stores player's hand
            $table->json('dealer_hand'); // Stores dealer's hand
            $table->integer('bet_amount');
            $table->enum('status', ['playing', 'win', 'loss', 'push'])->default('playing');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('blackjack_hands');
        Schema::dropIfExists('blackjack_sessions');
    }
};
