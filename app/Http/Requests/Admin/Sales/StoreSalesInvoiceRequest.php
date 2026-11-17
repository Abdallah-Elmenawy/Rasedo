<?php

namespace App\Http\Requests\Admin\Sales;

use App\Support\ReceiptText;
use Illuminate\Foundation\Http\FormRequest;

class StoreSalesInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'order_type' => ['nullable', 'in:'.implode(',', ReceiptText::orderTypeKeys())],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax' => ['nullable', 'numeric', 'min:0'],
            'items.*.serials' => ['nullable', 'array'],
            'items.*.serials.*.code' => ['required', 'string', 'max:80'],
            'items.*.serials.*.kind' => ['nullable', 'in:piece,box'],
            'confirm' => ['nullable'],
            'payment_status' => ['nullable', 'in:paid,unpaid'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'treasury_id' => ['nullable', 'exists:treasuries,id'],
        ];
    }
}
