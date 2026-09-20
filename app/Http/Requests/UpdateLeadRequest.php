<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\LeadStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateLeadRequest extends FormRequest
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
            'status' => ['required', Rule::enum(LeadStatus::class)],
            'completed_price' => ['required_if:status,completed', 'nullable', 'numeric', 'min:0.01', 'max:9999999999.99'],
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
