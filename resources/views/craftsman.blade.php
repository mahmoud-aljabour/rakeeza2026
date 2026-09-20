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
                            <span class="craftsman-feature-icon">
                                <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                            </span>
                            {{ $site['craftsman_feature_1'] }}
                        </li>
                        <li>
                            <span class="craftsman-feature-icon">
                                <i class="fa-solid fa-users" aria-hidden="true"></i>
                            </span>
                            {{ $site['craftsman_feature_2'] }}
                        </li>
                        <li>
                            <span class="craftsman-feature-icon">
                                <i class="fa-solid fa-briefcase" aria-hidden="true"></i>
                            </span>
                            {{ __('site.craftsman.ongoing_work') }}
                        </li>
                    </ul>
                    <a class="contact-box-sm" href="tel:{{ $site['phone'] }}">
                        <i class="fa-solid fa-phone" aria-hidden="true"></i>
                        <div>
                            <span>{{ __('site.craftsman.inquire') }}</span>
                            <strong class="ltr">{{ $site['phone'] }}</strong>
                        </div>
                    </a>
                </aside>

                <form class="contact-form-card" id="craftsman-form" action="{{ route('craftsman.store') }}" method="POST">
                    @csrf
                    <h3>{{ __('site.craftsman.data_title') }}</h3>
                    <p>{{ __('site.craftsman.data_intro') }}</p>
                    <div class="hp-field" aria-hidden="true">
                        <label for="craftsman-website">Website</label>
                        <input id="craftsman-website" name="website" type="text" tabindex="-1" autocomplete="off">
                    </div>
                    <div class="form-row">
                        <div class="form-field">
                            <label for="craftsman-first-name">{{ __('site.craftsman.first_name') }}</label>
                            <input id="craftsman-first-name" name="first_name" type="text" required autocomplete="given-name" placeholder="{{ __('site.craftsman.first_name_placeholder') }}">
                        </div>
                        <div class="form-field">
                            <label for="craftsman-father-name">{{ __('site.craftsman.father_name') }}</label>
                            <input id="craftsman-father-name" name="father_name" type="text" required autocomplete="additional-name" placeholder="{{ __('site.craftsman.father_name_placeholder') }}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-field">
                            <label for="craftsman-grandfather-name">{{ __('site.craftsman.grandfather_name') }}</label>
                            <input id="craftsman-grandfather-name" name="grandfather_name" type="text" required autocomplete="additional-name" placeholder="{{ __('site.craftsman.grandfather_name_placeholder') }}">
                        </div>
                        <div class="form-field">
                            <label for="craftsman-family-name">{{ __('site.craftsman.family_name') }}</label>
                            <input id="craftsman-family-name" name="family_name" type="text" required autocomplete="family-name" placeholder="{{ __('site.craftsman.family_name_placeholder') }}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-field">
                            <label for="craftsman-national-id">{{ __('site.craftsman.national_id') }}</label>
                            <input id="craftsman-national-id" name="national_id" type="text" required inputmode="numeric" pattern="[0-9]{9}" maxlength="9" autocomplete="off" placeholder="{{ __('site.craftsman.national_id_placeholder') }}" dir="ltr">
                        </div>
                        <div class="form-field">
                            <label for="craftsman-phone">{{ __('site.craftsman.phone') }}</label>
                            <input id="craftsman-phone" name="phone" type="tel" required autocomplete="tel" inputmode="tel" placeholder="05xxxxxxxx" dir="ltr">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-field">
                            <label for="craftsman-city">{{ __('site.craftsman.city') }}</label>
                            <select id="craftsman-city" name="city" required>
                                <option value="" selected disabled>{{ __('site.craftsman.city_placeholder') }}</option>
                                @foreach ($cities as $city)
                                    <option value="{{ $city }}">{{ $city }}</option>
                                @endforeach
                            </select>
                        </div>
                        <fieldset class="form-field">
                            <legend class="form-field-heading">
                                <span>{{ __('site.craftsman.specialty') }}</span>
                                <span class="form-field-hint">{{ __('site.craftsman.specialty_multiple_hint') }}</span>
                            </legend>
                            <details class="service-multiselect" id="craftsman-specialty">
                                <summary>
                                    <span
                                        class="service-multiselect-label"
                                        data-placeholder="{{ __('site.craftsman.specialty_placeholder') }}"
                                        data-count-label="{{ __('site.craftsman.specialty_selected_count') }}"
                                    >{{ __('site.craftsman.specialty_placeholder') }}</span>
                                    <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                                </summary>
                                <div class="service-choice-grid">
                                @foreach ($specialties as $value => $label)
                                        <label class="service-choice">
                                            <input type="checkbox" name="specialties[]" value="{{ $value }}">
                                            <span>{{ $label }}</span>
                                        </label>
                                @endforeach
                                </div>
                            </details>
                        </fieldset>
                    </div>

                    <div class="form-row">
                        <div class="form-field">
                            <label for="craftsman-experience">{{ __('site.craftsman.experience') }}</label>
                            <input id="craftsman-experience" name="experience_years" type="number" min="0" max="60" required value="0" dir="ltr">
                        </div>
                        <fieldset class="form-field">
                            <legend>{{ __('site.craftsman.has_tools') }}</legend>
                            <div class="binary-choice">
                                <label class="binary-choice-label">
                                    <input name="has_tools" type="radio" value="1" required>
                                    <span>{{ __('site.craftsman.tools_yes') }}</span>
                                </label>
                                <label class="binary-choice-label">
                                    <input name="has_tools" type="radio" value="0" required>
                                    <span>{{ __('site.craftsman.tools_no') }}</span>
                                </label>
                            </div>
                        </fieldset>
                    </div>

                    <div class="form-field">
                        <label for="craftsman-bio">{{ __('site.craftsman.bio') }}</label>
                        <textarea id="craftsman-bio" name="bio" placeholder="{{ __('site.craftsman.bio_placeholder') }}"></textarea>
                    </div>

                    <button
                        type="submit"
                        class="btn-primary"
                        data-default-label="{{ __('site.craftsman.submit') }}"
                        data-loading-label="{{ __('site.contact.sending') }}"
                    >
                        <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                        <span>{{ __('site.craftsman.submit') }}</span>
                    </button>
                    <p class="form-note" id="craftsman-form-note" role="status"></p>
                </form>
            </div>
        </section>
@endsection
