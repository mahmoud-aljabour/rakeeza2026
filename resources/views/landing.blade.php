<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0a3356">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} | حلول متكاملة لترميم وتأهيل المنازل والمنشآت</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    <link rel="preload" as="image" href="{{ asset('images/hero-bg-3.jpg') }}">
    <!-- Google Fonts: Cairo — خط عربي واحد للعناوين والنصوص -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/landing.css', 'resources/js/landing.js'])
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.7/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.7/dist/ScrollTrigger.min.js"></script>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    
</head>
<body>

    <a class="skip-link" href="#main">تخطي إلى المحتوى</a>

    <div class="scroll-progress" aria-hidden="true"></div>

    <!-- Top Info Bar -->
    <div class="top-bar">
        <div class="top-bar-contact">
            <a href="tel:{{ $site['phone'] }}"><i class="fa-solid fa-phone"></i> {{ $site['phone'] }}</a>
            <a href="mailto:{{ $site['email'] }}"><i class="fa-solid fa-envelope"></i> {{ $site['email'] }}</a>
        </div>
        <div class="top-bar-actions">
            <a href="{{ route('craftsman.create') }}" class="btn-craftsman-link"><i class="fa-solid fa-user-gear"></i> انضم كحرفي</a>
        </div>
    </div>

    <!-- Main Navigation Header (Glassmorphism Pill Design) -->
    <header>
        <div class="nav-container">
            <div class="logo-area">
                <a href="#home" aria-label="ركيزة - للتشطيب والصيانة">
                    <img src="{{ asset('images/logo.png') }}" alt="ركيزة للتشطيب والصيانة" width="85" height="54" decoding="async">
                </a>
            </div>

            <nav class="nav-menu" id="main-nav">
                <a href="#home">الرئيسية</a>
                <a href="#about">من نحن</a>
                <a href="#services">خدماتنا</a>
                <a href="#projects">أعمالنا السابقة</a>
                <a href="#contact">اتصل بنا</a>
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
                <h1>{{ $site['hero_title'] }}<span>{{ $site['hero_highlight'] }}</span></h1>
                <p>{{ $site['hero_text'] }}</p>
                <div class="hero-btns">
                    <a href="tel:{{ $site['phone'] }}" class="btn-primary">
                        <i class="fa-solid fa-phone-volume"></i>
                        تواصل معنا لطلب الخدمة
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
                    <h4>خدمات السباكة</h4>
                    <p>صيانة وإصلاح موثوق</p>
                </div>
            </div>
            <div class="feature-box">
                <div class="feature-icon"><i class="fa-solid fa-compass-drafting"></i></div>
                <div>
                    <h4>الهندسة المعمارية</h4>
                    <p>استشارات وتصميم متخصص</p>
                </div>
            </div>
            <div class="feature-box">
                <div class="feature-icon"><i class="fa-solid fa-trowel-bricks"></i></div>
                <div>
                    <h4>تسقيف وترميم</h4>
                    <p>حلول بناء وتأهيل متينة</p>
                </div>
            </div>
            <div class="feature-box">
                <div class="feature-icon"><i class="fa-solid fa-helmet-safety"></i></div>
                <div>
                    <h4>أعمال البناء</h4>
                    <p>التزام بمعايير السلامة</p>
                </div>
            </div>
        </div>
    </div>

    <!-- About Section -->
    <section class="section-padding" id="about">
        <div class="container">
            <div class="about-grid">
                <div class="about-text">
                    <h3>من نحن</h3>
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
                                <span>اطلب عرض سعر سريع:</span>
                                <strong class="ltr">{{ $site['phone'] }}</strong>
                            </div>
                        </a>
                        <a class="contact-box-sm" href="mailto:{{ $site['email'] }}">
                            <i class="fa-solid fa-envelope-open-text" aria-hidden="true"></i>
                            <div>
                                <span>البريد الإلكتروني:</span>
                                <strong class="ltr" style="font-size:0.85rem;">{{ $site['email'] }}</strong>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="why-us-image-card about-photo-card">
                    <img src="{{ asset('images/about-team.jpg') }}" alt="فني ركيزة للتشطيب والصيانة" loading="lazy" decoding="async">
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="section-padding" id="services">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">خدماتنا الشاملة</h2>
            </div>

            <div class="services-grid">
                @foreach ($services as $service)
                    <article class="service-card">
                        <a href="{{ route('services.show', $service) }}" class="service-image">
                            <img src="{{ $service->imageUrl() }}" alt="{{ $service->title }}" loading="lazy" decoding="async">
                        </a>
                        <div class="service-body">
                            <div>
                                <div class="service-header">
                                    <h3 class="service-title">
                                        <a href="{{ route('services.show', $service) }}">{{ $service->title }}</a>
                                    </h3>
                                </div>
                                <p class="service-desc">{{ $service->description }}</p>
                            </div>
                            <a href="{{ route('services.show', $service) }}" class="service-btn">
                                عرض التفاصيل
                                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <!-- Our Vision -->
            <div class="vision-card">
                <h3><i class="fa-solid fa-eye" style="color: var(--accent);"></i> رؤيتنا</h3>
                <p>{{ $site['vision_text'] }}</p>
            </div>
        </div>
    </section>

    <!-- Latest Projects -->
    <section class="section-padding" id="projects">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">اطلع على أحدث أعمالنا</h2>
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
                        <h4>انضم إلى فريق ركيزة اليوم</h4>
                        <a href="{{ route('craftsman.create') }}" class="btn-primary craftsman-join-btn">
                            <i class="fa-solid fa-user-plus"></i>
                            سجّل كحرفي الآن
                        </a>
                    </div>
                </div>
                <div class="craftsman-action">
                    <img src="{{ asset('images/craftsman.jpg') }}" alt="حرفي ركيزة أثناء العمل" loading="lazy" decoding="async">
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
                            <i class="fa-solid fa-bolt"></i>
                            {{ $site['why_item_1'] }}
                        </div>
                        <div class="why-item">
                            <i class="fa-solid fa-users-gear"></i>
                            {{ $site['why_item_2'] }}
                        </div>
                        <div class="why-item">
                            <i class="fa-solid fa-toolbox"></i>
                            {{ $site['why_item_3'] }}
                        </div>
                        <div class="why-item">
                            <i class="fa-solid fa-clipboard-list"></i>
                            {{ $site['why_item_4'] }}
                        </div>
                        <div class="why-item">
                            <i class="fa-solid fa-tags"></i>
                            {{ $site['why_item_5'] }}
                        </div>
                        <div class="why-item">
                            <i class="fa-solid fa-shield-virus"></i>
                            {{ $site['why_item_6'] }}
                        </div>
                    </div>
                </div>

                <div class="why-us-image-card why-us-photo">
                    <img src="{{ asset('images/why-us.jpg') }}" alt="تخطيط هندسي واحترافية في التنفيذ" loading="lazy" decoding="async">
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
                <h2 class="section-title">اتصل بنا</h2>
            </div>
            <p class="contact-intro">{{ $site['contact_intro'] }}</p>

            <div class="contact-grid">
                <div class="contact-info-stack">
                    <a class="contact-info-item is-phone" href="tel:{{ $site['phone'] }}">
                        <span class="contact-info-icon" aria-hidden="true"><i class="fa-solid fa-phone"></i></span>
                        <div class="contact-info-text">
                            <span class="contact-info-label">الهاتف</span>
                            <strong class="ltr">{{ $site['phone'] }}</strong>
                        </div>
                    </a>
                    <a class="contact-info-item is-email" href="mailto:{{ $site['email'] }}">
                        <span class="contact-info-icon" aria-hidden="true"><i class="fa-solid fa-envelope"></i></span>
                        <div class="contact-info-text">
                            <span class="contact-info-label">البريد الإلكتروني</span>
                            <strong class="ltr">{{ $site['email'] }}</strong>
                        </div>
                    </a>
                    <a class="contact-info-item is-whatsapp" href="https://wa.me/{{ $site['whatsapp'] }}" target="_blank" rel="noopener noreferrer">
                        <span class="contact-info-icon" aria-hidden="true"><i class="fa-brands fa-whatsapp"></i></span>
                        <div class="contact-info-text">
                            <span class="contact-info-label">واتساب</span>
                            <strong>تواصل مباشر مع الفريق</strong>
                        </div>
                    </a>
                    <div class="contact-info-item is-hours">
                        <span class="contact-info-icon" aria-hidden="true"><i class="fa-solid fa-clock"></i></span>
                        <div class="contact-info-text">
                            <span class="contact-info-label">ساعات العمل</span>
                            <strong>{{ $site['hours'] }}</strong>
                        </div>
                    </div>
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
                            <option value="" selected disabled>اختر الخدمة المطلوبة</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}">{{ $service->title }}</option>
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
            </div>
        </div>
    </section>

    </main>

    <!-- Footer -->
    <footer data-nav-bg="dark">
        <div class="footer-grid">
            <div class="footer-about">
                <a href="#home" class="footer-logo" aria-label="ركيزة - للتشطيب والصيانة">
                    <img src="{{ asset('images/logo.png') }}" alt="ركيزة للتشطيب والصيانة" width="120" height="64" decoding="async">
                </a>
                <p>{{ $site['footer_about'] }}</p>
            </div>

            <div>
                <h4 class="footer-title">روابط سريعة</h4>
                <ul class="footer-links">
                    <li><a href="#home">الرئيسية</a></li>
                    <li><a href="#about">من نحن</a></li>
                    <li><a href="#services">خدماتنا</a></li>
                    <li><a href="#projects">أعمالنا السابقة</a></li>
                    <li><a href="{{ route('craftsman.create') }}">تسجيل الحرفيين</a></li>
                    <li><a href="#contact">اتصل بنا</a></li>
                </ul>
            </div>

            <div>
                <h4 class="footer-title">خدماتنا</h4>
                <ul class="footer-links">
                    @foreach ($services->take(4) as $service)
                        <li><a href="{{ route('services.show', $service) }}">{{ $service->title }}</a></li>
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

        <div class="copyright">
            جميع الحقوق محفوظة &copy; 2026 - شركة ركيزة للخدمات المتكاملة
        </div>
    </footer>

    <!-- Floating WhatsApp Call Button -->
    <a href="https://wa.me/{{ $site['whatsapp'] }}" class="floating-whatsapp" target="_blank" rel="noopener noreferrer" aria-label="تواصل عبر واتساب">
        <i class="fa-brands fa-whatsapp"></i>
    </a>

    
    </body>
</html>
