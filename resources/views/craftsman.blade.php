@extends('layouts.site')

@section('title', __('site.meta.craftsman_title', ['app' => config('app.name')]))

@section('content')
        <section class="page-hero" data-nav-bg="dark">
            <div class="page-hero-bg" style="background-image: url('{{ asset('images/craftsman.jpg') }}');"></div>
            <div class="container page-hero-content">
                <span class="craftsman-kicker">{{ $site['craftsman_kicker'] }}</span>
                <h1>{{ __('site.craftsman.form_title') }}</h1>
                <p>{{ $site['craftsman_text'] }}</p>
            </div>
        </section>

        <section class="section-padding">
            <div class="container craftsman-register-grid">
                <aside class="craftsman-register-info">
                    <h2>{{ __('site.craftsman.why_join') }}</h2>
                    <ul class="craftsman-features">
                        <li>
                            <span class="craftsman-feature-icon"><i class="fa-solid fa-circle-check"></i></span>
                            {{ $site['craftsman_feature_1'] }}
                        </li>
                        <li>
                            <span class="craftsman-feature-icon"><i class="fa-solid fa-handshake"></i></span>
                            {{ $site['craftsman_feature_2'] }}
                        </li>
                        <li>
                            <span class="craftsman-feature-icon"><i class="fa-solid fa-briefcase"></i></span>
                            {{ __('site.craftsman.ongoing_work') }}
                        </li>
                    </ul>
                    <a class="contact-box-sm" href="tel:{{ $site['phone'] }}">
                        <i class="fa-solid fa-phone-flip" aria-hidden="true"></i>
                        <div>
                            <span>{{ __('site.craftsman.inquire') }}</span>
                            <strong class="ltr">{{ $site['phone'] }}</strong>
                        </div>
                    </a>
                </aside>

                <form class="contact-form-card" id="craftsman-form" action="{{ route('craftsman.store') }}" method="POST" data-whatsapp="{{ $site['whatsapp'] }}">
                    @csrf
                    <h3>{{ __('site.craftsman.data_title') }}</h3>
                    <p>{{ __('site.craftsman.data_intro') }}</p>

                    <div class="form-row">
                        <div class="form-field">
                            <label for="craftsman-name">{{ __('site.craftsman.full_name') }}</label>
                            <input id="craftsman-name" name="name" type="text" required autocomplete="name" placeholder="{{ __('site.craftsman.full_name_placeholder') }}">
                        </div>
                        <div class="form-field">
                            <label for="craftsman-phone">{{ __('site.craftsman.phone') }}</label>
                            <input id="craftsman-phone" name="phone" type="tel" required autocomplete="tel" inputmode="tel" placeholder="05xxxxxxxx" dir="ltr">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-field">
                            <label for="craftsman-city">{{ __('site.craftsman.city') }}</label>
                            <input id="craftsman-city" name="city" type="text" required placeholder="{{ __('site.craftsman.city_placeholder') }}">
                        </div>
                        <div class="form-field">
                            <label for="craftsman-specialty">{{ __('site.craftsman.specialty') }}</label>
                            <select id="craftsman-specialty" name="specialty" required>
                                <option value="" selected disabled>{{ __('site.craftsman.specialty_placeholder') }}</option>
                                @foreach ($specialties as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-field">
                            <label for="craftsman-experience">{{ __('site.craftsman.experience') }}</label>
                            <input id="craftsman-experience" name="experience_years" type="number" min="0" max="60" required value="0" dir="ltr">
                        </div>
                        <div class="form-field form-field-check">
                            <label class="check-label">
                                <input id="craftsman-tools" name="has_tools" type="checkbox" value="1">
                                {{ __('site.craftsman.has_tools') }}
                            </label>
                        </div>
                    </div>

                    <div class="form-field">
                        <label for="craftsman-bio">{{ __('site.craftsman.bio') }}</label>
                        <textarea id="craftsman-bio" name="bio" placeholder="{{ __('site.craftsman.bio_placeholder') }}"></textarea>
                    </div>

                    <button type="submit" class="btn-primary btn-whatsapp">
                        <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                        {{ __('site.craftsman.submit_whatsapp') }}
                    </button>
                    <p class="form-note" id="craftsman-form-note" role="status"></p>
                </form>
            </div>
        </section>
@endsection
