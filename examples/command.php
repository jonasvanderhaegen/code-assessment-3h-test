<?php

require_once __DIR__ . '/../vendor/autoload.php';

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

use App\WordGuessGameManager;
use App\Exceptions\InvalidArgumentException;
require_once __DIR__ . '/../VocabularyCheckerImpl.php';

// ray()->newScreen();

// Initialize the game
$wordList = ['apple', 'grape', 'melon'];
$vocabularyChecker = new \VocabularyCheckerImpl('wordlist.txt');
$game = new WordGuessGameManager($wordList, $vocabularyChecker);

// ray($game);


// Output initial game state
echo "Initial Game State:\n";
print_r($game->getGameStrings());

// Simulate Player 1 making a guess
$playerName = 'Player1';
$guess = 'ample';
try {
    $score = $game->submitGuess($playerName, $guess);
    echo "\n$playerName submits '$guess':\n";
    echo "Score: $score\n";
    print_r($game->getGameStrings());
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Simulate Player 2 making a guess
$playerName = 'Player2';
$guess = 'grape';
try {
    $score = $game->submitGuess($playerName, $guess);
    echo "\n$playerName submits '$guess':\n";
    echo "Score: $score\n";
    print_r($game->getGameStrings());
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Simulate Player 3 making an invalid guess
$playerName = 'Player3';
$guess = 'wrong';
try {
    $score = $game->submitGuess($playerName, $guess);
    echo "\n$playerName submits '$guess':\n";
    echo "Score: $score\n";
    print_r($game->getGameStrings());
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Check game completion
if ($game->isGameComplete()) {
    echo "\nThe game is complete!\n";
} else {
    echo "\nThe game is not yet complete.\n";
}

// Output final player scores
echo "\nFinal Player Scores:\n";
print_r($game->getPlayerScores());

?>
