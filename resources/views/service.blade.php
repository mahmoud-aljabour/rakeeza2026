@extends('layouts.site')

@section('title', $service->seoTitle())
@section('body-class', 'service-page')

@push('head')
    @include('partials.seo-meta', [
        'title' => $service->seoTitle(),
        'description' => $service->metaDescription(),
        'image' => $service->imageUrl(),
    ])
    @include('partials.json-ld', ['schema' => \App\Support\StructuredData::service($service)])
@endpush

@section('content')
        <div class="service-shell">
            <div class="container">
                <nav class="service-crumb" aria-label="{{ __('site.services.crumb') }}">
                    <ol>
                        <li><a href="{{ route('landing') }}">{{ __('site.nav.home') }}</a></li>
                        <li><a href="{{ url('/#services') }}">{{ __('site.services.crumb_services') }}</a></li>
                        <li><span aria-current="page">{{ $service->displayTitle() }}</span></li>
                    </ol>
                </nav>

                <section class="service-intro">
                    <div class="service-intro-visual">
                        <img src="{{ $service->imageUrl() }}" alt="{{ $service->imageAlt() }}" fetchpriority="high" decoding="async">
                    </div>
                    <div class="service-intro-copy">
                        <p class="service-kicker">{{ __('site.services.kicker') }}</p>
                        <h1>{{ $service->pageHeading() }}</h1>
                        <p class="service-lead">{{ $service->displayDescription() }}</p>
                        <div class="service-facts">
                            <div class="service-fact">
                                <strong>{{ $service->projects_count }}</strong>
                                <span>{{ __('site.services.projects_count') }}</span>
                            </div>
                            <div class="service-fact">
                                <strong>{{ __('site.services.fast') }}</strong>
                                <span>{{ __('site.services.fast_text') }}</span>
                            </div>
                            <div class="service-fact">
                                <strong>{{ __('site.services.direct') }}</strong>
                                <span>{{ __('site.services.direct_text') }}</span>
                            </div>
                        </div>
                        <div class="service-cta-row">
                            <a
                                href="https://wa.me/{{ $site['whatsapp'] }}?text={{ urlencode(__('site.services.whatsapp_quote_message', ['service' => $service->displayTitle()])) }}"
                                class="btn-primary btn-whatsapp"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                                {{ __('site.services.request_quote') }}
                            </a>
                            <a href="#contact" class="btn-ghost">
                                <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                                {{ __('site.services.send_request') }}
                            </a>
                        </div>
                    </div>
                </section>

                @if ($service->bodyBlocks() !== [])
                    <article class="service-article">
                        <div class="service-article-head">
                            <h2>{{ __('site.services.about_title') }}</h2>
                            <p>{{ __('site.services.about_hint') }}</p>
                        </div>
                        <div class="service-article-body is-collapsed" data-collapsible>
                            <div class="service-article-text" id="service-article-text">
                                @foreach ($service->bodyBlocks() as $block)
                                    @if ($block['tag'] === 'h2')
                                        <h2>{{ $block['text'] }}</h2>
                                    @elseif ($block['tag'] === 'h3')
                                        <h3>{{ $block['text'] }}</h3>
                                    @elseif ($block['tag'] === 'ul')
                                        <ul>
                                            @foreach ($block['items'] as $item)
                                                <li>{{ $item }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p>{{ $block['text'] }}</p>
                                    @endif
                                @endforeach
                            </div>
                            <button
                                type="button"
                                class="service-article-toggle"
                                aria-expanded="false"
                                aria-controls="service-article-text"
                                data-more-label="{{ __('site.services.read_more') }}"
                                data-less-label="{{ __('site.services.read_less') }}"
                            >
                                <span>{{ __('site.services.read_more') }}</span>
                                <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                            </button>
                        </div>
                    </article>
                @endif
            </div>

            <section class="service-works-band">
                <div class="container">
                    <div class="service-works-heading">
                        <p class="service-kicker">{{ __('site.services.works_kicker') }}</p>
                        <h2>{{ __('site.services.works_title') }}</h2>
                    </div>

                    <div class="service-works-list">
                        @forelse ($projects as $project)
                            <x-service-work :project="$project" :index="$loop->iteration" :service-name="$service->displayTitle()" />
                        @empty
                            <div class="empty-projects">
                                <i class="fa-solid fa-images" aria-hidden="true"></i>
                                <p>{{ __('site.services.empty_projects') }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>

            <div class="container">
                <section class="service-quote" id="contact">
                    <div class="service-quote-copy">
                        <p class="service-kicker is-light">{{ __('site.services.quote_kicker') }}</p>
                        <h2>{{ __('site.services.quote_title') }}</h2>
                        <p>{{ __('site.services.quote_text') }}</p>
                        <a href="tel:{{ $site['phone'] }}" class="service-quote-phone">
                            <i class="fa-solid fa-phone"></i>
                            <span dir="ltr">{{ $site['phone'] }}</span>
                        </a>
                    </div>

                    <form class="contact-form-card" id="contact-form" action="{{ route('leads.store') }}" method="POST">
                        @csrf
                        <h3>{{ __('site.contact.form_title') }}</h3>
                        <p>{{ __('site.contact.form_dashboard_intro') }}</p>
                        <div class="hp-field" aria-hidden="true">
                            <label for="contact-website">Website</label>
                            <input id="contact-website" name="website" type="text" tabindex="-1" autocomplete="off">
                        </div>
                        <div class="form-row">
                            <div class="form-field">
                                <label for="contact-name">{{ __('site.contact.name') }}</label>
                                <input id="contact-name" name="name" type="text" required autocomplete="name" minlength="2" maxlength="80" placeholder="{{ __('site.contact.name_placeholder') }}">
                            </div>
                            <div class="form-field">
                                <label for="contact-phone">{{ __('site.contact.phone_field') }}</label>
                                <input id="contact-phone" name="phone" type="tel" required autocomplete="tel" inputmode="tel" placeholder="05xxxxxxxx" dir="ltr">
                            </div>
                        </div>
                        <div class="form-field">
                            <label for="contact-email">{{ __('site.contact.email_field') }}</label>
                            <input id="contact-email" name="email" type="email" required autocomplete="email" inputmode="email" maxlength="255" placeholder="{{ __('site.contact.email_placeholder') }}" dir="ltr">
                        </div>
                        <fieldset class="form-field">
                            <legend>{{ __('site.contact.service') }}</legend>
                            <p class="form-field-hint">{{ __('site.contact.service_multiple_hint') }}</p>
                            <details class="service-multiselect" id="contact-service">
                                <summary>
                                    <span
                                        class="service-multiselect-label"
                                        data-placeholder="{{ __('site.contact.service_placeholder') }}"
                                        data-count-label="{{ __('site.contact.service_selected_count') }}"
                                    >{{ __('site.contact.service_placeholder') }}</span>
                                    <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                                </summary>
                                <div class="service-choice-grid">
                                    @foreach ($services as $item)
                                        <label class="service-choice">
                                            <input type="checkbox" name="service_ids[]" value="{{ $item->id }}" @checked($item->id === $service->id)>
                                            <span>{{ $item->displayTitle() }}</span>
                                        </label>
                                    @endforeach
                                    <label class="service-choice">
                                        <input type="checkbox" name="service_ids[]" value="general">
                                        <span>{{ __('site.contact.general_inquiry') }}</span>
                                    </label>
                                </div>
                            </details>
                        </fieldset>
                        <div class="form-field">
                            <label for="contact-message">{{ __('site.contact.details') }}</label>
                            <textarea id="contact-message" name="message" required maxlength="1000" placeholder="{{ __('site.contact.details_placeholder') }}"></textarea>
                        </div>
                        <button type="submit" class="btn-primary" data-default-label="{{ __('site.contact.send_request') }}" data-loading-label="{{ __('site.contact.sending') }}">
                            <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                            <span>{{ __('site.contact.send_request') }}</span>
                        </button>
                        <p class="form-note" id="contact-form-note" role="status"></p>
                    </form>
                </section>

                @if ($relatedServices->isNotEmpty())
                    <section class="service-related" aria-labelledby="service-related-title">
                        <div class="service-works-heading">
                            <p class="service-kicker">{{ __('site.services.related_kicker') }}</p>
                            <h2 id="service-related-title">{{ __('site.services.related_title') }}</h2>
                        </div>
                        <ul class="service-related-grid">
                            @foreach ($relatedServices as $item)
                                <li class="service-related-card">
                                    <a href="{{ route('services.show', $item) }}" class="service-related-media" tabindex="-1" aria-hidden="true">
                                        <img src="{{ $item->imageUrl() }}" alt="{{ $item->imageAlt() }}" loading="lazy" decoding="async">
                                    </a>
                                    <div class="service-related-body">
                                        <h3><a href="{{ route('services.show', $item) }}">{{ $item->displayTitle() }}</a></h3>
                                        @if ($item->snippet() !== '')
                                            <p>{{ $item->snippet() }}</p>
                                        @endif
                                        <a href="{{ route('services.show', $item) }}" class="service-related-link">
                                            {{ __('site.services.related_cta') }}
                                            <i class="fa-solid {{ \App\Support\AppLocale::isRtl() ? 'fa-arrow-left' : 'fa-arrow-right' }}" aria-hidden="true"></i>
                                        </a>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            </div>
        </div>
@endsection
