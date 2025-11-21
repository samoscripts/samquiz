<?php

namespace App\Domain\Quiz\FamilyFeud\Service;

use App\Domain\Quiz\FamilyFeud\Entity\Question as DomainQuestion;
use App\Domain\Quiz\FamilyFeud\ValueObject\Answer;
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
    public function buildVerifyAnswerPrompt(string $answerPlayerText, DomainQuestion $question): string
    {
        $correctAnswers = $question->answers();
        $correctAnswers = array_map(fn(Answer $answer) => $answer->text(), $correctAnswers);
        $answersList = json_encode($correctAnswers, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        return <<<EOT
Gra familijada - pytanie: "{$question->text()}"
Odpowiedź użytkownika: "{$answerPlayerText}"
Lista poprawnych odpowiedzi:
$answersList
Sprawdź czy odpowiedź użytkownika pasuje do którejś z poprawnych. Pasować może tylko jedna odpowiedź.
Odpowiedz TYLKO w formacie JSON (bez dodatkowego tekstu, bez markdown):
{"found": true/false, "answer": "odpowiedź"}
Przykład: {"found": true, "answer": "Warszawa"}
EOT;
    }
}

