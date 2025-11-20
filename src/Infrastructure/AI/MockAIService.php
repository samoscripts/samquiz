<?php

namespace App\Infrastructure\AI;

use App\Domain\Quiz\Shared\Service\AIServiceInterface;

class MockAIService implements AIServiceInterface
{
    /**
     * Mock implementacja - zwraca zamockowane dane gdy AI nie jest dostępne
     */
    public function ask(string $prompt): array
    {
        // Fallback - zwraca zamockowane odpowiedzi
        return [
            ['text' => 'Odpowiedź A', 'points' => 25],
            ['text' => 'Odpowiedź B', 'points' => 20],
            ['text' => 'Odpowiedź C', 'points' => 15],
            ['text' => 'Odpowiedź D', 'points' => 12],
            ['text' => 'Odpowiedź E', 'points' => 10],
            ['text' => 'Odpowiedź F', 'points' => 8],
            ['text' => 'Odpowiedź G', 'points' => 5],
            ['text' => 'Odpowiedź H', 'points' => 3],
            ['text' => 'Odpowiedź I', 'points' => 2],
        ];
    }
}
