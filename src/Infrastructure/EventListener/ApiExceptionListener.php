<?php
// src/EventListener/ApiExceptionListener.php
namespace App\Infrastructure\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ApiExceptionListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        // Możesz tu dodać warunek np. tylko dla /api/*
        $message = $exception->getMessage();

        $statusCode = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : JsonResponse::HTTP_INTERNAL_SERVER_ERROR;

        $response = new JsonResponse(
            [
                'error' => $message,
                'status' => $statusCode
            ], 
            $statusCode
        );
        $response->headers->set('Content-Type', 'application/json');

        $event->setResponse($response);
    }
}
