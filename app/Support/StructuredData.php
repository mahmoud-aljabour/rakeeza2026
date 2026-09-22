<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Service;

final class StructuredData
{
    /**
     * @param  array<string, string>  $site
     * @return array<string, mixed>
     */
    public static function localBusiness(array $site): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => __('site.brand'),
            'description' => __('site.schema.business_description'),
            'url' => route('landing'),
            'image' => asset('images/logo.png'),
            'telephone' => $site['phone'],
            'email' => $site['email'],
            'areaServed' => self::areaServed(),
        ];
    }

    /**
     * @param  array<string, string>  $site
     * @return array<string, mixed>
     */
    public static function service(Service $service, array $site): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => $service->displayTitle(),
            'serviceType' => $service->displayTitle(),
            'description' => $service->metaDescription(),
            'url' => route('services.show', $service),
            'image' => $service->imageUrl(),
            'areaServed' => self::areaServed(),
            'provider' => [
                '@type' => 'LocalBusiness',
                'name' => __('site.brand'),
                'telephone' => $site['phone'],
                'email' => $site['email'],
                'areaServed' => self::areaServed(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function webPage(string $title, string $description, string $url): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => $title,
            'description' => $description,
            'url' => $url,
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => __('site.brand'),
                'url' => route('landing'),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function toScript(array $data): string
    {
        return (string) json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_THROW_ON_ERROR,
        );
    }

    /**
     * @return array{ '@type': string, name: string }
     */
    private static function areaServed(): array
    {
        return [
            '@type' => 'AdministrativeArea',
            'name' => __('site.schema.area'),
        ];
    }
}
