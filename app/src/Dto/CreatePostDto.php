<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreatePostDto
{
    #[Assert\NotBlank(message: 'Title is required')]
    #[Assert\Length(min: 1, max: 200, minMessage: 'Title must be at least 1 character', maxMessage: 'Title must not exceed 200 characters')]
    public string $title;

    #[Assert\NotBlank(message: 'Body is required')]
    #[Assert\Length(min: 1, minMessage: 'Body must be at least 1 character')]
    public string $body;
}

