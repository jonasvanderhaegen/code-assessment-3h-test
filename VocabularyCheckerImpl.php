<?php

// Interface for checking the existence of words in a vocabulary
interface VocabularyChecker {
    // Method to check if a word exists in the vocabulary
    function exists(string $word): bool;
}

// Implementation of the VocabularyChecker interface
class VocabularyCheckerImpl implements VocabularyChecker {
    // Array to hold valid words
    private array $validWords = [];

    // Constructor to load words from a file into the validWords array
    public function __construct() {
        try {
            // Attempt to open the wordlist file
            $handle = fopen(__DIR__ . '/wordlist.txt', 'r', false);
            if ($handle !== false) {
                // Read each line from the file and add it to the validWords array
                while (($line = fgets($handle)) !== false) {
                    $this->validWords[] = trim($line); // Trim whitespace
                }
                fclose($handle); // Close the file handle
            } else {
                throw new Exception("Failed to open wordlist.txt"); // Handle file open error
            }
        } catch (Exception $e) {
            echo $e->getMessage(); // Output error message if an exception occurs
        }
    }

    // Method to check if a word exists in the validWords array
    public function exists(string $word): bool {
        return in_array($word, $this->validWords); // Return true if the word is found
    }
}

// Suggestions for Improvement:
// 1. Verifying that the file is readable and accessible before attempting to load it would improve error handling and provide more meaningful feedback to the user.

// 2. The `file()` function, it may consume excessive memory for large word lists. A more pragmatic approach might involve processing the file line by line or utilizing a database for storage.

// 3. For faster lookups, especially with larger datasets, consider transforming the word list into an associative array or using `array_flip()` during initialization.

// 4. Adding validation to ensure the word list contains only appropriate entries (e.g., valid alphabetic words) can prevent issues downstream.

// 5. Comprehensive tests should cover scenarios such as empty files, unreadable files, or files containing invalid entries, ensuring robustness in various edge cases.