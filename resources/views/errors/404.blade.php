@extends('layouts.site')

@section('title', __('site.not_found.title'))
@section('body-class', 'service-page')

@push('head')
    <meta name="robots" content="noindex, follow">
@endpush

@section('content')
        <section class="page-hero not-found-hero" data-nav-bg="dark">
            <div class="container page-hero-content">
                <p class="service-kicker is-light">{{ __('site.not_found.kicker') }}</p>
                <h1>{{ __('site.not_found.heading') }}</h1>
                <p>{{ __('site.not_found.text') }}</p>
                <div class="not-found-actions">
                    <a href="{{ route('landing') }}" class="btn-primary">
                        <i class="fa-solid fa-house" aria-hidden="true"></i>
                        {{ __('site.not_found.home') }}
                    </a>
                    <a href="{{ url('/#contact') }}" class="btn-ghost">
                        <i class="fa-solid fa-phone" aria-hidden="true"></i>
                        {{ __('site.not_found.contact') }}
                    </a>
                </div>
            </div>
        </section>

        @if ($services->isNotEmpty())
            <div class="service-shell">
                <div class="container">
                    <section class="service-related" aria-labelledby="not-found-services-title">
                        <div class="service-works-heading">
                            <h2 id="not-found-services-title">{{ __('site.not_found.services_title') }}</h2>
                        </div>
                        <ul class="service-related-grid">
                            @foreach ($services as $item)
                                <li class="service-related-card">
                                    <a href="{{ route('services.show', $item) }}" class="service-related-media" tabindex="-1" aria-hidden="true">
                                        <img src="{{ $item->imageUrl() }}" alt="{{ $item->imageAlt() }}" loading="lazy" decoding="async">
                                    </a>
                                    <div class="service-related-body">
                                        <h3><a href="{{ route('services.show', $item) }}">{{ $item->displayTitle() }}</a></h3>
                                        @if ($item->snippet() !== '')
                                            <p>{{ $item->snippet() }}</p>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                </div>
            </div>
        @endif
@endsection
