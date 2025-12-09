<?php

namespace App\Controller;

use App\Dto\UpdateUserDto;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/users')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly SerializerInterface $serializer
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $usersList = $this->userRepository->findAllOrdered();
        $data = $this->serializer->serialize($usersList, 'json', ['groups' => 'user:read']);

        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $data = $this->serializer->serialize($user, 'json', ['groups' => 'user:read']);
        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(User $user): JsonResponse
    {
        $data = $this->serializer->serialize($user, 'json', ['groups' => 'user:read']);
        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/me', methods: ['PUT', 'PATCH'])]
    public function updateMe(
        #[MapRequestPayload] UpdateUserDto $dto
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        if (!$dto->hasUpdates()) {
            return $this->json(['error' => 'nothing to update'], 400);
        }

        try {
            $this->userRepository->update($user, $dto->name, $dto->email);
            $data = $this->serializer->serialize($user, 'json', ['groups' => 'user:write']);
            return new JsonResponse($data, 200, [], true);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getCode() ?: 409);
        }
    }

}

