<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionListener implements EventSubscriberInterface
{

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
        }

        if ($exception instanceof \Symfony\Component\Security\Core\Exception\AccessDeniedException) {
            $statusCode = Response::HTTP_FORBIDDEN;
        }

        if ($exception instanceof \Symfony\Component\Security\Core\Exception\AuthenticationException) {
            $statusCode = Response::HTTP_UNAUTHORIZED;
        }

        $message = $exception->getMessage();
        if (empty($message)) {
            $message = Response::$statusTexts[$statusCode] ?? 'An error occurred';
        }

        $data = [
            'error' => $message,
            'status' => $statusCode,
        ];

        $previous = $exception->getPrevious();
        if ($previous instanceof \Symfony\Component\Validator\Exception\ValidationFailedException) {
            $violations = [];
            foreach ($previous->getViolations() as $violation) {
                $propertyPath = $violation->getPropertyPath();
                if (empty($propertyPath)) {
                    $propertyPath = 'general';
                }
                $violations[$propertyPath] = $violation->getMessage();
            }
            $data['violations'] = $violations;
            $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        $response = new JsonResponse($data, $statusCode);
        $event->setResponse($response);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', -128],
        ];
    }
}

