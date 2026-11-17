<?php

namespace App\Models\PublicSettings;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CompanySetting extends Model
{
    protected $fillable = [
        'name', 'legal_name', 'email', 'phone', 'address', 'tax_number', 'logo', 'avatar',
        'show_brand_name', 'show_brand_logo', 'brand_logo_side',
        'currency', 'currency_symbol', 'language', 'print_format',
        'receipt_sales_title', 'receipt_order_in_store', 'receipt_order_pickup', 'receipt_order_delivery',
        'sale_prefix', 'purchase_prefix', 'payment_prefix', 'receipt_prefix', 'journal_prefix',
        'sale_next_number', 'purchase_next_number', 'payment_next_number', 'receipt_next_number', 'journal_next_number',
        'default_tax_rate', 'tax_enabled', 'discount_enabled',
        'sales_account_id', 'purchases_account_id', 'inventory_account_id', 'cogs_account_id',
        'customers_account_id', 'suppliers_account_id', 'tax_account_id', 'sales_discount_account_id',
    ];

    protected function casts(): array
    {
        return [
            'default_tax_rate' => 'decimal:2',
            'tax_enabled' => 'boolean',
            'discount_enabled' => 'boolean',
            'show_brand_name' => 'boolean',
            'show_brand_logo' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return Cache::remember('company_settings', 60, function () {
            return static::query()->first() ?? static::query()->create(['name' => 'Rasedo']);
        });
    }

    public function nextNumber(string $type): string
    {
        $map = [
            'sale' => ['sale_prefix', 'sale_next_number'],
            'purchase' => ['purchase_prefix', 'purchase_next_number'],
            'payment' => ['payment_prefix', 'payment_next_number'],
            'receipt' => ['receipt_prefix', 'receipt_next_number'],
            'journal' => ['journal_prefix', 'journal_next_number'],
        ];
        [$prefixField, $numberField] = $map[$type];
        $number = $this->{$numberField};
        $this->increment($numberField);
        Cache::forget('company_settings');

        return sprintf('%s-%04d', $this->{$prefixField}, $number);
    }
}
