<?php
namespace App\Domain\Quiz\FamilyFeud\Service;

use App\Domain\Quiz\FamilyFeud\Entity\Question;
use App\Domain\Quiz\FamilyFeud\ValueObject\Answer;
use App\Domain\Quiz\Shared\Service\AIServiceInterface;

class QuestionGenerator
{
    public function __construct(
        private AIServiceInterface $aiService,
        private PromptBuilder $promptBuilder
    ) {}

    public function generate(string $questionText): Question
    {
        try {
            // Budujemy prompt używając PromptBuilder
            $prompt = $this->promptBuilder->buildGenerateAnswersPrompt($questionText);
            
            // Wysyłamy do AI
            $aiResponse = $this->aiService->ask($prompt);
            
            // Parsujemy odpowiedź z AI
            $answers = array_map(
                fn($answerData) => new Answer($answerData['text'], $answerData['points']),
                $aiResponse
            );
            
            return new Question($questionText, $answers);
            
        } catch (\Exception $e) {
            throw new \Exception('Failed to generate question: ' . $e->getMessage());
        }
    }
}
