@extends('layouts.site')

@section('title', __('site.meta.landing_title', ['app' => config('app.name')]))

@push('head')
    <link rel="preload" as="image" href="{{ asset('images/hero-bg-3.jpg') }}">
@endpush

@section('content')
    <!-- Hero Section -->
    <section class="hero" id="home" data-nav-bg="dark">
        <div class="hero-slides" aria-hidden="true">
            <div class="hero-slide is-active" style="background-image: url('{{ asset('images/hero-bg-3.jpg') }}');"></div>
            <div class="hero-slide" style="background-image: url('{{ asset('images/hero-bg.jpg') }}');"></div>
            <div class="hero-slide" style="background-image: url('{{ asset('images/hero-bg-2.webp') }}');"></div>
        </div>
        <div class="hero-container">
            <div class="hero-content">
                <span class="hero-kicker">{{ $site['hero_kicker'] }}</span>
                <h1>{{ trim($site['hero_title']) }} <span>{{ $site['hero_highlight'] }}</span></h1>
                <p>{{ $site['hero_text'] }}</p>
                <div class="hero-btns">
                    <a href="tel:{{ $site['phone'] }}" class="btn-primary">
                        <i class="fa-solid fa-phone-volume"></i>
                        {{ __('site.hero.cta') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Features Bar -->
    <div class="container">
        <div class="features-bar">
            <div class="feature-box">
                <div class="feature-icon"><i class="fa-solid fa-faucet-drip"></i></div>
                <div>
                    <h4>{{ __('site.features.plumbing_title') }}</h4>
                    <p>{{ __('site.features.plumbing_text') }}</p>
                </div>
            </div>
            <div class="feature-box">
                <div class="feature-icon"><i class="fa-solid fa-compass-drafting"></i></div>
                <div>
                    <h4>{{ __('site.features.architecture_title') }}</h4>
                    <p>{{ __('site.features.architecture_text') }}</p>
                </div>
            </div>
            <div class="feature-box">
                <div class="feature-icon"><i class="fa-solid fa-trowel-bricks"></i></div>
                <div>
                    <h4>{{ __('site.features.roofing_title') }}</h4>
                    <p>{{ __('site.features.roofing_text') }}</p>
                </div>
            </div>
            <div class="feature-box">
                <div class="feature-icon"><i class="fa-solid fa-helmet-safety"></i></div>
                <div>
                    <h4>{{ __('site.features.construction_title') }}</h4>
                    <p>{{ __('site.features.construction_text') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- About Section -->
    <section class="section-padding" id="about">
        <div class="container">
            <div class="about-grid">
                <div class="about-text">
                    <h3>{{ __('site.about.title') }}</h3>
                    <p>{{ $site['about_text'] }}</p>
                    
                    <div class="about-highlights">
                        <div class="about-highlight-item">
                            <i class="fa-solid fa-circle-check"></i>
                            {{ $site['about_highlight_1'] }}
                        </div>
                        <div class="about-highlight-item">
                            <i class="fa-solid fa-circle-check"></i>
                            {{ $site['about_highlight_2'] }}
                        </div>
                    </div>

                    <div class="contact-cards-inline">
                        <a class="contact-box-sm" href="tel:{{ $site['phone'] }}">
                            <i class="fa-solid fa-phone-flip" aria-hidden="true"></i>
                            <div>
                                <span>{{ __('site.about.quote_label') }}</span>
                                <strong class="ltr">{{ $site['phone'] }}</strong>
                            </div>
                        </a>
                        <a class="contact-box-sm" href="mailto:{{ $site['email'] }}">
                            <i class="fa-solid fa-envelope-open-text" aria-hidden="true"></i>
                            <div>
                                <span>{{ __('site.about.email_label') }}</span>
                                <strong class="ltr" style="font-size:0.85rem;">{{ $site['email'] }}</strong>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="why-us-image-card about-photo-card">
                    <img src="{{ asset('images/about-team.jpg') }}" alt="{{ __('site.about.photo_alt') }}" loading="lazy" decoding="async">
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="section-padding" id="services">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">{{ __('site.services.title') }}</h2>
            </div>

            <div class="services-grid">
                @foreach ($services as $service)
                    <article class="service-card">
                        <a href="{{ route('services.show', $service) }}" class="service-image">
                            <img src="{{ $service->imageUrl() }}" alt="{{ $service->displayTitle() }}" loading="lazy" decoding="async">
                        </a>
                        <div class="service-body">
                            <div>
                                <div class="service-header">
                                    <h3 class="service-title">
                                        <a href="{{ route('services.show', $service) }}">{{ $service->displayTitle() }}</a>
                                    </h3>
                                </div>
                                <p class="service-desc">{{ $service->displayDescription() }}</p>
                            </div>
                            <a href="{{ route('services.show', $service) }}" class="service-btn">
                                {{ __('site.services.details') }}
                                <i class="fa-solid {{ \App\Support\AppLocale::isRtl() ? 'fa-arrow-left' : 'fa-arrow-right' }}" aria-hidden="true"></i>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <!-- Our Vision -->
            <div class="vision-card">
                <h3><i class="fa-solid fa-eye" style="color: var(--accent);"></i> {{ __('site.services.vision') }}</h3>
                <p>{{ $site['vision_text'] }}</p>
            </div>
        </div>
    </section>

    <!-- Latest Projects -->
    <section class="section-padding" id="projects">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">{{ __('site.services.more_projects') }}</h2>
            </div>
            <div class="projects-grid">
                @foreach ($projects as $project)
                    <x-project-card :project="$project" />
                @endforeach
            </div>
        </div>
    </section>

    <!-- Join Craftsman -->
    <section class="section-padding" id="craftsman">
        <div class="container">
            <div class="craftsman-banner" data-nav-bg="dark">
                <div class="craftsman-content">
                    <span class="craftsman-kicker">{{ $site['craftsman_kicker'] }}</span>
                    <h3>{{ $site['craftsman_title'] }}</h3>
                    <p>{{ $site['craftsman_text'] }}</p>
                    <ul class="craftsman-features">
                        <li>
                            <span class="craftsman-feature-icon"><i class="fa-solid fa-circle-check"></i></span>
                            {{ $site['craftsman_feature_1'] }}
                        </li>
                        <li>
                            <span class="craftsman-feature-icon"><i class="fa-solid fa-handshake"></i></span>
                            {{ $site['craftsman_feature_2'] }}
                        </li>
                    </ul>
                    <div class="craftsman-join">
                        <h4>{{ __('site.craftsman.join_today') }}</h4>
                        <a href="{{ route('craftsman.create') }}" class="btn-primary craftsman-join-btn">
                            <i class="fa-solid fa-user-plus"></i>
                            {{ __('site.craftsman.register_now') }}
                        </a>
                    </div>
                </div>
                <div class="craftsman-action">
                    <img src="{{ asset('images/craftsman.jpg') }}" alt="{{ __('site.craftsman.photo_alt') }}" loading="lazy" decoding="async">
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="section-padding why-us" id="why-us" data-nav-bg="dark">
        <div class="container">
            <div class="why-us-grid">
                <div class="why-us-content">
                    <span class="why-us-kicker">{{ $site['why_kicker'] }}</span>
                    <h3>{{ $site['why_title'] }}</h3>
                    <p class="why-us-lead">{{ $site['why_lead'] }}</p>
                    <div class="why-list">
                        <div class="why-item">
                            <span class="why-item-icon" aria-hidden="true"><i class="fa-solid fa-bolt"></i></span>
                            <span>{{ $site['why_item_1'] }}</span>
                        </div>
                        <div class="why-item">
                            <span class="why-item-icon" aria-hidden="true"><i class="fa-solid fa-users-gear"></i></span>
                            <span>{{ $site['why_item_2'] }}</span>
                        </div>
                        <div class="why-item">
                            <span class="why-item-icon" aria-hidden="true"><i class="fa-solid fa-toolbox"></i></span>
                            <span>{{ $site['why_item_3'] }}</span>
                        </div>
                        <div class="why-item">
                            <span class="why-item-icon" aria-hidden="true"><i class="fa-solid fa-clipboard-list"></i></span>
                            <span>{{ $site['why_item_4'] }}</span>
                        </div>
                        <div class="why-item">
                            <span class="why-item-icon" aria-hidden="true"><i class="fa-solid fa-tags"></i></span>
                            <span>{{ $site['why_item_5'] }}</span>
                        </div>
                        <div class="why-item">
                            <span class="why-item-icon" aria-hidden="true"><i class="fa-solid fa-shield-halved"></i></span>
                            <span>{{ $site['why_item_6'] }}</span>
                        </div>
                    </div>
                </div>

                <div class="why-us-image-card why-us-photo">
                    <img src="{{ asset('images/why-us.jpg') }}" alt="{{ __('site.why_photo_alt') }}" loading="lazy" decoding="async">
                    <div class="why-us-caption">
                        <h4>{{ $site['why_caption_title'] }}</h4>
                        <p>{{ $site['why_caption_text'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="section-padding contact-section" id="contact">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">{{ __('site.contact.title') }}</h2>
            </div>
            <p class="contact-intro">{{ $site['contact_intro'] }}</p>

            <div class="contact-grid">
                <div class="contact-info-stack">
                    <a class="contact-info-item is-phone" href="tel:{{ $site['phone'] }}">
                        <span class="contact-info-icon" aria-hidden="true"><i class="fa-solid fa-phone"></i></span>
                        <div class="contact-info-text">
                            <span class="contact-info-label">{{ __('site.contact.phone') }}</span>
                            <strong class="ltr">{{ $site['phone'] }}</strong>
                        </div>
                    </a>
                    <a class="contact-info-item is-email" href="mailto:{{ $site['email'] }}">
                        <span class="contact-info-icon" aria-hidden="true"><i class="fa-solid fa-envelope"></i></span>
                        <div class="contact-info-text">
                            <span class="contact-info-label">{{ __('site.contact.email') }}</span>
                            <strong class="ltr">{{ $site['email'] }}</strong>
                        </div>
                    </a>
                    <a class="contact-info-item is-whatsapp" href="https://wa.me/{{ $site['whatsapp'] }}" target="_blank" rel="noopener noreferrer">
                        <span class="contact-info-icon" aria-hidden="true"><i class="fa-brands fa-whatsapp"></i></span>
                        <div class="contact-info-text">
                            <span class="contact-info-label">{{ __('site.contact.whatsapp') }}</span>
                            <strong>{{ __('site.contact.whatsapp_direct') }}</strong>
                        </div>
                    </a>
                    <div class="contact-info-item is-hours">
                        <span class="contact-info-icon" aria-hidden="true"><i class="fa-solid fa-clock"></i></span>
                        <div class="contact-info-text">
                            <span class="contact-info-label">{{ __('site.contact.hours') }}</span>
                            <strong>{{ $site['hours'] }}</strong>
                        </div>
                    </div>
                </div>

                <form class="contact-form-card" id="contact-form" action="{{ route('leads.store') }}" method="POST">
                    @csrf
                    <h3>{{ __('site.contact.form_title') }}</h3>
                    <p>{{ __('site.contact.form_email_intro') }}</p>
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
                    <div class="form-field">
                        <label for="contact-service">{{ __('site.contact.service') }}</label>
                        <select id="contact-service" name="service_id" required>
                            <option value="" selected disabled>{{ __('site.contact.service_placeholder') }}</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}">{{ $service->displayTitle() }}</option>
                            @endforeach
                            <option value="general">{{ __('site.contact.general_inquiry') }}</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="contact-message">{{ __('site.contact.details') }}</label>
                        <textarea id="contact-message" name="message" required maxlength="1000" placeholder="{{ __('site.contact.details_placeholder') }}"></textarea>
                    </div>
                    <button type="submit" class="btn-primary" data-default-label="{{ __('site.contact.send_request') }}" data-loading-label="{{ __('site.contact.sending') }}">
                        <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                        <span>{{ __('site.contact.send_request') }}</span>
                    </button>
                    <p class="form-note" id="contact-form-note" role="status"></p>
                </form>
            </div>
        </div>
    </section>
@endsection
