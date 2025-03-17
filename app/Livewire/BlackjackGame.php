<?php

namespace App\Livewire;

use App\Models\BlackjackHand;
use App\Models\BlackjackSession;
use Livewire\Component;

class BlackjackGame extends Component
{
    public $session;
    public $hands;
    public $deck;
    public $playerBalance = 5000;
    public $betAmount = 0;
    public $reshuffleAfterPlay = false;
    public $deckNumber = 2; // adjust this number for different deck types(ex. 2 deck, 6 deck, 8 deck)

    public function mount() 
    {
        $this->session = BlackjackSession::firstOrCreate([], ['player_balance' => 5000]);
        $this->hands = $this->session->hands()->get()->toArray();
        $this->initializeDeck();
    }

    private function initializeDeck() 
    {
        $suits = ['hearts', 'diamonds', 'clubs', 'spades'];
        $values = [2, 3, 4, 5, 6, 7, 8, 9, 10, 'J', 'Q', 'K', 'A'];
        $this->deck = [];

        // create two decks
        foreach(range(1, $this -> deckNumber) as $deck) {
            foreach ($suits as $suit) {
                foreach ($values as $value) {
                    $this->deck[] = ['suit' => $suit, 'value' => $value];
                }
            }
        }

        shuffle($this->deck);

        // Deck penetration logic (placing wild card randomly)
        $penetrationPoint = rand(floor(count($this->deck) * 0.75), floor(count($this->deck) * 0.80));
        array_splice($this->deck, $penetrationPoint, 0, [['suit' => 'wild', 'value' => 'wild']]);
    }

    private function checkForWildCardDuringDeal($card) 
    {
        if ($card['value'] === 'wild') {
            $this->reshuffleAfterPlay = true; // Mark for reshuffle after play ends
        }
    }

    private function reshuffleDeckIfNeeded() 
    {
        if ($this->reshuffleAfterPlay) {
            $this->initializeDeck();
            $this->reshuffleAfterPlay = false;
        }
    }

    public function placeBet($amount) 
    {
        if($amount < 25) return; // prevent invald bets ($25 minimum bet)
        if($this->session->player_balance >= $amount) {
            $this->betAmount = $amount;
        }
    }

    public function addBet($amount)
    {
        if (($this->betAmount + $amount) <= $this->playerBalance) {
            $this->betAmount += $amount;
        }
    }

    public function clearBet()
    {
        $this->betAmount = 0;
    }

    public function dealCards() 
    {
        if ($this->betAmount <= 25) return; // double check if minimum bet is $25

        // Deal cards in the correct sequence
        $playerHand = [array_pop($this->deck)];
        $this->checkForWildCardDuringDeal(end($playerHand));

        $dealerHand = [array_pop($this->deck)];
        $this->checkForWildCardDuringDeal(end($dealerHand));

        $playerHand[] = array_pop($this->deck);
        $this->checkForWildCardDuringDeal(end($playerHand));

        $dealerSecondCard = array_pop($this->deck);
        $this->checkForWildCardDuringDeal($dealerSecondCard);

        $hand = BlackjackHand::create([
            'session_id' => $this->session->id,
            'player_hand' => $playerHand,
            'dealer_hand' => [$dealerHand, ['suit' => 'hidden', 'value' => 'hidden']],
            'bet_amount' => $this->betAmount,
            'status' => 'playing',
        ]);

        //Blackjack check immediately after dealing
        if ($this->calculateHandValue($playerHand) === 21) {
            if ($this->calculateHandValue([$dealerHand[0], $dealerSecondCard]) === 21) {
                $hand->update(['status' => 'push']);
                $this->session->increment('player_balance', $this->betAmount); // Refund bet
            } else {
                $hand->update(['status' => 'win']);
                $this->session->increment('player_balance', intval($this->betAmount * 2.5)); // 3:2 payout
            }
        }

        // Insurance and even money logic
        if($dealerHand[0]['value'] === 'A') {
            $hand->update(['insurance_offer' => true]);
            if ($this->calculateHandValue($playerHand) === 21) {
                $hand->update(['even_money_offer' => true]);
            }
        }

        $this->hands[] = $hand->toArray();
        $this->betAmount = 0;
    }

    public function split($handId)
    {
        $hand = BlackjackHand::find($handId);
        if ($hand && count($hand->player_hand) === 2 && $hand->player_hand[0]['value'] === $hand->player_hand[1]['value']) {
            $newHand = BlackjackHand::create([
                'session_id' => $this->session->id,
                'player_hand' => [array_pop($this->deck), $hand->player_hand[1]],
                'dealer_hand' => $hand->dealer_hand,
                'bet_amount' => $hand->bet_amount,
                'status' => 'playing'
            ]);
            
            $hand->update(['player_hand' => [$hand->player_hand[0], array_pop($this->deck)]]);
            $this->hands->push($newHand);
        }
    }

