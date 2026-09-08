<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0a3356">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>تسجيل الحرفيين | {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/landing.css', 'resources/js/landing.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <a class="skip-link" href="#main">تخطي إلى المحتوى</a>
    <div class="scroll-progress" aria-hidden="true"></div>

    <header>
        <div class="nav-container">
            <div class="logo-area">
                <a href="{{ route('landing') }}" aria-label="ركيزة - للتشطيب والصيانة">
                    <img src="{{ asset('images/logo.png') }}" alt="ركيزة للتشطيب والصيانة" width="85" height="54" decoding="async">
                </a>
            </div>

            <nav class="nav-menu" id="main-nav">
                <a href="{{ url('/#home') }}">الرئيسية</a>
                <a href="{{ url('/#about') }}">من نحن</a>
                <a href="{{ url('/#services') }}">خدماتنا</a>
                <a href="{{ url('/#projects') }}">أعمالنا السابقة</a>
                <a href="{{ url('/#contact') }}">اتصل بنا</a>
            </nav>

            <div class="header-actions">
                <a href="{{ route('craftsman.create') }}" class="nav-cta is-active">
                    <span class="nav-cta-full">تسجيل الحرفيين</span>
                    <span class="nav-cta-short">تسجيل</span>
                </a>
                <button type="button" class="menu-toggle" aria-label="فتح القائمة" aria-expanded="false" aria-controls="main-nav">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </header>
    <div class="nav-overlay" id="nav-overlay" aria-hidden="true"></div>

    <main id="main">
        <section class="page-hero" data-nav-bg="dark">
            <div class="page-hero-bg" style="background-image: url('{{ asset('images/craftsman.jpg') }}');"></div>
            <div class="container page-hero-content">
                <span class="craftsman-kicker">{{ $site['craftsman_kicker'] }}</span>
                <h1>نموذج تسجيل الحرفيين</h1>
                <p>{{ $site['craftsman_text'] }}</p>
            </div>
        </section>

        <section class="section-padding">
            <div class="container craftsman-register-grid">
                <aside class="craftsman-register-info">
                    <h2>لماذا تنضم إلى ركيزة؟</h2>
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
                            فرص عمل مستمرة في خدمات المنازل والمنشآت.
                        </li>
                    </ul>
                    <a class="contact-box-sm" href="tel:{{ $site['phone'] }}">
                        <i class="fa-solid fa-phone-flip" aria-hidden="true"></i>
                        <div>
                            <span>للاستفسار قبل التسجيل:</span>
                            <strong class="ltr">{{ $site['phone'] }}</strong>
                        </div>
                    </a>
                </aside>

                <form class="contact-form-card" id="craftsman-form" action="{{ route('craftsman.store') }}" method="POST" data-whatsapp="{{ $site['whatsapp'] }}">
                    @csrf
                    <h3>بيانات الحرفي</h3>
                    <p>عبّئ النموذج وسنراجع طلبك ونتواصل معك لاستكمال الانضمام.</p>

                    <div class="form-row">
                        <div class="form-field">
                            <label for="craftsman-name">الاسم الكامل</label>
                            <input id="craftsman-name" name="name" type="text" required autocomplete="name" placeholder="اسمك الرباعي">
                        </div>
                        <div class="form-field">
                            <label for="craftsman-phone">رقم الجوال</label>
                            <input id="craftsman-phone" name="phone" type="tel" required autocomplete="tel" inputmode="tel" placeholder="05xxxxxxxx" dir="ltr">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-field">
                            <label for="craftsman-city">المدينة / المنطقة</label>
                            <input id="craftsman-city" name="city" type="text" required placeholder="مثال: غزة، خان يونس، رفح">
                        </div>
                        <div class="form-field">
                            <label for="craftsman-specialty">التخصص</label>
                            <select id="craftsman-specialty" name="specialty" required>
                                <option value="" selected disabled>اختر تخصصك</option>
                                @foreach ($specialties as $specialty)
                                    <option value="{{ $specialty }}">{{ $specialty }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-field">
                            <label for="craftsman-experience">سنوات الخبرة</label>
                            <input id="craftsman-experience" name="experience_years" type="number" min="0" max="60" required value="0" dir="ltr">
                        </div>
                        <div class="form-field form-field-check">
                            <label class="check-label">
                                <input id="craftsman-tools" name="has_tools" type="checkbox" value="1">
                                لدي معدات وأدوات عمل خاصة
                            </label>
                        </div>
                    </div>

                    <div class="form-field">
                        <label for="craftsman-bio">نبذة عن خبرتك</label>
                        <textarea id="craftsman-bio" name="bio" placeholder="اكتب نوع الأعمال التي تتقنها، ومناطق عملك، وأي ملاحظات..."></textarea>
                    </div>

                    <button type="submit" class="btn-primary">
                        <i class="fa-solid fa-user-plus"></i>
                        إرسال طلب الانضمام
                    </button>
                    <p class="form-note" id="craftsman-form-note" role="status"></p>
                </form>
            </div>
        </section>
    </main>

    <footer data-nav-bg="dark">
        <div class="footer-grid">
            <div class="footer-about">
                <a href="{{ route('landing') }}" class="footer-logo" aria-label="ركيزة">
                    <img src="{{ asset('images/logo.png') }}" alt="ركيزة" width="120" height="64" decoding="async">
                </a>
                <p>{{ $site['footer_about'] }}</p>
            </div>
            <div>
                <h4 class="footer-title">روابط سريعة</h4>
                <ul class="footer-links">
                    <li><a href="{{ url('/#home') }}">الرئيسية</a></li>
                    <li><a href="{{ url('/#services') }}">خدماتنا</a></li>
                    <li><a href="{{ route('craftsman.create') }}">تسجيل الحرفيين</a></li>
                    <li><a href="{{ url('/#contact') }}">اتصل بنا</a></li>
                </ul>
            </div>
            <div>
                <h4 class="footer-title">تواصل معنا</h4>
                <ul class="footer-contact">
                    <li><a href="tel:{{ $site['phone'] }}"><i class="fa-solid fa-phone" aria-hidden="true"></i> <span dir="ltr">{{ $site['phone'] }}</span></a></li>
                    <li><a href="mailto:{{ $site['email'] }}"><i class="fa-solid fa-envelope" aria-hidden="true"></i> <span dir="ltr">{{ $site['email'] }}</span></a></li>
                </ul>
            </div>
        </div>
        <div class="copyright">جميع الحقوق محفوظة &copy; 2026 - شركة ركيزة للخدمات المتكاملة</div>
    </footer>
</body>
</html>
