<?php

namespace App\Http\Requests\Admin\Purchases;

use Illuminate\Foundation\Http\FormRequest;

class PayPurchaseInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'treasury_id' => ['required', 'exists:treasuries,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transacted_at' => ['required', 'date'],
        ];
    }
}
