    <div>
        <div class="flex justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold">Player Balance: ${{ $session->player_balance }}</h1>
            </div>
            <div>
                <button wire:click="dealCards" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">
                    Deal Cards
                </button>
            </div>
        </div>

        <div class="mb-4">
            <h2 class="text-xl mb-2">Select Bet:</h2>
            <div class="flex space-x-2">
                <button wire:click="addBet(5)" class="bg-yellow-500 px-4 py-2 rounded">5</button>
                <button wire:click="addBet(25)" class="bg-yellow-500 px-4 py-2 rounded">25</button>
                <button wire:click="addBet(100)" class="bg-yellow-500 px-4 py-2 rounded">100</button>
                <button wire:click="addBet(500)" class="bg-yellow-500 px-4 py-2 rounded">500</button>
                <button wire:click="addBet(1000)" class="bg-yellow-500 px-4 py-2 rounded">1000</button>
            </div>
            <div class="mt-3">
                <button wire:click="clearBet" class="bg-red-500 px-4 py-2 rounded">Clear Bet</button>
            </div>
        </div>

        <div class="mb-4 text-lg">
            Bet Amount: <span class="font-bold text-yellow-400">${{ $betAmount }}</span>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <!-- Player's Hand -->
            <div>
                <h2 class="text-xl font-bold mb-2">Player's Hand</h2>
                <div class="flex space-x-2">
                    @foreach($hands as $hand)
                        @foreach($hand['player_hand'] as $card)
                            <div class="card">
                                <span>{{ $card['value'] }} of {{ ucfirst($card['suit']) }}</span>
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>

            <!-- Dealer's Hand -->
            <div>
                <h2 class="text-xl font-bold mb-2">Dealer's Hand</h2>
                <div class="flex space-x-2">
                    @foreach($hands as $hand)
                        @foreach($hand['dealer_hand']as $card)
                            <div class="card">
                                <span>
                                    @if($card['value'] === 'hidden')
                                        Hidden
                                    @else
                                        {{ $card['value'] }} of {{ ucfirst($card['suit']) }}
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex space-x-4 mt-6">
            <button wire:click="hit({{ $hands[0]->id ?? '' }})" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                Hit
            </button>

            <button wire:click="stand({{ $hands[0]->id ?? '' }})" class="bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600">
                Stand
            </button>

            <button wire:click="doubleDown({{ $hands[0]->id ?? '' }})" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">
                Double Down
            </button>

            <button wire:click="split({{ $hands[0]->id ?? '' }})" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">
                Split
            </button>
        </div>
    </div>