<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreCraftsmanRequest extends FormRequest
{
    /**
     * @var list<string>
     */
    public const CITIES = ['رفح', 'خانيونس', 'وسطى', 'غزة'];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:80'],
            'father_name' => ['required', 'string', 'max:80'],
            'grandfather_name' => ['required', 'string', 'max:80'],
            'family_name' => ['required', 'string', 'max:80'],
            'phone' => ['required', 'string', 'max:30'],
            'city' => ['required', 'string', Rule::in(self::CITIES)],
            'specialties' => ['required', 'array', 'min:1'],
            'specialties.*' => ['required', 'string', 'max:255'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:60'],
            'has_tools' => ['sometimes', 'boolean'],
            'bio' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array{name: string, phone: string, city: string, specialty: list<string>, experience_years: int, has_tools?: bool, bio?: string|null}
     */
    public function craftsmanPayload(): array
    {
        $validated = $this->validated();

        return [
            'name' => trim(implode(' ', [
                $validated['first_name'],
                $validated['father_name'],
                $validated['grandfather_name'],
                $validated['family_name'],
            ])),
            'phone' => $validated['phone'],
            'city' => $validated['city'],
            'specialty' => array_values($validated['specialties']),
            'experience_years' => $validated['experience_years'],
            'has_tools' => (bool) ($validated['has_tools'] ?? false),
            'bio' => $validated['bio'] ?? null,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => __('site.craftsman.errors.first_name'),
            'father_name.required' => __('site.craftsman.errors.father_name'),
            'grandfather_name.required' => __('site.craftsman.errors.grandfather_name'),
            'family_name.required' => __('site.craftsman.errors.family_name'),
            'city.in' => __('site.craftsman.errors.city'),
            'specialties.required' => __('site.craftsman.errors.specialties'),
            'specialties.min' => __('site.craftsman.errors.specialties'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $payload = [
            'first_name' => is_string($this->input('first_name')) ? trim($this->input('first_name')) : $this->input('first_name'),
            'father_name' => is_string($this->input('father_name')) ? trim($this->input('father_name')) : $this->input('father_name'),
            'grandfather_name' => is_string($this->input('grandfather_name')) ? trim($this->input('grandfather_name')) : $this->input('grandfather_name'),
            'family_name' => is_string($this->input('family_name')) ? trim($this->input('family_name')) : $this->input('family_name'),
            'phone' => is_string($this->input('phone')) ? trim($this->input('phone')) : $this->input('phone'),
            'bio' => is_string($this->input('bio')) ? trim($this->input('bio')) : $this->input('bio'),
            'has_tools' => filter_var($this->input('has_tools'), FILTER_VALIDATE_BOOLEAN),
        ];

        $specialties = $this->input('specialties');
        if (is_string($specialties) && $specialties !== '') {
            $payload['specialties'] = [$specialties];
        } elseif (is_array($specialties)) {
            $payload['specialties'] = array_values(array_filter(
                $specialties,
                static fn (mixed $item): bool => is_string($item) && trim($item) !== '',
            ));
        }

        $this->merge($payload);
    }
}
