<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Base active-record style model. All domain models extend this.
 *
 * Features: attribute access, casting, fillable guard, timestamps,
 * find / all / where / create / update / delete / paginate.
 */
abstract class BaseModel
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $casts = [];
    protected bool $timestamps = true;
    protected array $attributes = [];
    protected bool $exists = false;

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    public static function db(): Database
    {
        return Database::connect();
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    public function exists(): bool
    {
        return $this->exists;
    }

    public function fill(array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
        return $this;
    }

    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function getAttribute(string $key): mixed
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->cast($key, $this->attributes[$key]);
        }
        return null;
    }

    public function __get(string $name): mixed
    {
        return $this->getAttribute($name);
    }

    public function __set(string $name, mixed $value): void
    {
        $this->setAttribute($name, $value);
    }

    public function __isset(string $name): bool
    {
        return array_key_exists($name, $this->attributes);
    }

    public function attributes(): array
    {
        return $this->attributes;
    }

    public function toArray(): array
    {
        $out = [];
        foreach (array_keys($this->attributes) as $key) {
            $out[$key] = $this->getAttribute($key);
        }
        return $out;
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }

    public function __toString(): string
    {
        return $this->toJson();
    }

    protected function cast(string $key, mixed $value): mixed
    {
        $type = $this->casts[$key] ?? null;
        if ($value === null || $type === null) {
            return $value;
        }
        return match ($type) {
            'int', 'integer' => (int)$value,
            'float', 'double' => (float)$value,
            'bool', 'boolean' => (bool)$value,
            'array' => json_decode((string)$value, true),
            'json' => json_decode((string)$value, true),
            default => $value,
        };
    }

    // ------------------------------------------------------------------
    // Query API
    // ------------------------------------------------------------------

    public static function find(int|string $id): ?static
    {
        $model = new static();
        $row = static::db()->first(
            sprintf('SELECT * FROM `%s` WHERE `%s` = ?', $model->table, $model->primaryKey),
            [$id]
        );
        return $row ? $model->newFromRow($row) : null;
    }

    /**
     * @return static[]
     */
    public static function all(): array
    {
        $model = new static();
        $rows = static::db()->query('SELECT * FROM `' . $model->table . '`');
        return array_map(fn (array $row) => $model->newFromRow($row), $rows);
    }

    /**
     * @return static[]
     */
    public static function where(string $column, mixed $operator = '=', mixed $value = null, ?int $limit = null, ?int $offset = null): array
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        $model = new static();
        $sql = sprintf('SELECT * FROM `%s` WHERE `%s` %s ?', $model->table, $column, $operator);
        if ($limit !== null) {
            $sql .= ' LIMIT ' . (int)$limit;
        }
        if ($offset !== null) {
            $sql .= ' OFFSET ' . (int)$offset;
        }
        $rows = static::db()->query($sql, [$value]);
        return array_map(fn (array $row) => $model->newFromRow($row), $rows);
    }

    public static function whereIn(string $column, array $values): array
    {
        if ($values === []) {
            return [];
        }
        $model = new static();
        $placeholders = implode(',', array_fill(0, count($values), '?'));
        $rows = static::db()->query(
            sprintf('SELECT * FROM `%s` WHERE `%s` IN (%s)', $model->table, $column, $placeholders),
            array_values($values)
        );
        return array_map(fn (array $row) => $model->newFromRow($row), $rows);
    }

    public static function firstWhere(string $column, mixed $value): ?static
    {
        $model = new static();
        $row = static::db()->first(
            sprintf('SELECT * FROM `%s` WHERE `%s` = ?', $model->table, $column),
            [$value]
        );
        return $row ? $model->newFromRow($row) : null;
    }

    public static function create(array $attributes): static
    {
        $model = new static();
        $model->fillableOnly($attributes);
        $model->save();
        return $model;
    }

    public function update(array $attributes): bool
    {
        $this->fillableOnly($attributes);
        return $this->save();
    }

    public function save(): bool
    {
        $data = $this->attributes;
        if ($this->timestamps) {
            $now = date('Y-m-d H:i:s');
            if (!$this->exists) {
                $data['created_at'] = $data['created_at'] ?? $now;
                $data['updated_at'] = $data['updated_at'] ?? $now;
            } else {
                $data['updated_at'] = $now;
                unset($data['created_at']);
            }
        }

        if ($this->exists) {
            unset($data[$this->primaryKey]);
            if ($data === []) {
                return true;
            }
            $set = implode(', ', array_map(fn ($k) => "`$k` = ?", array_keys($data)));
            $params = array_values($data);
            $params[] = $this->attributes[$this->primaryKey];
            static::db()->execute(
                sprintf('UPDATE `%s` SET %s WHERE `%s` = ?', $this->table, $set, $this->primaryKey),
                $params
            );
            return true;
        }

        $columns = implode(', ', array_map(fn ($k) => "`$k`", array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        static::db()->execute(
            sprintf('INSERT INTO `%s` (%s) VALUES (%s)', $this->table, $columns, $placeholders),
            array_values($data)
        );
        $this->attributes[$this->primaryKey] = static::db()->lastInsertId();
        $this->exists = true;
        return true;
    }

    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }
        static::db()->execute(
            sprintf('DELETE FROM `%s` WHERE `%s` = ?', $this->table, $this->primaryKey),
            [$this->attributes[$this->primaryKey]]
        );
        $this->exists = false;
        return true;
    }

    public static function count(): int
    {
        $model = new static();
        return (int)static::db()->scalar(sprintf('SELECT COUNT(*) FROM `%s`', $model->table));
    }

    /**
     * @return array{items: static[], total: int, page: int, per_page: int, last_page: int}
     */
    public static function paginate(int $perPage = 15, int $page = 1): array
    {
        $model = new static();
        $total = (int)static::db()->scalar(sprintf('SELECT COUNT(*) FROM `%s`', $model->table));
        $lastPage = (int)max(1, ceil($total / $perPage));
        $page = min(max(1, $page), $lastPage);
        $offset = ($page - 1) * $perPage;
        $rows = static::db()->query(
            sprintf('SELECT * FROM `%s` ORDER BY `%s` DESC LIMIT %d OFFSET %d', $model->table, $model->primaryKey, $perPage, $offset)
        );
        return [
            'items' => array_map(fn (array $row) => $model->newFromRow($row), $rows),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => $lastPage,
        ];
    }

    protected function newFromRow(array $row): static
    {
        $model = new static();
        $model->fill($row);
        $model->exists = true;
        return $model;
    }

    private function fillableOnly(array $attributes): void
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, $this->fillable, true)) {
                $this->attributes[$key] = $value;
            }
        }
    }
}
