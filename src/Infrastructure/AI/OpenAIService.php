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

    /**
     * Uniwersalna metoda do wysyłania promptów do OpenAI
     */
    public function ask(string $prompt): array
    {
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
                'max_tokens' => 1000
            ]
        ]);

        $data = $response->toArray();
        $content = $data['choices'][0]['message']['content'];
        
        return $this->parseResponse($content);
    }

    /**
     * Parsuje odpowiedź z AI (usuwa markdown, dekoduje JSON)
     */
    private function parseResponse(string $content): array
    {
        // Usuń markdown formatting jeśli istnieje
        $content = preg_replace('/```json\s*/', '', $content);
        $content = preg_replace('/```\s*$/', '', $content);
        $content = trim($content);
        
        $data = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Nie udało się sparsować odpowiedzi AI: ' . json_last_error_msg() . '. Odpowiedź: ' . substr($content, 0, 200));
        }
        
        return $data;
    }
}
