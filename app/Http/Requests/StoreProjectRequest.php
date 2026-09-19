<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\PublicImage;
use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
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
            'details' => ['nullable', 'string'],
            'details_en' => ['nullable', 'string'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'order_column' => ['nullable', 'integer', 'min:0'],
            'sync_images' => ['sometimes', 'boolean'],
            'items' => ['nullable', 'array', 'max:12'],
            'items.*.source' => ['required_with:items', 'in:existing,file,url'],
            'items.*.path' => ['nullable', 'string', 'max:2048'],
            'items.*.url' => ['nullable', 'string', 'max:2048'],
            'items.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.max' => 'يمكن إضافة 12 صورة كحد أقصى.',
            'items.*.url.max' => 'رابط الصورة أطول من المسموح.',
            'items.*.image.max' => 'حجم الصورة أكبر من 4 ميغابايت.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            foreach ($this->input('items', []) as $index => $item) {
                $source = $item['source'] ?? '';

                if ($source === 'url') {
                    $url = trim((string) ($item['url'] ?? ''));

                    if ($url === '' || PublicImage::isAcceptableReference($url)) {
                        continue;
                    }

                    $validator->errors()->add(
                        "items.$index.url",
                        'أدخل رابط صورة يبدأ بـ http أو مسارًا محليًا مثل images/project.jpg.',
                    );
                }

                if ($source === 'file' && ! $this->file("items.$index.image")) {
                    $validator->errors()->add(
                        "items.$index.image",
                        'اختر ملف صورة صالحًا.',
                    );
                }

                if ($source === 'existing' && trim((string) ($item['path'] ?? '')) === '') {
                    $validator->errors()->add(
                        "items.$index.path",
                        'مسار الصورة غير صالح.',
                    );
                }
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $items = $this->input('items');

        $payload = [];

        if ($this->has('service_id')) {
            $serviceId = $this->input('service_id');
            $payload['service_id'] = ($serviceId === '' || $serviceId === '0' || $serviceId === null)
                ? null
                : $serviceId;
        }

        if ($this->has('sync_images')) {
            $payload['sync_images'] = filter_var($this->input('sync_images'), FILTER_VALIDATE_BOOLEAN);
        }

        if (! is_array($items)) {
            if ($payload !== []) {
                $this->merge($payload);
            }

            return;
        }

        $this->merge([
            ...$payload,
            'items' => array_map(static function (mixed $item): mixed {
                if (! is_array($item)) {
                    return $item;
                }

                if (array_key_exists('url', $item)) {
                    $item['url'] = trim((string) $item['url']);
                }

                if (array_key_exists('path', $item)) {
                    $item['path'] = trim((string) $item['path']);
                }

                return $item;
            }, $items),
        ]);
    }
}
