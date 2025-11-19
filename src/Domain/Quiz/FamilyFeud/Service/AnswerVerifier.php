<?php

namespace App\Domain\Quiz\FamilyFeud\Service;

class AnswerVerifier
{

    public function __construct(
        private int $levLimit = 2,
        private int $similarLimit = 75
    ) {}

    /**
     * Normalizuje tekst (usuwanie polskich znaków, małe litery)
     */
    private function normalizeText(string $text): string
    {
        return mb_strtolower(
            $this->removePolishChars($text)
        );
    }

    /**
     * Usuwa polskie znaki diakrytyczne
     */
    private function removePolishChars(string $text): string
    {
        $replacements = [
            'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n',
            'ó' => 'o', 'ś' => 's', 'ź' => 'z', 'ż' => 'z',
            'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'E', 'Ł' => 'L', 'Ń' => 'N',
            'Ó' => 'O', 'Ś' => 'S', 'Ź' => 'Z', 'Ż' => 'Z'
        ];

        return strtr($text, $replacements);
    }

    /**
     * Sprawdza, czy odpowiedź użytkownika pasuje do odpowiedzi z listy
     * 
     * @param string $userAnswer Odpowiedź wpisana przez użytkownika
     * @param string $correctAnswer Poprawna odpowiedź z listy
     * @return bool
     */
    private function checkAnswer(string $userAnswer, string $correctAnswer): bool
    {
        $userAnswer = trim($this->normalizeText($userAnswer));
        $correctAnswer = trim($this->normalizeText($correctAnswer));

        if (empty($userAnswer) || empty($correctAnswer)) {
            return false;
        }

        // Dokładne dopasowanie
        if ($userAnswer === $correctAnswer) {
            return true;
        }

        // 2. Levenshtein (literówki)
        if (levenshtein($correctAnswer, $userAnswer) <= $this->levLimit) {
            return true;
        }

        // 3. Podobieństwo procentowe
        similar_text($correctAnswer, $userAnswer, $percent);
        if ($percent >= $this->similarLimit) {
            return true;
        }

        return false;
    }

    /**
     * Znajduje wszystkie pasujące odpowiedzi w liście
     * 
     * @param string $userAnswer Odpowiedź wpisana przez użytkownika
     * @param array $answers Lista odpowiedzi w formacie [['text' => string, 'points' => int], ...]
     * @return array Indeksy pasujących odpowiedzi
     */
    public function findMatchingAnswers(string $userAnswer, array $answers): array
    {
        $matchingIndices = [];

        foreach ($answers as $index => $answer) {
            if (!isset($answer['text'])) {
                continue;
            }

            if ($this->checkAnswer($userAnswer, $answer['text'])) {
                $matchingIndices[] = $index;
            }
        }

        return $matchingIndices;
    }
}

