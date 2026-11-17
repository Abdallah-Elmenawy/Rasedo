<?php

namespace App\Http\Requests\Admin\Treasury;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'treasury_id' => ['required', 'exists:treasuries,id'],
            'customer_id' => ['required', 'exists:customers,id'],
            'invoice_id' => ['nullable', 'exists:sales_invoices,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transacted_at' => ['required', 'date'],
            'method' => ['required', Rule::in(PaymentMethod::values())],
            'notes' => ['nullable', 'string'],
        ];
    }
}
