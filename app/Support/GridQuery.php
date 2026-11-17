<?php

namespace App\Support;

use App\Models\AllUsers\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class GridQuery
{
    public static function apply(Builder $query, array $fields): Builder
    {
        foreach ($fields as $name => $field) {
            $value = request($name);
            if ($value === null || $value === '') {
                continue;
            }

            $column = $field['column'];
            match ($field['type']) {
                'text' => $query->where($column, 'like', '%'.$value.'%'),
                'select' => $query->where($column, $value),
                'number' => $query->where($column, $value),
                'date' => $query->whereDate($column, $value),
                default => null,
            };
        }

        return $query;
    }

    public static function applyCollection(Collection $rows, array $fields): Collection
    {
        foreach ($fields as $name => $field) {
            $value = request($name);
            if ($value === null || $value === '') {
                continue;
            }

            $rows = $rows->filter(function ($row) use ($field, $value) {
                $current = data_get($row, $field['key']);

                return match ($field['type']) {
                    'text' => mb_stripos((string) $current, (string) $value) !== false,
                    'number' => abs((float) $current - (float) $value) < 0.0001,
                    'bool' => (int) (bool) $current === (int) $value,
                    'date' => str_starts_with((string) ($current instanceof \DateTimeInterface ? $current->format('Y-m-d') : $current), (string) $value),
                    default => (string) $current === (string) $value,
                };
            })->values();
        }

        return $rows;
    }

    public static function distinct(Builder $query, string $column, ?callable $label = null): array
    {
        return (clone $query)
            ->reorder()
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->mapWithKeys(function ($value) use ($label) {
                $key = (string) $value;
                $text = $label ? $label($value) : $key;

                return [$key => $text];
            })
            ->all();
    }

    public static function labeled(Builder $query, string $column, array $labels): array
    {
        return self::distinct($query, $column, fn ($value) => $labels[(string) $value] ?? (string) $value);
    }

    public static function users(Builder $query, string $column = 'created_by'): array
    {
        $ids = (clone $query)->reorder()->whereNotNull($column)->distinct()->pluck($column);

        return User::query()->whereIn('id', $ids)->orderBy('name')->pluck('name', 'id')->all();
    }

    public static function related(Builder $query, string $foreignKey, string $model, string $label = 'name'): array
    {
        $ids = (clone $query)->reorder()->whereNotNull($foreignKey)->distinct()->pluck($foreignKey);

        return $model::query()->whereIn('id', $ids)->orderBy($label)->pluck($label, 'id')->all();
    }
}
