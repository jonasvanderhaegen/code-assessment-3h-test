<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

use App\WordGuessGameManager;
use App\Exceptions\InvalidArgumentException;

require_once __DIR__ . '/../VocabularyCheckerImpl.php';

class MultiplayerGuessingGameTest extends TestCase {

    private WordGuessGameManager $game;
    private \VocabularyCheckerImpl $vocabularyChecker;

    public function testInitialization() 
    {
        $vocabularyChecker = new \VocabularyCheckerImpl('wordlist.txt');
        $game = new WordGuessGameManager(['apple', 'grape', 'melon'], $vocabularyChecker);
        $this->assertIsArray($game->getGameStrings());
    }

    protected function setUp(): void 
    {
        $words = ["apple", "grape", "melon"];
        $this->vocabularyChecker = $this->createMock(\VocabularyCheckerImpl::class);
        $this->vocabularyChecker->method('exists')->willReturnCallback(function ($word) {
            return in_array($word, ["apple", "grape", "melon", "ample", "maple"]);
        });

        $this->game = new WordGuessGameManager($words, $this->vocabularyChecker);
    }

    public function testGameInitialization(): void 
    {
        $gameStrings = $this->game->getGameStrings();
        $this->assertCount(3, $gameStrings);

        foreach ($gameStrings as $string) {
            $this->assertMatchesRegularExpression('/^\**[^\*]\**$/', $string, "Each word should have one revealed character.");
        }
    }

    public function testSubmitValidGuess(): void 
    {
        $score = $this->game->submitGuess("Player1", "apple");
        $this->assertGreaterThan(0, $score, "Valid guesses should score points.");

        $gameStrings = $this->game->getGameStrings();
        $this->assertNotEquals(["*****", "*****", "*****"], $gameStrings, "Characters should be revealed after a valid guess.");
    }

    public function testSubmitExactMatch(): void 
    {
        $score = $this->game->submitGuess("Player1", "apple");
        $this->assertEquals(10, $score, "Exact matches should score 10 points.");

        $gameStrings = $this->game->getGameStrings();
        $this->assertEquals("apple", $gameStrings[0], "Exact match should fully reveal the word.");
    }

    public function testInvalidWordSubmission(): void 
    {
        $this->expectException(InvalidArgumentException::class);
        $this->game->submitGuess("Player1", "wrong");
    }

    public function testSubmitGuessWithMismatchedLength(): void 
    {
        $this->expectException(InvalidArgumentException::class);
        $this->game->submitGuess("Player1", "short");
    }

    public function testPlayerScores(): void 
    {
        $this->game->submitGuess("Player1", "apple");
        $this->game->submitGuess("Player2", "grape");

        $scores = $this->game->getPlayerScores();

        $this->assertArrayHasKey("Player1", $scores);
        $this->assertArrayHasKey("Player2", $scores);
        $this->assertGreaterThan(0, $scores["Player1"], "Player1 should have a positive score.");
        $this->assertEquals(10, $scores["Player2"], "Exact match by Player2 should score 10 points.");
    }

    public function testGameCompletion(): void 
    {
        $this->game->submitGuess("Player1", "apple");
        $this->game->submitGuess("Player2", "grape");
        $this->game->submitGuess("Player3", "melon");

        $this->assertTrue($this->game->isGameComplete(), "Game should be complete when all words are revealed.");
    }

    public function testNoScoreForDuplicateSubmission(): void 
    {
        $this->game->submitGuess("Player1", "ample");
        $initialScore = $this->game->getPlayerScores()["Player1"];

        $this->game->submitGuess("Player1", "ample");
        $finalScore = $this->game->getPlayerScores()["Player1"];

        $this->assertEquals($initialScore, $finalScore, "Duplicate submissions should not increase the score.");
    }

    public function testRevealPartialCharacters(): void 
    {
        $this->game->submitGuess("Player1", "ample");
        $gameStrings = $this->game->getGameStrings();
    
        $this->assertStringContainsString("a", $gameStrings[0], "The letter 'a' should be revealed in the word.");
        $this->assertStringContainsString("p", $gameStrings[0], "The letter 'p' should be revealed in the word.");
        $this->assertStringContainsString("e", $gameStrings[0], "The letter 'e' should be revealed in the word.");
    }

    public function testInvalidGuessAfterCompletion(): void {
        $this->game->submitGuess("Player1", "apple");
        $this->game->submitGuess("Player2", "grape");
        $this->game->submitGuess("Player3", "melon");

        $this->expectException(InvalidArgumentException::class);
        $this->game->submitGuess("Player1", "wrong");
    }
}
