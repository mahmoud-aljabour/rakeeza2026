<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Service;
use App\Rules\PalestinianPhone;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

final class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:80', 'regex:/^[\p{L}\s\'\-\.]+$/u'],
            'phone' => ['required', 'string', 'max:20', new PalestinianPhone],
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'service_ids' => ['required', 'array', 'min:1'],
            'service_ids.*' => ['required'],
            'message' => ['required', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('site.form.errors.name_required'),
            'name.min' => __('site.form.errors.name_length'),
            'name.max' => __('site.form.errors.name_length'),
            'name.regex' => __('site.form.errors.name_alpha'),
            'phone.required' => __('site.form.errors.phone'),
            'email.required' => __('site.form.errors.email_required'),
            'email.email' => __('site.form.errors.email'),
            'service_ids.required' => __('site.form.errors.service'),
            'service_ids.min' => __('site.form.errors.service'),
            'message.required' => __('site.form.errors.details_required'),
            'message.max' => __('site.form.errors.details_max'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $serviceIds = $this->input('service_ids', $this->input('service_id'));

        if (! is_array($serviceIds)) {
            $serviceIds = $serviceIds === null || $serviceIds === '' ? [] : [$serviceIds];
        }

        $normalized = [];
        foreach ($serviceIds as $item) {
            if ($item === 'general') {
                $normalized[] = 'general';
                continue;
            }

            if ($item === '' || $item === null) {
                continue;
            }

            if (is_numeric($item)) {
                $normalized[] = (int) $item;
            }
        }

        $this->merge([
            'name' => is_string($this->input('name')) ? trim($this->input('name')) : $this->input('name'),
            'phone' => is_string($this->input('phone')) ? trim($this->input('phone')) : $this->input('phone'),
            'email' => is_string($this->input('email')) ? mb_strtolower(trim($this->input('email'))) : $this->input('email'),
            'message' => is_string($this->input('message')) ? trim($this->input('message')) : $this->input('message'),
            'service_ids' => array_values(array_unique($normalized, SORT_REGULAR)),
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $ids = (array) $this->input('service_ids', []);

            if ($ids === []) {
                $validator->errors()->add('service_ids', __('site.form.errors.service'));

                return;
            }

            $numericIds = array_values(array_filter($ids, static fn (mixed $id): bool => is_int($id)));
            $hasGeneral = in_array('general', $ids, true);

            if (! $hasGeneral && $numericIds === []) {
                $validator->errors()->add('service_ids', __('site.form.errors.service'));

                return;
            }

            if ($numericIds !== []) {
                $existing = Service::query()->whereIn('id', $numericIds)->pluck('id')->all();
                if (count($existing) !== count($numericIds)) {
                    $validator->errors()->add('service_ids', __('site.form.errors.service'));
                }
            }
        });
    }

    /**
     * @return array{name: string, phone: string, email: string, service_id: int|null, service_ids: list<int>, message: string}
     */
    public function leadPayload(): array
    {
        $validated = $this->safe()->only(['name', 'phone', 'email', 'message', 'service_ids']);
        $rawIds = (array) ($validated['service_ids'] ?? []);
        $serviceIds = array_values(array_filter($rawIds, static fn (mixed $id): bool => is_int($id)));

        return [
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'message' => $validated['message'],
            'service_id' => $serviceIds[0] ?? null,
            'service_ids' => $serviceIds,
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $validator->errors()->first() ?: __('site.form.send_failed'),
            'errors' => $validator->errors(),
        ], 422));
    }
}
