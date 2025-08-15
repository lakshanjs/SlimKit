<?php
declare(strict_types=1);

namespace App\Support;

class Crypto
{
    public static function hash(string $value): string
    {
        return password_hash($value, PASSWORD_DEFAULT);
    }

    public static function verify(string $value, string $hash): bool
    {
        return password_verify($value, $hash);
    }
}
