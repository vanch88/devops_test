<?php

namespace App\Controller;

use App\Dto\CreatePostDto;
use App\Dto\UpdatePostDto;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/posts')]
class PostController extends AbstractController
{
    public function __construct(
        private readonly PostRepository $postRepository,
        private readonly SerializerInterface $serializer
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $postsList = $this->postRepository->findAllOrdered();
        $data = $this->serializer->serialize($postsList, 'json', ['groups' => 'post:read']);

        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(Post $post): JsonResponse
    {
        $data = $this->serializer->serialize($post, 'json', ['groups' => 'post:read']);
        return new JsonResponse($data, 200, [], true);
    }

    #[Route('', methods: ['POST'])]
    public function store(
        #[MapRequestPayload] CreatePostDto $dto
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        $post = $this->postRepository->create($user, $dto->title, $dto->body);
        $data = $this->serializer->serialize($post, 'json', ['groups' => 'post:write']);

        return new JsonResponse($data, 201, [], true);
    }

    #[Route('/{id}', methods: ['PUT', 'PATCH'])]
    public function update(
        Post $post,
        #[MapRequestPayload] UpdatePostDto $dto
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        if ($post->getUser()->getId() !== $user->getId()) {
            return $this->json(['error' => 'You can only update your own posts'], 403);
        }

        if (!$dto->hasUpdates()) {
            return $this->json(['error' => 'nothing to update'], 400);
        }

        $this->postRepository->update($post, $dto->title, $dto->body, null);
        $data = $this->serializer->serialize($post, 'json', ['groups' => 'post:write']);

        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function destroy(Post $post): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($post->getUser()->getId() !== $user->getId()) {
            return $this->json(['error' => 'You can only delete your own posts'], 403);
        }

        $deleted = $this->postRepository->delete($post);

        return $this->json(['deleted' => $deleted]);
    }

}

