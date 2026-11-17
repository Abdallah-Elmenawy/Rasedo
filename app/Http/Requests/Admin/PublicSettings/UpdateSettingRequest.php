<?php

namespace App\Http\Requests\Admin\PublicSettings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'legal_name' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'tax_number' => ['nullable', 'string', 'max:50'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'show_brand_name' => ['nullable', 'boolean'],
            'show_brand_logo' => ['nullable', 'boolean'],
            'brand_logo_side' => ['required', 'in:left,right'],
            'currency' => ['required', 'string', 'max:10'],
            'currency_symbol' => ['required', 'string', 'max:10'],
            'language' => ['required', 'in:ar,en'],
            'print_format' => ['required', 'in:thermal-58,thermal-80,a5,a4'],
            'receipt_sales_title' => ['required', 'string', 'max:40'],
            'receipt_order_in_store' => ['required', 'string', 'max:40'],
            'receipt_order_pickup' => ['required', 'string', 'max:40'],
            'receipt_order_delivery' => ['required', 'string', 'max:40'],
            'sale_prefix' => ['required', 'string', 'max:10'],
            'purchase_prefix' => ['required', 'string', 'max:10'],
            'payment_prefix' => ['required', 'string', 'max:10'],
            'receipt_prefix' => ['required', 'string', 'max:10'],
            'journal_prefix' => ['required', 'string', 'max:10'],
            'default_tax_rate' => ['nullable', 'numeric', 'min:0'],
            'sales_account_id' => ['nullable', 'exists:accounts,id'],
            'purchases_account_id' => ['nullable', 'exists:accounts,id'],
            'inventory_account_id' => ['nullable', 'exists:accounts,id'],
            'cogs_account_id' => ['nullable', 'exists:accounts,id'],
            'customers_account_id' => ['nullable', 'exists:accounts,id'],
            'suppliers_account_id' => ['nullable', 'exists:accounts,id'],
            'tax_account_id' => ['nullable', 'exists:accounts,id'],
            'sales_discount_account_id' => ['nullable', 'exists:accounts,id'],
        ];
    }
}
