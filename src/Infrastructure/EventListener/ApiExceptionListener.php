<?php

namespace App\Infrastructure\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;

class ApiExceptionListener implements EventSubscriberInterface
{
    private bool $isDebug;

    public function __construct(KernelInterface $kernel)
    {
        $this->isDebug = $kernel->isDebug();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 10],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        $exception = $event->getThrowable();

        // Debug: logowanie dla testów
        error_log(sprintf(
            'ApiExceptionListener: Path=%s | AJAX=%s | API=%s | Exception=%s | Headers: X-Requested-With=%s, Accept=%s',
            $request->getPathInfo(),
            $this->isAjaxRequest($request) ? 'YES' : 'NO',
            $this->isApiRequest($request) ? 'YES' : 'NO',
            get_class($exception),
            $request->headers->get('X-Requested-With', 'NONE'),
            $request->headers->get('Accept', 'NONE')
        ));

        // Dla żądań do API zawsze zwracamy JSON, niezależnie od nagłówków
        // Dla innych żądań sprawdzamy czy są AJAX
        $isApi = $this->isApiRequest($request);
        $isAjax = $this->isAjaxRequest($request);
        
        if (!$isApi && !$isAjax) {
            error_log('ApiExceptionListener: Ignorowanie - nie API i nie AJAX');
            return; // Pozwalamy Symfony obsłużyć błąd normalnie (HTML)
        }

        error_log('ApiExceptionListener: Przetwarzanie błędu jako JSON');
        
        // Zatrzymujemy propagację, aby domyślny handler nie obsłużył tego
        $event->stopPropagation();

        // Określamy kod statusu
        $statusCode = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : JsonResponse::HTTP_INTERNAL_SERVER_ERROR;

        // Określamy komunikat błędu
        $message = $exception->getMessage();
        
        // W trybie produkcyjnym nie pokazujemy szczegółów wyjątków
        if (!$this->isDebug && $statusCode >= 500) {
            $message = 'Wystąpił błąd serwera. Proszę spróbować ponownie później.';
        }

        // Tworzymy odpowiedź JSON
        $response = new JsonResponse(
            [
                'error' => $message
            ],
            $statusCode
        );

        $response->headers->set('Content-Type', 'application/json');
        $event->setResponse($response);
    }

    /**
     * Sprawdza czy żądanie jest AJAX
     */
    private function isAjaxRequest(Request $request): bool
    {
        // Sprawdzamy różne sposoby wykrywania żądań AJAX
        if ($request->isXmlHttpRequest()) {
            return true;
        }

        $xRequestedWith = $request->headers->get('X-Requested-With');
        if ($xRequestedWith === 'XMLHttpRequest') {
            return true;
        }

        $accept = $request->headers->get('Accept', '');
        if (str_contains($accept, 'application/json')) {
            return true;
        }

        // Sprawdzamy też Content-Type dla POST/PUT
        $contentType = $request->headers->get('Content-Type', '');
        if (str_contains($contentType, 'application/json')) {
            return true;
        }

        return false;
    }

    /**
     * Sprawdza czy żądanie jest do API
     */
    private function isApiRequest(Request $request): bool
    {
        $pathInfo = $request->getPathInfo();
        return str_starts_with($pathInfo, '/api');
    }
}
