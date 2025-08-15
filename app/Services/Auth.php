<?php
declare(strict_types=1);

namespace App\Services;

use PDO;
use App\Support\Crypto;

class Auth
{
    public function __construct(private PDO $pdo)
    {
    }

    public function attempt(string $username, string $password): bool
    {
        $stmt = $this->pdo->prepare('SELECT id, password FROM users WHERE username = :username');
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
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
