<?php

namespace App\Domain\Quiz\FamilyFeud\Service;

class PromptBuilder
{
    /**
     * Buduje prompt do generowania odpowiedzi dla pytania
     */
    public function buildGenerateAnswersPrompt(string $questionText): string
    {
        return <<<EOT
Na pytanie: $questionText
potrzebuję 10 odpowiedzi do quizu familijada - wraz z punktami. Suma punktów ma wynosić 100.
Odpowiedz TYLKO w formacie JSON (bez dodatkowego tekstu, bez markdown):
[
    {"text": "odpowiedź", "points": 10},
    {"text": "odpowiedź", "points": 8}
]
EOT;
    }

    /**
     * Buduje prompt do weryfikacji odpowiedzi użytkownika
     */
    public function buildVerifyAnswerPrompt(string $userAnswer, array $correctAnswers): string
    {
        $answersList = json_encode($correctAnswers, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
        return <<<EOT
Użytkownik wpisał: "$userAnswer"

Lista poprawnych odpowiedzi:
$answersList

Czy odpowiedź użytkownika pasuje do którejś z poprawnych? 
Odpowiedz TYLKO w formacie JSON (bez dodatkowego tekstu, bez markdown):
{"matches": [indeksy pasujących odpowiedzi], "found": true/false}

Przykład: {"matches": [0, 2], "found": true}
EOT;
    }
}

