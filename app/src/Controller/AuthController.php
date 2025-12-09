<?php

namespace App\Controller;

use App\Dto\LoginDto;
use App\Dto\RegisterDto;
use App\Repository\UserRepository;
use App\Service\JwtService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/auth')]
class AuthController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly JwtService $jwtService,
        private readonly SerializerInterface $serializer
    ) {
    }

    #[Route('/register', methods: ['POST'])]
    public function register(
        #[MapRequestPayload] RegisterDto $dto
    ): JsonResponse {
        $tempUser = new \App\Entity\User('', '', '');
        $hashedPassword = $this->passwordHasher->hashPassword($tempUser, $dto->password);

        try {
            $user = $this->userRepository->create($dto->name, $dto->email, $hashedPassword);
            $token = $this->jwtService->generateToken($user->getId(), $user->getEmail());

            $data = $this->serializer->serialize($user, 'json', ['groups' => 'user:read']);

            return new JsonResponse([
                'user' => json_decode($data, true),
                'token' => $token,
            ], 201);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getCode() ?: 409);
        }
    }

    #[Route('/login', methods: ['POST'])]
    public function login(
        #[MapRequestPayload] LoginDto $dto
    ): JsonResponse {
        $user = $this->userRepository->findByEmail($dto->email);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $dto->password)) {
            return $this->json(['error' => 'Invalid credentials'], 401);
        }

        $token = $this->jwtService->generateToken($user->getId(), $user->getEmail());
        $data = $this->serializer->serialize($user, 'json', ['groups' => 'user:read']);

        return $this->json([
            'user' => json_decode($data, true),
            'token' => $token,
        ]);
    }
}

