<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\CraftsmanStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateCraftsmanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(CraftsmanStatus::class)],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('note')) {
            $this->merge([
                'note' => trim((string) $this->input('note')),
            ]);
        }
    }
}
