<?php

declare(strict_types=1);

namespace App\Services;

use LakshanJS\PdoDb\PdoDb;
use App\Support\Crypto;

class Auth
{
    public function __construct(private PdoDb $db)
    {
    }

    public function attempt(string $username, string $password): bool
    {
        $user = $this->db
            ->where('username', $username)
            ->getOne('users', ['id', 'password']);
        if ($user && Crypto::verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            return true;
        }
        return false;
    }

    public function logout(): void
    {
        unset($_SESSION['user_id']);
    }

    public function check(): bool
    {
        return isset($_SESSION['user_id']);
    }
}
