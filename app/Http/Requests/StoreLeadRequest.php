<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'message' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $serviceId = $this->input('service_id');

        if ($serviceId === 'general' || $serviceId === '' || $serviceId === null) {
            $this->merge(['service_id' => null]);
        }
    }
}
