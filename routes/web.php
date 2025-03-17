<?php

use App\Livewire\BlackjackGame;
use Illuminate\Support\Facades\Route;


Route::get('/', BlackjackGame::class)->name('blackjack');

