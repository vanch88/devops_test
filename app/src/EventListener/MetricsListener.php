<?php

namespace App\EventListener;

use App\Service\MetricsService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class MetricsListener implements EventSubscriberInterface
{
    private array $startTimes = [];

    public function __construct(
        private readonly MetricsService $metricsService
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $this->startTimes[$request->getUri()] = microtime(true);
    }

    public function onKernelTerminate(TerminateEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();
        
        if ($request->getPathInfo() === '/metrics') {
            return;
        }

        $startTime = $this->startTimes[$request->getUri()] ?? microtime(true);
        $duration = microtime(true) - $startTime;

        $method = $request->getMethod();
        $route = $request->getPathInfo();
        $statusCode = $response->getStatusCode();

        $this->metricsService->recordHttpRequest($method, $route, $statusCode, $duration);

        if ($statusCode === 422) {
            $this->metricsService->recordValidationError($route);
        }

        unset($this->startTimes[$request->getUri()]);
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        $exception = $event->getThrowable();
        
        if ($request->getPathInfo() === '/metrics') {
            return;
        }

        $startTime = $this->startTimes[$request->getUri()] ?? microtime(true);
        $duration = microtime(true) - $startTime;

        $method = $request->getMethod();
        $route = $request->getPathInfo();
        
        $statusCode = 500;
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
        }

        $this->metricsService->recordHttpRequest($method, $route, $statusCode, $duration);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 1024],
            KernelEvents::TERMINATE => ['onKernelTerminate'],
            KernelEvents::EXCEPTION => ['onKernelException', -128],
        ];
    }
}

