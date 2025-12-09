<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class UpdatePostDto
{
    #[Assert\Length(min: 1, max: 200, minMessage: 'Title must be at least 1 character', maxMessage: 'Title must not exceed 200 characters')]
    public ?string $title = null;

    #[Assert\Length(min: 1, minMessage: 'Body must be at least 1 character')]
    public ?string $body = null;

    public function hasUpdates(): bool
    {
        return $this->title !== null || $this->body !== null;
    }
}

