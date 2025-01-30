<?php

interface VocabularyChecker {
    function exists(string $word): bool;
}

class VocabularyCheckerImpl implements VocabularyChecker {
    private array $validWords = [];

    public function __construct() {
        try {
            $handle = fopen(__DIR__ . '/wordlist.txt', 'r', false);
            if ($handle !== false) {
                while (($line = fgets($handle)) !== false) {
                    $this->validWords[] = trim($line);
                }
                fclose($handle);
            } else {
                throw new Exception("Failed to open wordlist.txt");
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function exists(string $word): bool {
        return in_array($word, $this->validWords);
    }
}

// Suggestions for Improvement:
// 1. Verifying that the file is readable and accessible before attempting to load it would improve error handling and provide more meaningful feedback to the user.

// 2. The `file()` function, it may consume excessive memory for large word lists. A more pragmatic approach might involve processing the file line by line or utilizing a database for storage.

// 3. For faster lookups, especially with larger datasets, consider transforming the word list into an associative array or using `array_flip()` during initialization.

// 4. Adding validation to ensure the word list contains only appropriate entries (e.g., valid alphabetic words) can prevent issues downstream.

// 5. Comprehensive tests should cover scenarios such as empty files, unreadable files, or files containing invalid entries, ensuring robustness in various edge cases.