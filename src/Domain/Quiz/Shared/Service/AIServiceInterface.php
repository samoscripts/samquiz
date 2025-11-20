<?php

namespace App\Domain\Quiz\Shared\Service;

interface AIServiceInterface
{
    /**
     * Wysyła zapytanie do AI z gotowym promptem
     * 
     * @param string $prompt Gotowy prompt do wysłania do AI
     * @return array Odpowiedź z AI (sparsowana z JSON)
     */
    public function ask(string $prompt): array;
}
