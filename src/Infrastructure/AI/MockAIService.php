<?php

namespace App\Infrastructure\AI;

use App\Domain\Quiz\Shared\Service\AIServiceInterface;

class MockAIService implements AIServiceInterface
{
    public function generateQuestion(string $text): array
    {
        // Fallback - zwraca zamockowane dane gdy AI nie jest dostępne
        return [
            'question' => $text,
            'answers' => [
                ['text' => 'Odpowiedź A', 'points' => 10],
                ['text' => 'Odpowiedź B', 'points' => 8],
                ['text' => 'Odpowiedź C', 'points' => 5],
                ['text' => 'Odpowiedź D', 'points' => 2],
            ]
        ];
    }
}
