<?php

// Interface for a multiplayer guessing game
interface MultiplayerGuessingGame {
    // Method to get the current game strings (partially revealed words)
    function getGameStrings(): array;

    // Method to submit a guess from a player
    function submitGuess(string $playerName, string $submission);
}