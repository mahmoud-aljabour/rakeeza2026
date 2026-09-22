<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePrivacyPolicySectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
            'description_en' => ['nullable', 'string', 'max:10000'],
            'order_column' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'عنوان البند مطلوب.',
            'description.required' => 'وصف البند مطلوب.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->input('is_active'), FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        if ($this->has('order_column')) {
            $order = $this->input('order_column');
            $this->merge([
                'order_column' => ($order === '' || $order === null) ? 0 : (int) $order,
            ]);
        }

        foreach (['title', 'title_en', 'description', 'description_en'] as $field) {
            if (! $this->has($field)) {
                continue;
            }

            $value = trim((string) $this->input($field));
            $this->merge([
                $field => $value === '' ? null : $value,
            ]);
        }
    }
}