    public function doubleDown($handId)
    {
        $hand = BlackjackHand::find($handId);
        if ($hand && $hand->status === 'playing' && count($hand->player_hand) === 2) {
            $hand->bet_amount *= 2;
            $playerHand = $hand->player_hand;
            $playerHand[] = array_pop($this->deck);
            $hand->update(['player_hand' => $playerHand, 'status' => 'stand']);
        }
    }

    public function hit($handId) 
    {
        $hand = BlackjackHand::find($handId);
        if ($hand && $hand->status === 'playing') {
            $playerHand = $hand->player_hand;
            $playerHand[] = array_pop($this->deck);
            $hand->update(['player_hand' => $playerHand]);
        }
    }

    public function stand($handId) {
        $hand = BlackjackHand::find($handId);
        if ($hand && $hand->status === 'playing') {
            $hand->update(['status' => 'stand']);
            $this->dealerPlay($hand);
        }
    }

    public function dealerPlay($hand)
    {
        $dealerHand = $hand->dealer_hand;
        $dealerHand[1] = array_pop($this->deck); // Reveal hidden card

        // Correct Wild Card Handling
        $this->checkForWildCardDuringDeal($dealerHand[1]); // Mark for reshuffle if wild

        // Check if the new cards drawn are wild cards
        while ($this->calculateHandValue($dealerHand) < 17) {
            $nextCard = array_pop($this->deck);
            $this->checkForWildCardDuringDeal($nextCard); // Check each card for wild
            $dealerHand[] = $nextCard;
        }

        $hand->update(['dealer_hand' => $dealerHand]);
        $this->evaluateWinLoss($hand);

        // Reshuffle after play if wild card was encountered
        $this->reshuffleDeckIfNeeded(); 
    }

    public function offerInsurance($handId)
    {
        $hand = BlackjackHand::find($handId);
        if ($hand->insurance_offer && $this->session->player_balance >= $hand->bet_amount / 2) {
            $insuranceBet = $hand->bet_amount / 2;
            $this->session->decrement('player_balance', $insuranceBet);

            $dealerSecondCard = $hand->dealer_hand[1]['value'];
            if ($dealerSecondCard === '10' || $dealerSecondCard === 'J' || $dealerSecondCard === 'Q' || $dealerSecondCard === 'K') {
                $this->session->increment('player_balance', $insuranceBet * 3); // Insurance pays 2:1
            }
        }
    }

    public function takeEvenMoney($handId)
    {
        $hand = BlackjackHand::find($handId);
        if ($hand && $hand->even_money_offer) {
            $this->session->increment('player_balance', $hand->bet_amount * 2); // 1:1 payout
            $hand->update(['status' => 'win']);
        }
    }

    private function evaluateWinLoss($hand)
    {
        $playerValue = $this->calculateHandValue($hand->player_hand);
        $dealerValue = $this->calculateHandValue($hand->dealer_hand);

        if ($playerValue > 21) {
            $hand->update(['status' => 'loss']);
            $this->session->decrement('player_balance', $hand->bet_amount);
        } elseif ($dealerValue > 21 || $playerValue > $dealerValue) {
            $hand->update(['status' => 'win']);
            $this->session->increment('player_balance', $hand->bet_amount * 2);
        } elseif ($playerValue == $dealerValue) {
            $hand->update(['status' => 'push']);
            $this->session->increment('player_balance', $hand->bet_amount);
        } else {
            $hand->update(['status' => 'loss']);
            $this->session->decrement('player_balance', $hand->bet_amount);
        }

        // Reshuffle if the wild card is drawn during deal
        $this->reshuffleDeckIfNeeded();
    }

    private function calculateHandValue($hand)
    {
        $value = 0;
        $aces = 0;
        foreach ($hand as $card) {
            if (in_array($card['value'], ['J', 'Q', 'K'])) {
                $value += 10;
            } elseif ($card['value'] === 'A') {
                $value += 11;
                $aces++;
            } else {
                $value += $card['value'];
            }
        }
        while ($value > 21 && $aces) {
            $value -= 10;
            $aces--;
        }
        return $value;
    }

    public function render()
    {
        return view('livewire.blackjack-game', [
            'balance' => $this->session->player_balance
        ]);
    }
}
