<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Service;

final class StructuredData
{
    /**
     * LocalBusiness and WebSite for the homepage.
     *
     * @param  array<string, string>  $site
     * @return array<string, mixed>
     */
    public static function home(array $site): array
    {
        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                self::localBusiness($site),
                self::website(),
            ],
        ];
    }

    /**
     * Service and BreadcrumbList for a public service page.
     *
     * @return array<string, mixed>
     */
    public static function service(Service $service): array
    {
        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                self::serviceNode($service),
                self::breadcrumbs($service),
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
                '@id' => self::publicUrl().'/#website',
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
     * @param  array<string, string>  $site
     * @return array<string, mixed>
     */
    private static function localBusiness(array $site): array
    {
        $business = [
            '@type' => 'LocalBusiness',
            '@id' => self::publicUrl().'/#business',
            'name' => __('site.schema.business_name'),
            'url' => self::publicUrl(),
            'logo' => self::publicUrl().'/images/logo.png',
            'description' => __('site.schema.business_description'),
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => 'Gaza',
                'addressRegion' => 'Gaza Strip',
                'addressCountry' => 'PS',
            ],
            'areaServed' => self::areaServed(),
        ];

        return self::withContact($business, $site);
    }

    /**
     * @return array<string, mixed>
     */
    private static function website(): array
    {
        return [
            '@type' => 'WebSite',
            '@id' => self::publicUrl().'/#website',
            'name' => __('site.schema.business_name'),
            'url' => self::publicUrl(),
            'publisher' => [
                '@id' => self::publicUrl().'/#business',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function serviceNode(Service $service): array
    {
        return [
            '@type' => 'Service',
            'name' => self::serviceName($service),
            'description' => $service->metaDescription(),
            'url' => url()->current(),
            'provider' => [
                '@type' => 'LocalBusiness',
                '@id' => self::publicUrl().'/#business',
                'name' => __('site.schema.business_name'),
                'url' => self::publicUrl(),
            ],
            'areaServed' => self::areaServed(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function breadcrumbs(Service $service): array
    {
        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => __('site.nav.home'),
                    'item' => self::publicUrl(),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => __('site.services.crumb_services'),
                    'item' => self::publicUrl().'/#services',
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $service->displayTitle(),
                    'item' => url()->current(),
                ],
            ],
        ];
    }

    private static function serviceName(Service $service): string
    {
        $english = trim((string) $service->seo_title_en);
        $arabic = trim((string) $service->seo_title);

        if (AppLocale::current() === AppLocale::ENGLISH && $english !== '') {
            return $english;
        }

        if ($arabic !== '') {
            return $arabic;
        }

        return $service->displayTitle();
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $site
     * @return array<string, mixed>
     */
    private static function withContact(array $data, array $site): array
    {
        $phone = trim((string) ($site['phone'] ?? ''));
        $email = trim((string) ($site['email'] ?? ''));

        if ($phone !== '') {
            $data['telephone'] = $phone;
        }

        if ($email !== '') {
            $data['email'] = $email;
        }

        $sameAs = self::sameAs($site);

        if ($sameAs !== []) {
            $data['sameAs'] = $sameAs;
        }

        return $data;
    }

    /**
     * @param  array<string, string>  $site
     * @return list<string>
     */
    private static function sameAs(array $site): array
    {
        /** @var list<string> $configured */
        $configured = config('rakeeza.social', []);
        $links = [];

        foreach ($configured as $url) {
            if (is_string($url) && $url !== '') {
                $links[] = $url;
            }
        }

        $whatsapp = preg_replace('/\D+/', '', (string) ($site['whatsapp'] ?? '')) ?? '';

        if ($whatsapp !== '') {
            $links[] = 'https://wa.me/'.$whatsapp;
        }

        return array_values(array_unique($links));
    }

    /**
     * @return array{ '@type': string, name: string }
     */
    private static function areaServed(): array
    {
        return [
            '@type' => 'Place',
            'name' => __('site.schema.area'),
        ];
    }

    private static function publicUrl(): string
    {
        return rtrim((string) config('rakeeza.public_url'), '/');
    }
}
