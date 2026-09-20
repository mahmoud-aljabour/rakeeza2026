<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\PalestinianPhone;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
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
            'first_name' => ['required', 'string', 'min:2', 'max:80', 'regex:/^[\p{L}\s\'\-\.]+$/u'],
            'father_name' => ['required', 'string', 'min:2', 'max:80', 'regex:/^[\p{L}\s\'\-\.]+$/u'],
            'grandfather_name' => ['required', 'string', 'min:2', 'max:80', 'regex:/^[\p{L}\s\'\-\.]+$/u'],
            'family_name' => ['required', 'string', 'min:2', 'max:80', 'regex:/^[\p{L}\s\'\-\.]+$/u'],
            'national_id' => ['required', 'string', 'regex:/^\d{9}$/', Rule::unique('craftsmen', 'national_id')],
            'phone' => ['required', 'string', 'max:20', new PalestinianPhone],
            'city' => ['required', 'string', Rule::in(self::CITIES)],
            'specialties' => ['required', 'array', 'min:1'],
            'specialties.*' => ['required', 'string', 'max:255'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:60'],
            'has_tools' => ['required', 'boolean'],
            'bio' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array{name: string, national_id: string, phone: string, city: string, specialty: list<string>, experience_years: int, has_tools: bool, bio?: string|null}
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
            'national_id' => $validated['national_id'],
            'phone' => $validated['phone'],
            'city' => $validated['city'],
            'specialty' => array_values($validated['specialties']),
            'experience_years' => $validated['experience_years'],
            'has_tools' => (bool) $validated['has_tools'],
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
            'first_name.min' => __('site.craftsman.errors.name_length'),
            'first_name.max' => __('site.craftsman.errors.name_length'),
            'first_name.regex' => __('site.craftsman.errors.name_alpha'),
            'father_name.required' => __('site.craftsman.errors.father_name'),
            'father_name.min' => __('site.craftsman.errors.name_length'),
            'father_name.max' => __('site.craftsman.errors.name_length'),
            'father_name.regex' => __('site.craftsman.errors.name_alpha'),
            'grandfather_name.required' => __('site.craftsman.errors.grandfather_name'),
            'grandfather_name.min' => __('site.craftsman.errors.name_length'),
            'grandfather_name.max' => __('site.craftsman.errors.name_length'),
            'grandfather_name.regex' => __('site.craftsman.errors.name_alpha'),
            'family_name.required' => __('site.craftsman.errors.family_name'),
            'family_name.min' => __('site.craftsman.errors.name_length'),
            'family_name.max' => __('site.craftsman.errors.name_length'),
            'family_name.regex' => __('site.craftsman.errors.name_alpha'),
            'national_id.required' => __('site.craftsman.errors.national_id_required'),
            'national_id.regex' => __('site.craftsman.errors.national_id_format'),
            'national_id.unique' => __('site.craftsman.errors.national_id_unique'),
            'phone.required' => __('site.form.errors.phone'),
            'city.in' => __('site.craftsman.errors.city'),
            'specialties.required' => __('site.craftsman.errors.specialties'),
            'specialties.min' => __('site.craftsman.errors.specialties'),
            'has_tools.required' => __('site.craftsman.errors.has_tools'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $payload = [
            'first_name' => is_string($this->input('first_name')) ? trim($this->input('first_name')) : $this->input('first_name'),
            'father_name' => is_string($this->input('father_name')) ? trim($this->input('father_name')) : $this->input('father_name'),
            'grandfather_name' => is_string($this->input('grandfather_name')) ? trim($this->input('grandfather_name')) : $this->input('grandfather_name'),
            'family_name' => is_string($this->input('family_name')) ? trim($this->input('family_name')) : $this->input('family_name'),
            'national_id' => is_string($this->input('national_id')) ? trim($this->input('national_id')) : $this->input('national_id'),
            'phone' => is_string($this->input('phone')) ? trim($this->input('phone')) : $this->input('phone'),
            'bio' => is_string($this->input('bio')) ? trim($this->input('bio')) : $this->input('bio'),
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

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $validator->errors()->first() ?: __('site.form.craftsman_failed'),
            'errors' => $validator->errors(),
        ], 422));
    }
}
