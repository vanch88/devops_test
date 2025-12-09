<?php

namespace App\Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    private string $secret;
    private int $expiration;

    public function __construct()
    {
        $this->secret = $_ENV['JWT_SECRET'] ?? 'your-secret-key-change-in-production';
        $this->expiration = (int)($_ENV['JWT_EXPIRATION'] ?? 3600);
    }

    public function generateToken(int $userId, string $email): string
    {
        $issuedAt = time();
        $expire = $issuedAt + $this->expiration;

        $payload = [
            'iat' => $issuedAt,
            'exp' => $expire,
            'sub' => $userId,
            'email' => $email,
        ];

        return JWT::encode($payload, $this->secret, 'HS256');
    }

    public function validateToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, 'HS256'));
            return (array) $decoded;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getUserIdFromToken(string $token): ?int
    {
        $payload = $this->validateToken($token);
        return $payload['sub'] ?? null;
    }
}

