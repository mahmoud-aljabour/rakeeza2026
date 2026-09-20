<!DOCTYPE html>
<html lang="{{ \App\Support\AppLocale::current() }}" dir="{{ \App\Support\AppLocale::direction() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0a3356">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/landing.css', 'resources/js/landing.js'])
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.7/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.7/dist/ScrollTrigger.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        window.Rakeeza = {
            locale: @json(\App\Support\AppLocale::current()),
            dir: @json(\App\Support\AppLocale::direction()),
            i18n: {
                openMenu: @json(__('site.nav.open_menu')),
                closeMenu: @json(__('site.nav.close_menu')),
                submissionSuccess: @json(__('site.form.submission_success')),
                sendFailed: @json(__('site.form.send_failed')),
                requiredFields: @json(__('site.form.required_fields')),
                sending: @json(__('site.contact.sending')),
                name: @json(__('site.form.name')),
                phone: @json(__('site.form.phone')),
                service: @json(__('site.form.service')),
                details: @json(__('site.form.details')),
                craftsmanSuccess: @json(__('site.craftsman.success')),
                craftsmanFailed: @json(__('site.form.craftsman_failed')),
                lightboxClose: @json(__('site.lightbox.close')),
                lightboxPrev: @json(__('site.lightbox.prev')),
                lightboxNext: @json(__('site.lightbox.next'))
            }
        };
    </script>
    @stack('head')
</head>
<body class="@yield('body-class')">
    <a class="skip-link" href="#main">{{ __('site.skip_content') }}</a>
    <div class="scroll-progress" aria-hidden="true"></div>

    <x-site-header :site="$site" />

    <main id="main">
        @yield('content')
    </main>

    <x-site-footer :site="$site" :services="$services" />

    <a href="https://wa.me/{{ $site['whatsapp'] }}" class="floating-whatsapp" target="_blank" rel="noopener noreferrer" aria-label="{{ __('site.whatsapp_float') }}">
        <i class="fa-brands fa-whatsapp"></i>
    </a>
</body>
</html>
