<?php

namespace App\Support;

class ReceiptText
{
    public static function salesTitle(): string
    {
        $title = trim((string) optional(company())->receipt_sales_title);

        return $title !== '' ? $title : 'المبيعات';
    }

    public static function orderTypeKeys(): array
    {
        return ['in_store', 'pickup', 'delivery'];
    }

    public static function orderTypes(): array
    {
        $setting = company();

        return [
            'in_store' => self::label(optional($setting)->receipt_order_in_store, 'من المحل'),
            'pickup' => self::label(optional($setting)->receipt_order_pickup, 'استلام'),
            'delivery' => self::label(optional($setting)->receipt_order_delivery, 'توصيل'),
        ];
    }

    public static function orderLabel(?string $key): ?string
    {
        $key = self::normalizeOrderType($key);
        if ($key === null) {
            return null;
        }

        return self::orderTypes()[$key] ?? null;
    }

    public static function normalizeOrderType(?string $key): ?string
    {
        if ($key === null || $key === '') {
            return null;
        }

        return match ($key) {
            'dine_in' => 'in_store',
            'takeaway' => 'pickup',
            default => $key,
        };
    }

    private static function label(?string $value, string $fallback): string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : $fallback;
    }
}
