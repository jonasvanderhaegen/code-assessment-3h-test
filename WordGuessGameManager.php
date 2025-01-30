<?php

namespace App;

require_once __DIR__ . '/MultiplayerGuessingGame.php';
require_once __DIR__ . '/VocabularyCheckerImpl.php';

use App\Exceptions\InvalidArgumentException;

class WordGuessGameManager implements \MultiplayerGuessingGame {
    private array $gameWords = [];
    private array $partiallyRevealedWords = [];
    private \VocabularyChecker $vocabularyChecker;
    private array $playerScores = [];

    public function __construct(array $words, \VocabularyChecker $vocabularyChecker) 
    {
        if (count($words) === 0 || count(array_unique(array_map('strlen', $words))) !== 1) {
            throw new InvalidArgumentException('All words must be of the same length and non-empty.', 'words');
        }
        
        $this->gameWords = $words;
        $this->vocabularyChecker = $vocabularyChecker;
        $this->initializeGameState();
    }

    private function initializeGameState(): void 
    {
        foreach ($this->gameWords as $word) {
            $revealedIndex = rand(0, strlen($word) - 1);
            $maskedWord = str_repeat('*', strlen($word));
            $this->partiallyRevealedWords[] = substr_replace($maskedWord, $word[$revealedIndex], $revealedIndex, 1);
        }
    }

    public function getGameStrings(): array 
    {
        return $this->partiallyRevealedWords;
    }

    public function submitGuess(string $playerName, string $submission): int 
    {
        if (!$this->vocabularyChecker->exists($submission)) {
            throw new InvalidArgumentException('Submission is not a valid English word.', 'submission');
        }

        if (strlen($submission) !== strlen($this->gameWords[0])) {
            throw new InvalidArgumentException('Submission must match the length of the game words.', 'submission');
        }

        if (!isset($this->playerScores[$playerName])) {
            $this->playerScores[$playerName] = 0;
        }

        $totalScore = 0;
        $isExactMatch = false;

        foreach ($this->partiallyRevealedWords as $index => $revealedWord) {
            $originalWord = $this->gameWords[$index];

            if ($revealedWord === $originalWord) {
                continue; // Skip fully revealed words
            }

            if ($submission === $originalWord) {
                $isExactMatch = true;
                $this->partiallyRevealedWords[$index] = $originalWord;
                $totalScore = 10; // Exact match overrides other scoring
                break;
            }

            if ($this->isValidGuess($revealedWord, $submission)) {
                $score = $this->revealCharacters($index, $submission);
                $totalScore += $score;
            }
        }

        $this->playerScores[$playerName] += $totalScore;

        return $isExactMatch ? $totalScore : $totalScore;
    }

    private function isValidGuess(string $revealedWord, string $submission): bool 
    {
        for ($i = 0; $i < strlen($revealedWord); $i++) {
            if ($revealedWord[$i] !== '*' && $revealedWord[$i] !== $submission[$i]) {
                return false;
            }
        }
        return true;
    }

    private function revealCharacters(int $index, string $submission): int 
    {
        $revealedWord = $this->partiallyRevealedWords[$index];
        $originalWord = $this->gameWords[$index];
        $newRevealedWord = $revealedWord;
        $revealCount = 0;
    
        for ($i = 0; $i < strlen($revealedWord); $i++) {
            if ($revealedWord[$i] === '*' && $submission[$i] === $originalWord[$i]) {
                $newRevealedWord[$i] = $originalWord[$i];
                $revealCount++;
            }
        }
    
        $this->partiallyRevealedWords[$index] = $newRevealedWord;
    
        return $revealCount;
    }
    

    public function isGameComplete(): bool 
    {
        foreach ($this->partiallyRevealedWords as $index => $revealedWord) {
            if ($revealedWord !== $this->gameWords[$index]) {
                return false;
            }
        }
        return true;
    }

    public function getPlayerScores(): array 
    {
        return $this->playerScores;
    }
}

?>
