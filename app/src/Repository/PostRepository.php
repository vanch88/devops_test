<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * @return Post[]
     */
    public function findAllOrdered(): array
    {
        return $this->findBy([], ['id' => 'DESC']);
    }

    public function create(User $user, string $title, string $body): Post
    {
        $post = new Post($user, $title, $body);
        $this->getEntityManager()->persist($post);
        $this->getEntityManager()->flush();

        return $post;
    }

    public function update(Post $post, ?string $title, ?string $body, ?User $user = null): void
    {
        if ($title !== null) {
            $post->setTitle($title);
        }

        if ($body !== null) {
            $post->setBody($body);
        }

        if ($user !== null) {
            $post->setUser($user);
        }

        $this->getEntityManager()->flush();
    }

    public function delete(Post $post): bool
    {
        if (!$post) {
            return false;
        }

        $this->getEntityManager()->remove($post);
        $this->getEntityManager()->flush();

        return true;
    }
}

