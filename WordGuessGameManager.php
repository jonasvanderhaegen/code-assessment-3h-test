<?php

namespace App;

require_once __DIR__ . '/MultiplayerGuessingGame.php';
require_once __DIR__ . '/VocabularyCheckerImpl.php';

use App\Exceptions\InvalidArgumentException;

class WordGuessGameManager implements \MultiplayerGuessingGame {
    // Array to hold the game words
    private array $gameWords = [];
    // Array to hold partially revealed words for display
    private array $partiallyRevealedWords = [];
    // Vocabulary checker instance to validate submissions
    private \VocabularyChecker $vocabularyChecker;
    // Array to track player scores
    private array $playerScores = [];

    // Constructor to initialize the game with words and a vocabulary checker
    public function __construct(array $words, \VocabularyChecker $vocabularyChecker) 
    {
        // Validate that all words are of the same length and non-empty
        if (count($words) === 0 || count(array_unique(array_map('strlen', $words))) !== 1) {
            throw new InvalidArgumentException('All words must be of the same length and non-empty.', 'words');
        }
        
        // Assign the words and vocabulary checker to the class properties
        $this->gameWords = $words;
        $this->vocabularyChecker = $vocabularyChecker;
        // Initialize the game state
        $this->initializeGameState();
    }

    // Method to initialize the game state by masking the words
    private function initializeGameState(): void 
    {
        foreach ($this->gameWords as $word) {
            // Randomly select an index to reveal in the masked word
            $revealedIndex = rand(0, strlen($word) - 1);
            // Create a masked version of the word
            $maskedWord = str_repeat('*', strlen($word));
            // Replace the masked character with the revealed character
            $this->partiallyRevealedWords[] = substr_replace($maskedWord, $word[$revealedIndex], $revealedIndex, 1);
        }
    }

    // Method to get the current state of the game strings
    public function getGameStrings(): array 
    {
        return $this->partiallyRevealedWords;
    }

    // Method to submit a guess and update scores
    public function submitGuess(string $playerName, string $submission): int 
    {
        // Validate the submission against the vocabulary checker
        if (!$this->vocabularyChecker->exists($submission)) {
            throw new InvalidArgumentException('Submission is not a valid English word.', 'submission');
        }

        // Ensure the submission matches the length of the game words
        if (strlen($submission) !== strlen($this->gameWords[0])) {
            throw new InvalidArgumentException('Submission must match the length of the game words.', 'submission');
        }

        // Initialize player score if not already set
        if (!isset($this->playerScores[$playerName])) {
            $this->playerScores[$playerName] = 0;
        }

        $totalScore = 0; // Total score for the current submission
        $isExactMatch = false; // Flag to check if the guess is an exact match

        // Iterate through the partially revealed words to check the guess
        foreach ($this->partiallyRevealedWords as $index => $revealedWord) {
            $originalWord = $this->gameWords[$index];

            // Skip fully revealed words
            if ($revealedWord === $originalWord) {
                continue; // Skip fully revealed words
            }

            // Check for an exact match
            if ($submission === $originalWord) {
                $isExactMatch = true;
                $this->partiallyRevealedWords[$index] = $originalWord; // Reveal the word
                $totalScore = 10; // Exact match gives a score of 10
                break; // Exit loop on exact match
            }

            // Check if the guess is valid and reveal characters if so
            if ($this->isValidGuess($revealedWord, $submission)) {
                $score = $this->revealCharacters($index, $submission);
                $totalScore += $score; // Add score from revealed characters
            }
        }

        // Update the player's score
        $this->playerScores[$playerName] += $totalScore;

        return $isExactMatch ? $totalScore : $totalScore; // Return the total score
    }

    // Method to validate if the guess is valid based on revealed characters
    private function isValidGuess(string $revealedWord, string $submission): bool 
    {
        for ($i = 0; $i < strlen($revealedWord); $i++) {
            // Check if the revealed character matches the submission
            if ($revealedWord[$i] !== '*' && $revealedWord[$i] !== $submission[$i]) {
                return false; // Invalid guess
            }
        }
        return true; // Valid guess
    }

    // Method to reveal characters in the word based on the submission
    private function revealCharacters(int $index, string $submission): int 
    {
        $revealedWord = $this->partiallyRevealedWords[$index];
        $originalWord = $this->gameWords[$index];
        $newRevealedWord = $revealedWord;
        $revealCount = 0; // Count of revealed characters
    
        for ($i = 0; $i < strlen($revealedWord); $i++) {
            // Reveal characters that match the submission
            if ($revealedWord[$i] === '*' && $submission[$i] === $originalWord[$i]) {
                $newRevealedWord[$i] = $originalWord[$i];
                $revealCount++; // Increment reveal count
            }
        }
    
        $this->partiallyRevealedWords[$index] = $newRevealedWord; // Update the revealed word
    
        return $revealCount; // Return the count of revealed characters
    }
    
    // Method to check if the game is complete
    public function isGameComplete(): bool 
    {
        foreach ($this->partiallyRevealedWords as $index => $revealedWord) {
            // Check if any word is still partially revealed
            if ($revealedWord !== $this->gameWords[$index]) {
                return false; // Game is not complete
            }
        }
        return true; // All words are revealed
    }

    // Method to get the current scores of players
    public function getPlayerScores(): array 
    {
        return $this->playerScores; // Return player scores
    }
}

?>
