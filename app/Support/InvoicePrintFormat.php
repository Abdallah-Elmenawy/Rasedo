<?php

namespace App\Support;

class InvoicePrintFormat
{
    public static function all(): array
    {
        return [
            'thermal-58' => [
                'label' => 'طباعة حرارية 58 مم',
                'hint' => 'إيصال ضيق للكاشير والمبيعات اليومية، من غير هوامش.',
                'paper' => '58 مم',
                'kind' => 'thermal',
                'size' => '58',
            ],
            'thermal-80' => [
                'label' => 'طباعة حرارية 80 مم',
                'hint' => 'إيصال الكاشير العريض، مناسب لفوري والمطاعم.',
                'paper' => '80 مم',
                'kind' => 'thermal',
                'size' => '80',
            ],
            'a5' => [
                'label' => 'فاتورة A5',
                'hint' => 'نصف صفحة لتفاصيل البيع والمشتريات.',
                'paper' => '148 × 210 مم',
                'kind' => 'sheet',
                'size' => 'a5',
            ],
            'a4' => [
                'label' => 'فاتورة A4',
                'hint' => 'صفحة كاملة للفواتير الرسمية والتفصيلية.',
                'paper' => '210 × 297 مم',
                'kind' => 'sheet',
                'size' => 'a4',
            ],
        ];
    }

    public static function key(?string $key): string
    {
        $key = (string) $key;

        return array_key_exists($key, self::all()) ? $key : 'a4';
    }

    public static function get(?string $key): array
    {
        return self::all()[self::key($key)];
    }
}
