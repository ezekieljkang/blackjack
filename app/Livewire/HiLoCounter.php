<?php

namespace App\Livewire;

use Livewire\Component;

class HiLoCounter extends Component
{
    public $runningCount = 0;
    public $trueCount = 0;
    public $showCount = false;
    public $decksRemaining = 2;

    public function toggleCount() {
        $this->showCount = !$this->showCount;
    }

    public function updateCount($cardValue) {
        if(in_array($cardValue, [2, 3, 4, 5, 6])) {
            $this->runningCount++;
        } elseif(in_array($cardValue, [10, 'J', 'Q', 'K', 'A'])) {
            $this->runningCount--;
        }

        $this->trueCount = $this->runningCount / max($this->decksRemaining, 1);
    }
    public function render()
    {
        return view('livewire.hi-lo-counter');
    }
}
