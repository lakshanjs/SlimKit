<?php
declare(strict_types=1);

namespace App\Support;

use Respect\Validation\Exceptions\ValidationException;

class Validation
{
    public static function validate(array $data, array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $rule) {
            try {
                $rule->setName(ucfirst($field))->assert($data[$field] ?? null);
            } catch (ValidationException $e) {
                $errors[$field] = $e->getMessages();
            }
        }
        return $errors;
    }
}
