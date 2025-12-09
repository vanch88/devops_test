<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @return User[]
     */
    public function findAllOrdered(): array
    {
        return $this->findBy([], ['id' => 'DESC']);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * @throws \RuntimeException
     */
    public function create(string $name, string $email, string $hashedPassword): User
    {
        $user = new User($name, $email, $hashedPassword);
        $this->getEntityManager()->persist($user);

        try {
            $this->getEntityManager()->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw new \RuntimeException('email must be unique', 409, $e);
        }

        return $user;
    }

    /**
     * @throws \RuntimeException
     */
    public function update(User $user, ?string $name, ?string $email): void
    {
        if ($name !== null) {
            $user->setName($name);
        }

        if ($email !== null) {
            $user->setEmail($email);
        }

        try {
            $this->getEntityManager()->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw new \RuntimeException('email must be unique', 409, $e);
        }
    }

    public function delete(User $user): bool
    {
        if (!$user) {
            return false;
        }

        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();

        return true;
    }
}

