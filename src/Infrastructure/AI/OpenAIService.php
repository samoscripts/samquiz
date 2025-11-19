<?php

namespace App\Infrastructure\AI;

use App\Domain\Quiz\Shared\Service\AIServiceInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenAIService implements AIServiceInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $apiKey,
        private string $model,
        private string $apiUrl,
    ) {}

    public function generateQuestion(string $text): array
    {
        $prompt = $this->buildPrompt($text);
        
        $response = $this->httpClient->request('POST', $this->apiUrl, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 500
            ]
        ]);
        $resp = $response->getContent();

        $data = $response->toArray();
        $content = $data['choices'][0]['message']['content'];
        
        return $this->parseResponse($content);
    }

    private function buildPrompt(string $text): string
    {

        $prompt = <<<EOT
        Na pytanie: $text
        potrzebuję 10 odpowiedzi do quizu familijada - wraz z punktami. Suma punktów ma wynosić
        Odpowiedz w formacie JSON:
            "text": <odpowiedź>, "points": <punkty>
        EOT;
        return $prompt;
    }

    private function parseResponse(string $content): array
    {
        // Usuń markdown formatting jeśli istnieje
        $content = preg_replace('/```json\s*/', '', $content);
        $content = preg_replace('/```\s*$/', '', $content);
        
        $data = json_decode(trim($content), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Nie udało się sparsować odpowiedzi AI: ' . json_last_error_msg());
        }
        
        return $data;
    }
}
