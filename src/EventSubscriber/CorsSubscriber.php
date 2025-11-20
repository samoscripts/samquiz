<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CorsSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 9999], // Wysoki priorytet, aby przechwycić OPTIONS przed routingiem
            KernelEvents::RESPONSE => ['onKernelResponse', 9999],
        ];
    }

    /**
     * Obsługuje preflight request (OPTIONS) przed routingiem
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Sprawdzamy, czy to żądanie OPTIONS do API
        if ($request->getMethod() === 'OPTIONS' && str_starts_with($request->getPathInfo(), '/api')) {
            $response = new JsonResponse();
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
            $response->headers->set('Access-Control-Max-Age', '3600');
            $response->setStatusCode(200);
            $response->setContent('');

            $event->setResponse($response);
            $event->stopPropagation(); // Zatrzymaj propagację, żeby nie dotarło do kontrolera
        }
    }

    /**
     * Dodaje nagłówki CORS do wszystkich odpowiedzi API
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $request = $event->getRequest();

        // Sprawdzamy, czy to żądanie do API
        if (str_starts_with($request->getPathInfo(), '/api')) {
            // Ustawiamy nagłówki CORS
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
            $response->headers->set('Access-Control-Max-Age', '3600');
        }
    }
}

