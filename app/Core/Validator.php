<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Minimal validation engine.
 *
 * Rules: required, nullable, email, min:n, max:n, numeric, integer, decimal,
 * confirmed, same:x, different:x, in:a,b,c, not_in:a,b,c, url, alpha_num,
 * alpha, string, bool, date, array, image, mimes:jpg,png, size_kb:n,
 * exists:table,column, unique:table,column,id.
 */
final class Validator
{
    private array $errors = [];

    public function __construct(private readonly array $data, private readonly array $rules)
    {
        foreach ($rules as $field => $ruleString) {
            $this->validateField($field, $ruleString);
        }
    }

    public static function make(array $data, array $rules): self
    {
        return new self($data, $rules);
    }

    public function passes(): bool
    {
        return $this->errors === [];
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function first(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    public function allErrors(): array
    {
        $out = [];
        foreach ($this->errors as $field => $messages) {
            foreach ($messages as $message) {
                $out[] = $message;
            }
        }
        return $out;
    }

    private function validateField(string $field, string $ruleString): void
    {
        $rules = array_map('trim', explode('|', $ruleString));
        $value = $this->data[$field] ?? null;
        $isPresent = array_key_exists($field, $this->data);

        foreach ($rules as $rule) {
            [$name, $params] = array_pad(explode(':', $rule, 2), 2, '');
            $params = $params === '' ? [] : explode(',', $params);
            $message = $this->apply($field, $name, $params, $value, $isPresent);
            if ($message !== null) {
                $this->errors[$field][] = $message;
            }
            if ($name === 'nullable' && ($value === null || $value === '')) {
                return;
            }
        }
    }

    private function apply(string $field, string $rule, array $params, mixed $value, bool $isPresent): ?string
    {
        $label = ucfirst(str_replace(['_', '.'], ' ', $field));

        return match ($rule) {
            'required' => $isPresent && $value !== null && $value !== '' ? null : "$label is required.",
            'nullable' => null,
            'email' => $value === null || $value === '' || filter_var($value, FILTER_VALIDATE_EMAIL) ? null : "$label must be a valid email address.",
            'min' => $this->checkLength($value, (int)($params[0] ?? 0), 'at least') ?: null,
            'max' => $this->checkLength($value, (int)($params[0] ?? 0), 'at most', true) ?: null,
            'numeric' => is_numeric($value) ? null : "$label must be a number.",
            'integer' => filter_var($value, FILTER_VALIDATE_INT) !== false ? null : "$label must be an integer.",
            'decimal' => is_numeric($value) ? null : "$label must be a decimal number.",
            'confirmed' => ($value === ($this->data[$field . '_confirmation'] ?? null)) ? null : "$label confirmation does not match.",
            'same' => $value === ($this->data[$params[0] ?? ''] ?? null) ? null : "$label must match {$params[0]}.",
            'different' => $value !== ($this->data[$params[0] ?? ''] ?? null) ? null : "$label must differ from {$params[0]}.",
            'in' => in_array($value, $params, true) ? null : "$label must be one of: " . implode(', ', $params) . '.',
            'not_in' => !in_array($value, $params, true) ? null : "$label must not be one of: " . implode(', ', $params) . '.',
            'url' => $value === null || $value === '' || filter_var($value, FILTER_VALIDATE_URL) ? null : "$label must be a valid URL.",
            'alpha' => $value === null || $value === '' || ctype_alpha((string)$value) ? null : "$label must contain only letters.",
            'alpha_num' => $value === null || $value === '' || ctype_alnum((string)$value) ? null : "$label must contain only letters and numbers.",
            'string' => $value === null || is_string($value) ? null : "$label must be a string.",
            'bool' => is_bool($value) || in_array($value, [0, 1, '0', '1', 'true', 'false'], true) ? null : "$label must be true or false.",
            'date' => $value === null || $value === '' || strtotime((string)$value) !== false ? null : "$label must be a valid date.",
            'array' => is_array($value) ? null : "$label must be an array.",
            'image' => $value === null || is_array($value) ? null : "$label must be an image.",
            'mimes' => $this->checkMime($value, $params),
            'size_kb' => $this->checkSize($value, (int)($params[0] ?? 0)),
            'exists' => $this->checkExists($params, $value),
            'unique' => $this->checkUnique($params, $value),
            default => null,
        };
    }

    private function checkLength(mixed $value, int $limit, string $word, bool $isMax = false): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        $length = is_array($value) ? count($value) : mb_strlen((string)$value);
        if (($isMax && $length > $limit) || (!$isMax && $length < $limit)) {
            return "This field must be $word $limit " . ($isArray ?? 'characters') . '.';
        }
        return null;
    }

    private function checkMime(mixed $value, array $params): ?string
    {
        if ($value === null || !is_array($value)) {
            return null;
        }
        if (($value['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        $ext = strtolower(pathinfo((string)($value['name'] ?? ''), PATHINFO_EXTENSION));
        return in_array($ext, $params, true) ? null : 'File must be of type: ' . implode(', ', $params) . '.';
    }

    private function checkSize(mixed $value, int $maxKb): ?string
    {
        if ($value === null || !is_array($value)) {
            return null;
        }
        $kb = (int)($value['size'] ?? 0) / 1024;
        return $kb <= $maxKb ? null : "File must be at most {$maxKb} KB.";
    }

    private function checkExists(array $params, mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (count($params) < 2) {
            return null;
        }
        $table = $params[0];
        $column = $params[1];
        $db = Database::connect();
        $found = $db->scalar(
            sprintf('SELECT COUNT(*) FROM `%s` WHERE `%s` = ?', $table, $column),
            [$value]
        );
        return $found > 0 ? null : 'The selected value is invalid.';
    }

    private function checkUnique(array $params, mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (count($params) < 2) {
            return null;
        }
        $table = $params[0];
        $column = $params[1];
        $ignoreId = $params[2] ?? null;
        $db = Database::connect();
        $sql = sprintf('SELECT COUNT(*) FROM `%s` WHERE `%s` = ?', $table, $column);
        $bind = [$value];
        if ($ignoreId !== null) {
            $sql .= ' AND `id` != ?';
            $bind[] = $ignoreId;
        }
        $found = $db->scalar($sql, $bind);
        return $found > 0 ? 'This value is already taken.' : null;
    }
}
