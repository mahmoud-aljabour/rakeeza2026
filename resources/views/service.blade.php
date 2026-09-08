<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0a3356">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $service->title }} | {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/landing.css', 'resources/js/landing.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="service-page">
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
                <a href="{{ url('/#services') }}" class="is-active" aria-current="page">خدماتنا</a>
                <a href="{{ url('/#projects') }}">أعمالنا السابقة</a>
                <a href="{{ url('/#contact') }}">اتصل بنا</a>
            </nav>

            <div class="header-actions">
                <a href="{{ route('craftsman.create') }}" class="nav-cta">
                    <span class="nav-cta-full">انضم إلينا كحرفي</span>
                    <span class="nav-cta-short">انضم كحرفي</span>
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
        <div class="service-shell">
            <div class="container">
                <nav class="service-crumb" aria-label="مسار الصفحة">
                    <a href="{{ route('landing') }}">الرئيسية</a>
                    <span aria-hidden="true">/</span>
                    <a href="{{ url('/#services') }}">خدماتنا</a>
                    <span aria-hidden="true">/</span>
                    <span>{{ $service->title }}</span>
                </nav>

                <section class="service-intro">
                    <div class="service-intro-visual">
                        <img src="{{ $service->imageUrl() }}" alt="{{ $service->title }}">
                        <span class="service-intro-badge">خدمات ركيزة</span>
                    </div>
                    <div class="service-intro-copy">
                        <p class="service-kicker">تفاصيل الخدمة</p>
                        <h1>{{ $service->title }}</h1>
                        <p class="service-lead">{{ $service->description }}</p>
                        <div class="service-facts">
                            <div class="service-fact">
                                <strong>{{ $projects->count() }}</strong>
                                <span>مشروع منفذ</span>
                            </div>
                            <div class="service-fact">
                                <strong>سريع</strong>
                                <span>تنفيذ بجودة عالية</span>
                            </div>
                            <div class="service-fact">
                                <strong>مباشر</strong>
                                <span>إشراف ميداني</span>
                            </div>
                        </div>
                        <div class="service-cta-row">
                            <a href="tel:{{ $site['phone'] }}" class="btn-primary">
                                <i class="fa-solid fa-phone-volume"></i>
                                اطلب عرض سعر
                            </a>
                            <a href="#contact" class="btn-ghost">
                                <i class="fa-solid fa-paper-plane"></i>
                                أرسل طلبك من هنا
                            </a>
                        </div>
                    </div>
                </section>
            </div>

            <section class="service-works-band">
                <div class="container">
                    <div class="service-works-heading">
                        <p class="service-kicker">معرض الأعمال</p>
                        <h2>مشاريع تم تنفيذها</h2>
                    </div>

                    <div class="service-works-list">
                        @forelse ($projects as $project)
                            <x-service-work :project="$project" :index="$loop->iteration" />
                        @empty
                            <div class="empty-projects">
                                <i class="fa-solid fa-images" aria-hidden="true"></i>
                                <p>لا توجد مشاريع معروضة لهذه الخدمة بعد.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>

            <div class="container">
                <section class="service-quote" id="contact">
                    <div class="service-quote-copy">
                        <p class="service-kicker is-light">تواصل مباشر</p>
                        <h2>اطلب عرض سعر</h2>
                        <p>أخبرنا عن احتياجك وسنجهّز رسالة واتساب جاهزة لإرسالها إلى فريق ركيزة.</p>
                        <a href="tel:{{ $site['phone'] }}" class="service-quote-phone">
                            <i class="fa-solid fa-phone"></i>
                            <span dir="ltr">{{ $site['phone'] }}</span>
                        </a>
                    </div>

                    <form class="contact-form-card" id="contact-form" action="{{ route('leads.store') }}" method="POST" data-whatsapp="{{ $site['whatsapp'] }}">
                        @csrf
                        <h3>أرسل طلبك</h3>
                        <p>عبّئ البيانات وسنفتح واتساب برسالة جاهزة لإرسالها مباشرة.</p>
                        <div class="form-row">
                            <div class="form-field">
                                <label for="contact-name">الاسم</label>
                                <input id="contact-name" name="name" type="text" required autocomplete="name" placeholder="اسمك الكامل">
                            </div>
                            <div class="form-field">
                                <label for="contact-phone">رقم الجوال</label>
                                <input id="contact-phone" name="phone" type="tel" required autocomplete="tel" inputmode="tel" placeholder="05xxxxxxxx" dir="ltr">
                            </div>
                        </div>
                        <div class="form-field">
                            <label for="contact-service">نوع الخدمة</label>
                            <select id="contact-service" name="service_id" required>
                                @foreach ($services as $item)
                                    <option value="{{ $item->id }}" @selected($item->id === $service->id)>{{ $item->title }}</option>
                                @endforeach
                                <option value="general">استفسار عام</option>
                            </select>
                        </div>
                        <div class="form-field">
                            <label for="contact-message">تفاصيل الطلب</label>
                            <textarea id="contact-message" name="message" required placeholder="اكتب تفاصيل العمل أو الموقع أو أي ملاحظات..."></textarea>
                        </div>
                        <button type="submit" class="btn-primary">
                            <i class="fa-brands fa-whatsapp"></i>
                            إرسال عبر واتساب
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
                            <p class="service-kicker">تصفح المزيد</p>
                            <h2>خدمات أخرى</h2>
                        </div>
                        <div class="service-related-grid">
                            @foreach ($related as $item)
                                <a href="{{ route('services.show', $item) }}" class="service-related-card">
                                    <img src="{{ $item->imageUrl() }}" alt="{{ $item->title }}" loading="lazy" decoding="async">
                                    <strong>{{ $item->title }}</strong>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>
        </div>
    </main>

    <footer data-nav-bg="dark">
        <div class="footer-grid">
            <div class="footer-about">
                <a href="{{ route('landing') }}" class="footer-logo" aria-label="ركيزة - للتشطيب والصيانة">
                    <img src="{{ asset('images/logo.png') }}" alt="ركيزة للتشطيب والصيانة" width="120" height="64" decoding="async">
                </a>
                <p>{{ $site['footer_about'] }}</p>
            </div>
            <div>
                <h4 class="footer-title">روابط سريعة</h4>
                <ul class="footer-links">
                    <li><a href="{{ url('/#home') }}">الرئيسية</a></li>
                    <li><a href="{{ url('/#services') }}">خدماتنا</a></li>
                    <li><a href="{{ url('/#projects') }}">أعمالنا السابقة</a></li>
                    <li><a href="{{ route('craftsman.create') }}">تسجيل الحرفيين</a></li>
                    <li><a href="{{ url('/#contact') }}">اتصل بنا</a></li>
                </ul>
            </div>
            <div>
                <h4 class="footer-title">خدماتنا</h4>
                <ul class="footer-links">
                    @foreach ($services->take(4) as $item)
                        <li><a href="{{ route('services.show', $item) }}">{{ $item->title }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h4 class="footer-title">تواصل معنا</h4>
                <ul class="footer-contact">
                    <li><a href="tel:{{ $site['phone'] }}"><i class="fa-solid fa-phone" aria-hidden="true"></i> <span dir="ltr">{{ $site['phone'] }}</span></a></li>
                    <li><a href="mailto:{{ $site['email'] }}"><i class="fa-solid fa-envelope" aria-hidden="true"></i> <span dir="ltr">{{ $site['email'] }}</span></a></li>
                    <li><i class="fa-solid fa-clock" aria-hidden="true"></i> <span>{{ $site['hours'] }}</span></li>
                </ul>
            </div>
        </div>
        <div class="copyright">جميع الحقوق محفوظة &copy; 2026 - شركة ركيزة للخدمات المتكاملة</div>
    </footer>

    <a href="https://wa.me/{{ $site['whatsapp'] }}" class="floating-whatsapp" target="_blank" rel="noopener noreferrer" aria-label="تواصل عبر واتساب">
        <i class="fa-brands fa-whatsapp"></i>
    </a>
</body>
</html>
