<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\PublicImage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'slug' => ['nullable', 'string', 'max:80', 'regex:/^(?!\d+$)[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('services', 'slug')->ignore($this->route('service'))],
            'description' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'body' => ['nullable', 'string', 'max:20000'],
            'body_en' => ['nullable', 'string', 'max:20000'],
            'seo_title' => ['nullable', 'string', 'max:120'],
            'seo_title_en' => ['nullable', 'string', 'max:120'],
            'meta_description' => ['nullable', 'string', 'min:150', 'max:160'],
            'meta_description_en' => ['nullable', 'string', 'min:150', 'max:160'],
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
            'slug.unique' => 'هذا الرابط مستخدم لخدمة أخرى.',
            'slug.regex' => 'استخدم حروفاً إنجليزية صغيرة وأرقاماً وشرطات فقط.',
            'meta_description.min' => 'وصف الميتا يجب أن يكون بين 150 و160 حرفاً.',
            'meta_description.max' => 'وصف الميتا يجب أن يكون بين 150 و160 حرفاً.',
            'meta_description_en.min' => 'الوصف الإنجليزي يجب أن يكون بين 150 و160 حرفاً.',
            'meta_description_en.max' => 'الوصف الإنجليزي يجب أن يكون بين 150 و160 حرفاً.',
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

        foreach ([
            'slug',
            'title_en',
            'description',
            'description_en',
            'body',
            'body_en',
            'seo_title',
            'seo_title_en',
            'meta_description',
            'meta_description_en',
        ] as $field) {
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
