<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\PublicImage;
use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
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
            'description' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'projects_count' => ['nullable', 'integer', 'min:0', 'max:99999'],
            'is_active' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'image_url' => ['nullable', 'string', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'image_url.max' => 'رابط الصورة أطول من المسموح.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $url = $this->input('image_url');

            if (! filled($url) || PublicImage::isAcceptableReference($url)) {
                return;
            }

            $validator->errors()->add(
                'image_url',
                'أدخل رابط صورة يبدأ بـ http أو مسارًا محليًا مثل images/service.jpg.',
            );
        });
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('projects_count')) {
            $count = $this->input('projects_count');
            $this->merge([
                'projects_count' => ($count === '' || $count === null) ? 0 : (int) $count,
            ]);
        }

        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->input('is_active'), FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        if ($this->has('image_url')) {
            $this->merge([
                'image_url' => trim((string) $this->input('image_url')),
            ]);
        }
    }
}
