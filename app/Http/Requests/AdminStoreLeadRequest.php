<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\PalestinianPhone;
use Illuminate\Foundation\Http\FormRequest;

final class AdminStoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:80', 'regex:/^[\p{L}\s\'\-\.]+$/u'],
            'phone' => ['required', 'string', 'max:20', new PalestinianPhone],
            'email' => ['nullable', 'string', 'email:rfc', 'max:255'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'message' => ['nullable', 'string', 'max:1000'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'الاسم مطلوب.',
            'name.min' => 'الاسم قصير جداً.',
            'name.max' => 'الاسم طويل جداً.',
            'name.regex' => 'الاسم يجب أن يحتوي على أحرف فقط.',
            'phone.required' => 'رقم الجوال مطلوب.',
            'email.email' => 'البريد الإلكتروني غير صالح.',
            'service_id.exists' => 'الخدمة غير موجودة.',
            'message.max' => 'التفاصيل أطول من المسموح.',
            'note.max' => 'الملاحظة أطول من المسموح.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $serviceId = $this->input('service_id');

        $this->merge([
            'name' => is_string($this->input('name')) ? trim($this->input('name')) : $this->input('name'),
            'phone' => is_string($this->input('phone')) ? trim($this->input('phone')) : $this->input('phone'),
            'email' => is_string($this->input('email')) ? mb_strtolower(trim($this->input('email'))) : $this->input('email'),
            'message' => is_string($this->input('message')) ? trim($this->input('message')) : $this->input('message'),
            'note' => is_string($this->input('note')) ? trim($this->input('note')) : $this->input('note'),
            'service_id' => ($serviceId === '' || $serviceId === null || $serviceId === '0')
                ? null
                : $serviceId,
        ]);
    }
}
