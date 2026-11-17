<?php

namespace App\Http\Requests\Admin\Sales;

use App\Support\ReceiptText;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CheckoutPosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'payment_status' => ['required', 'in:paid,unpaid'],
            'payment_method' => ['nullable', 'in:cash,visa,mada,wallet,other'],
            'submit_action' => ['nullable', 'in:pay,issue,draft'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'treasury_id' => ['nullable', 'exists:treasuries,id'],
            'order_type' => ['required', 'in:'.implode(',', ReceiptText::orderTypeKeys())],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'numeric', 'min:0.001'],
            'items.*.serials' => ['nullable', 'array'],
            'items.*.serials.*' => ['string', 'max:80'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            back()->with('error', $validator->errors()->first())->withInput()
        );
    }
}
