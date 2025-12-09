<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateUserDto
{
    #[Assert\Length(min: 1, max: 100, minMessage: 'Name must be at least 1 character', maxMessage: 'Name must not exceed 100 characters')]
    public ?string $name = null;

    #[Assert\Email(message: 'Email must be a valid email address')]
    #[Assert\Length(max: 150, maxMessage: 'Email must not exceed 150 characters')]
    public ?string $email = null;

    public function hasUpdates(): bool
    {
        return $this->name !== null || $this->email !== null;
    }
}

