<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class LoginDto
{
    #[Assert\NotBlank(message: 'Email is required')]
    #[Assert\Email(message: 'Email must be a valid email address')]
    public string $email;

    #[Assert\NotBlank(message: 'Password is required')]
    public string $password;
}

