@props(['site'])

@php
    $isHome = request()->routeIs('landing');
    $isService = request()->routeIs('services.show');
    $isCraftsman = request()->routeIs('craftsman.create');
    $logoHref = $isHome ? '#home' : route('landing');
    $nextLocale = \App\Support\AppLocale::other();
@endphp

<div class="top-bar">
    <div class="top-bar-contact">
        <a href="tel:{{ $site['phone'] }}"><i class="fa-solid fa-phone"></i> {{ $site['phone'] }}</a>
        <a href="mailto:{{ $site['email'] }}"><i class="fa-solid fa-envelope"></i> {{ $site['email'] }}</a>
    </div>
    <div class="top-bar-actions">
        <a href="{{ route('craftsman.create') }}" class="btn-craftsman-link"><i class="fa-solid fa-user-gear"></i> {{ __('site.join_craftsman') }}</a>
    </div>
</div>

<header>
    <div class="nav-container">
        <div class="logo-area">
            <a href="{{ $logoHref }}" aria-label="{{ __('site.brand_full') }}">
                <img src="{{ asset('images/logo.png') }}" alt="{{ __('site.brand_full') }}" width="85" height="54" decoding="async">
            </a>
        </div>

        <nav class="nav-menu" id="main-nav">
            <a href="{{ $isHome ? '#home' : url('/#home') }}">{{ __('site.nav.home') }}</a>
            <a href="{{ $isHome ? '#about' : url('/#about') }}">{{ __('site.nav.about') }}</a>
            <a href="{{ $isHome ? '#services' : url('/#services') }}" @class(['is-active' => $isService]) @if ($isService) aria-current="page" @endif>{{ __('site.nav.services') }}</a>
            <a href="{{ $isHome ? '#projects' : url('/#projects') }}">{{ __('site.nav.projects') }}</a>
            <a href="{{ $isHome ? '#contact' : url('/#contact') }}">{{ __('site.nav.contact') }}</a>
        </nav>

        <div class="header-actions">
            <a href="{{ route('locale.switch', $nextLocale) }}" class="lang-switch" hreflang="{{ $nextLocale }}" lang="{{ $nextLocale }}" aria-label="{{ __('site.lang.switch_to') }}">
                <i class="fa-solid fa-language" aria-hidden="true"></i>
                <span>{{ __('site.lang.label') }}</span>
            </a>
            <a href="{{ route('craftsman.create') }}" @class(['nav-cta', 'is-active' => $isCraftsman])>
                @if ($isCraftsman)
                    <span class="nav-cta-full">{{ __('site.craftsman_register') }}</span>
                    <span class="nav-cta-short">{{ __('site.craftsman_register_short') }}</span>
                @else
                    <span class="nav-cta-full">{{ __('site.join_craftsman_full') }}</span>
                    <span class="nav-cta-short">{{ __('site.join_craftsman') }}</span>
                @endif
            </a>
            <button type="button" class="menu-toggle" aria-label="{{ __('site.nav.open_menu') }}" aria-expanded="false" aria-controls="main-nav">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>
</header>
<div class="nav-overlay" id="nav-overlay" aria-hidden="true"></div>
