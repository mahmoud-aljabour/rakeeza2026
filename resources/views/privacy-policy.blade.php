@extends('layouts.site')

@section('title', __('site.meta.privacy_title'))
@section('body-class', 'service-page')

@push('head')
    @include('partials.seo-meta', [
        'title' => __('site.meta.privacy_title'),
        'description' => __('site.meta.privacy_description'),
        'image' => asset('images/logo.png'),
    ])
    @include('partials.json-ld', ['schema' => \App\Support\StructuredData::webPage(
        __('site.meta.privacy_title'),
        __('site.meta.privacy_description'),
        route('privacy'),
    )])
@endpush

@section('content')
        <section class="page-hero policy-hero" data-nav-bg="dark">
            <div class="policy-hero-mark" aria-hidden="true">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div class="container page-hero-content">
                <nav class="page-breadcrumb" aria-label="{{ __('site.privacy.crumb') }}">
                    <a href="{{ route('landing') }}">{{ __('site.nav.home') }}</a>
                    <span aria-hidden="true">/</span>
                    <span>{{ __('site.privacy.title') }}</span>
                </nav>
                <p class="service-kicker is-light">{{ __('site.privacy.kicker') }}</p>
                <h1>{{ __('site.privacy.title') }}</h1>
                @if ($sections->isNotEmpty())
                    <div class="policy-hero-meta">
                        <span>
                            <i class="fa-solid fa-list-ol" aria-hidden="true"></i>
                            {{ trans_choice('site.privacy.sections_count', $sections->count(), ['count' => $sections->count()]) }}
                        </span>
                    </div>
                @endif
            </div>
        </section>

        <div class="service-shell policy-shell">
            <div class="container">
                @if ($sections->isEmpty())
                    <p class="policy-empty">{{ __('site.privacy.empty') }}</p>
                @else
                    <div class="policy-layout">
                        <nav class="policy-toc" aria-label="{{ __('site.privacy.contents') }}">
                            <p class="policy-toc-title">{{ __('site.privacy.contents') }}</p>
                            <ol>
                                @foreach ($sections as $section)
                                    <li>
                                        <a href="#policy-{{ $section->id }}">
                                            <span>{{ sprintf('%02d', $loop->iteration) }}</span>
                                            {{ $section->displayTitle() }}
                                        </a>
                                    </li>
                                @endforeach
                            </ol>
                        </nav>

                        <div class="policy-list">
                            @foreach ($sections as $section)
                                <article id="policy-{{ $section->id }}" class="policy-section">
                                    <span class="policy-index" aria-hidden="true">{{ sprintf('%02d', $loop->iteration) }}</span>
                                    <div>
                                        <h2>{{ $section->displayTitle() }}</h2>
                                        <p>{{ $section->displayDescription() }}</p>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
@endsection
