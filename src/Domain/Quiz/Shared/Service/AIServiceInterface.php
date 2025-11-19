<?php

namespace App\Domain\Quiz\Shared\Service;

interface AIServiceInterface
{
    /**
     * Generuje pytanie z odpowiedziami na podstawie tekstu
     * 
     * @param string $text Tekst pytania
     * @return array Struktura: ['question' => string, 'answers' => [['text' => string, 'points' => int]]]
     */
    public function generateQuestion(string $text): array;
}
