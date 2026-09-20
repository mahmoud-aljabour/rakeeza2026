@extends('layouts.site')

@section('title', $service->displayTitle().' | '.config('app.name'))
@section('body-class', 'service-page')

@section('content')
        <div class="service-shell">
            <div class="container">
                <nav class="service-crumb" aria-label="{{ __('site.services.crumb') }}">
                    <a href="{{ route('landing') }}">{{ __('site.nav.home') }}</a>
                    <span aria-hidden="true">/</span>
                    <a href="{{ url('/#services') }}">{{ __('site.nav.services') }}</a>
                    <span aria-hidden="true">/</span>
                    <span>{{ $service->displayTitle() }}</span>
                </nav>

                <section class="service-intro">
                    <div class="service-intro-visual">
                        <img src="{{ $service->imageUrl() }}" alt="{{ $service->displayTitle() }}">
                       <!-- <span cl/ass="service-intro-bacdfkmkE[THHdge">خدمات ركيزة</span> --> 
                    </div>
                    <div class="service-intro-copy">
                        <p class="service-kicker">{{ __('site.services.kicker') }}</p>
                        <h1>{{ $service->displayTitle() }}</h1>
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
                                <i class="fa-solid fa-paper-plane"></i>
                                {{ __('site.services.send_request') }}
                            </a>
                        </div>
                    </div>
                </section>
            </div>

            <section class="service-works-band">
                <div class="container">
                    <div class="service-works-heading">
                        <p class="service-kicker">{{ __('site.services.works_kicker') }}</p>
                        <h2>{{ __('site.services.works_title') }}</h2>
                    </div>

                    <div class="service-works-list">
                        @forelse ($projects as $project)
                            <x-service-work :project="$project" :index="$loop->iteration" />
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

                @php
                    $related = $services->where('id', '!=', $service->id)->take(4);
                @endphp
                @if ($related->isNotEmpty())
                    <section class="service-related">
                        <div class="service-works-heading">
                            <p class="service-kicker">{{ __('site.services.related_kicker') }}</p>
                            <h2>{{ __('site.services.related_title') }}</h2>
                        </div>
                        <div class="service-related-grid">
                            @foreach ($related as $item)
                                <a href="{{ route('services.show', $item) }}" class="service-related-card">
                                    <img src="{{ $item->imageUrl() }}" alt="{{ $item->displayTitle() }}" loading="lazy" decoding="async">
                                    <strong>{{ $item->displayTitle() }}</strong>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>
        </div>
@endsection
