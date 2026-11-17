<?php

namespace App\Http\Requests\Admin\Purchases;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            'remove_attachment' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'attachment' => 'المرفق',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->hasFile('attachment') && ! $this->boolean('remove_attachment')) {
                $validator->errors()->add('attachment', 'اختر ملفاً أو حدّد حذف المرفق.');
            }
        });
    }
}
