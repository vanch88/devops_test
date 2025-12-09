<?php

namespace App\ArgumentResolver;

use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EntityValueResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly PostRepository $postRepository
    ) {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $type = $argument->getType();

        if ($type !== User::class && $type !== Post::class) {
            return [];
        }

        $id = $request->attributes->get('id');
        if ($id === null) {
            return [];
        }

        $entity = match ($type) {
            User::class => $this->userRepository->find($id),
            Post::class => $this->postRepository->find($id),
            default => null,
        };

        if ($entity === null) {
            throw new NotFoundHttpException(sprintf('%s with id "%s" not found', $type, $id));
        }

        return [$entity];
    }
}

