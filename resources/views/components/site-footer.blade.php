@props(['site', 'services'])

@php
    $isHome = request()->routeIs('landing');
    $logoHref = $isHome ? '#home' : route('landing');
@endphp

<footer id="site-footer" data-nav-bg="dark">
    <div class="footer-grid">
        <div class="footer-about">
            <a href="{{ $logoHref }}" class="footer-logo" aria-label="{{ __('site.brand_full') }}">
                <img src="{{ asset('images/logo.png') }}" alt="{{ __('site.brand_full') }}" width="120" height="64" loading="lazy" decoding="async">
            </a>
            <p>{{ $site['footer_about'] }}</p>
        </div>

        <div>
            <h4 class="footer-title">{{ __('site.footer.quick_links') }}</h4>
            <ul class="footer-links">
                <li><a href="{{ $isHome ? '#home' : url('/#home') }}">{{ __('site.nav.home') }}</a></li>
                <li><a href="{{ $isHome ? '#about' : url('/#about') }}">{{ __('site.nav.about') }}</a></li>
                <li><a href="{{ $isHome ? '#services' : url('/#services') }}">{{ __('site.nav.services') }}</a></li>
                <li><a href="{{ $isHome ? '#projects' : url('/#projects') }}">{{ __('site.nav.projects') }}</a></li>
                <li><a href="{{ route('craftsman.create') }}">{{ __('site.footer.join') }}</a></li>
                <li><a href="{{ route('privacy') }}">{{ __('site.footer.privacy') }}</a></li>
                <li><a href="{{ $isHome ? '#contact' : url('/#contact') }}">{{ __('site.nav.contact') }}</a></li>
            </ul>
        </div>

        <div>
            <h4 class="footer-title">{{ __('site.footer.services') }}</h4>
            <ul class="footer-links">
                @foreach ($services as $item)
                    <li><a href="{{ route('services.show', $item) }}">{{ $item->displayTitle() }}</a></li>
                @endforeach
            </ul>
        </div>

        <div>
            <h4 class="footer-title">{{ __('site.footer.contact') }}</h4>
            <ul class="footer-contact">
                <li><a href="tel:{{ $site['phone'] }}"><i class="fa-solid fa-phone" aria-hidden="true"></i> <span dir="ltr">{{ $site['phone'] }}</span></a></li>
                <li><a href="mailto:{{ $site['email'] }}"><i class="fa-solid fa-envelope" aria-hidden="true"></i> <span dir="ltr">{{ $site['email'] }}</span></a></li>
                <li><i class="fa-solid fa-clock" aria-hidden="true"></i> <span>{{ $site['hours'] }}</span></li>
            </ul>
        </div>
    </div>

    <div class="copyright">
        <p>{{ __('site.footer.copyright', ['year' => now()->year, 'company' => __('site.brand_company')]) }}</p>
        <p class="copyright-legal">
            <a href="{{ route('privacy') }}">{{ __('site.footer.privacy') }}</a>
        </p>
    </div>
</footer>
