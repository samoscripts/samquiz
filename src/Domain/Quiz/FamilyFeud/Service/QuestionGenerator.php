<?php
namespace App\Domain\Quiz\FamilyFeud\Service;

use App\Domain\Quiz\FamilyFeud\Entity\Question;
use App\Domain\Quiz\FamilyFeud\ValueObject\Answer;
use App\Domain\Quiz\Shared\Service\AIServiceInterface;

class QuestionGenerator
{
    public function __construct(
        private AIServiceInterface $aiService
    ) {}

    public function generate(string $questionText): Question
    {
        try {
            // Używamy AI service do generowania pytania
            $aiResponse = $this->aiService->generateQuestion($questionText);
            
            // Parsujemy odpowiedź z AI
            $answers = array_map(
                fn($answerData) => new Answer($answerData['text'], $answerData['points']),
                $aiResponse
            );
            
            return new Question($questionText, $answers);
            
        } catch (\Exception $e) {
            throw new \Exception('Failed to generate question: ' . $e->getMessage());
            // Fallback - zwracamy proste pytanie gdy AI nie działa
            // return $this->createFallbackQuestion($text);
        }
    }
    
    private function createFallbackQuestion(string $text): Question
    {
        $fallbackAnswers = [
            new Answer('Odpowiedź A', 10),
            new Answer('Odpowiedź B', 8),
            new Answer('Odpowiedź C', 5),
            new Answer('Odpowiedź D', 2),
        ];
        
        return new Question($text, $fallbackAnswers);
    }
}
