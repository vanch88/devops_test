<?php

namespace App\Validator\Constraint;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class EntityExists extends Constraint
{
    public string $message = 'Entity with id "{{ value }}" does not exist.';
    public string $entityClass;
    public ?string $repositoryMethod = null;

    public function __construct(
        string $entityClass,
        ?string $repositoryMethod = null,
        ?string $message = null,
        ?array $groups = null,
        mixed $payload = null
    ) {
        parent::__construct([], $groups, $payload);

        $this->entityClass = $entityClass;
        $this->repositoryMethod = $repositoryMethod ?? 'find';
        $this->message = $message ?? $this->message;
    }

    public function validatedBy(): string
    {
        return EntityExistsValidator::class;
    }
}

