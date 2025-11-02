<?php

declare(strict_types=1);

function sanitize(array $data): array
{
    $clean = [];
    foreach ($data as $key => $value) {
        $clean[$key] = is_string($value) ? trim($value) : $value;
    }
    return $clean;
}

function validate_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validate_required(array $data, array $fields): array
{
    $errors = [];
    foreach ($fields as $field => $label) {
        if (empty($data[$field])) {
            $errors[$field] = $label . ' è obbligatorio';
        }
    }
    return $errors;
}

function validate_string_length(string $value, int $min, int $max): bool
{
    $length = mb_strlen($value);
    return $length >= $min && $length <= $max;
}
