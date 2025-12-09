<?php

namespace App\EventListener;

use App\Service\JwtService;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class JwtAuthListener
{
    public function __construct(
        private readonly JwtService $jwtService,
        private readonly UserProviderInterface $userProvider,
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (str_starts_with($request->getPathInfo(), '/api/auth')) {
            return;
        }

        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $authHeader = $request->headers->get('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return;
        }

        $token = substr($authHeader, 7);
        $payload = $this->jwtService->validateToken($token);

        if (!$payload || !isset($payload['sub'])) {
            return;
        }

        try {
            $user = $this->userProvider->loadUserByIdentifier($payload['email']);
            $token = new UsernamePasswordToken($user, 'api', $user->getRoles());
            $this->tokenStorage->setToken($token);
        } catch (\Exception $e) {
        }
    }
}

