(function () {
            var header = document.querySelector('header');
            var toggle = document.querySelector('.menu-toggle');
            var overlay = document.getElementById('nav-overlay');
            var links = document.querySelectorAll('#main-nav a');
            if (!header || !toggle) return;

            var lastFocus = null;
            var i18n = (window.Rakeeza && window.Rakeeza.i18n) || {};

            function closeMenu() {
                var wasOpen = header.classList.contains('menu-open');
                document.body.classList.remove('nav-open');
                header.classList.remove('menu-open');
                toggle.setAttribute('aria-expanded', 'false');
                toggle.setAttribute('aria-label', i18n.openMenu || 'فتح القائمة');
                if (overlay) overlay.setAttribute('aria-hidden', 'true');
                window.dispatchEvent(new Event('nav-theme-refresh'));
                if (wasOpen && lastFocus && typeof lastFocus.focus === 'function') lastFocus.focus();
            }

            function openMenu() {
                lastFocus = document.activeElement;
                document.body.classList.add('nav-open');
                header.classList.add('menu-open');
                toggle.setAttribute('aria-expanded', 'true');
                toggle.setAttribute('aria-label', i18n.closeMenu || 'إغلاق القائمة');
                if (overlay) overlay.setAttribute('aria-hidden', 'false');
                if (links[0]) links[0].focus();
            }

            toggle.addEventListener('click', function () {
                if (header.classList.contains('menu-open')) closeMenu();
                else openMenu();
            });

            if (overlay) overlay.addEventListener('click', closeMenu);
            links.forEach(function (link) {
                link.addEventListener('click', closeMenu);
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeMenu();
            });
            window.addEventListener('resize', function () {
                if (window.innerWidth >= 1025) closeMenu();
            });
        })();

        (function () {
            var links = Array.prototype.slice.call(document.querySelectorAll('#main-nav a[href^="#"], .nav-cta'));
            if (!links.length) return;

            var items = links.map(function (link) {
                var id = link.getAttribute('href').slice(1);
                return { link: link, el: document.getElementById(id) };
            }).filter(function (item) {
                return item.el;
            }).sort(function (a, b) {
                return a.el.offsetTop - b.el.offsetTop;
            });

            function setActive(id) {
                links.forEach(function (link) {
                    var on = link.getAttribute('href') === '#' + id;
                    link.classList.toggle('is-active', on);
                    if (on) link.setAttribute('aria-current', 'page');
                    else link.removeAttribute('aria-current');
                });
            }

            function updateActive() {
                var marker = window.scrollY + 140;
                var current = items[0] ? items[0].el.id : 'home';
                for (var i = 0; i < items.length; i++) {
                    if (items[i].el.offsetTop <= marker) current = items[i].el.id;
                }
                setActive(current);
            }

            links.forEach(function (link) {
                link.addEventListener('click', function () {
                    setActive((link.getAttribute('href') || '').slice(1));
                });
            });

            window.addEventListener('scroll', updateActive, { passive: true });
            window.addEventListener('resize', updateActive);
            updateActive();
        })();

        (function () {
            var header = document.querySelector('header');
            if (!header) return;

            function updateNavTheme() {
                if (header.classList.contains('menu-open')) return;

                header.classList.remove('on-dark');
                header.classList.toggle('is-scrolled', window.scrollY > 60);
            }

            var ticking = false;
            function requestThemeUpdate() {
                if (ticking) return;
                ticking = true;
                requestAnimationFrame(function () {
                    updateNavTheme();
                    ticking = false;
                });
            }

            window.addEventListener('scroll', requestThemeUpdate, { passive: true });
            window.addEventListener('resize', requestThemeUpdate);
            window.addEventListener('nav-theme-refresh', requestThemeUpdate);
            updateNavTheme();
        })();

        (function () {
            var form = document.getElementById('contact-form');
            var note = document.getElementById('contact-form-note');
            if (!form) return;

            var serviceSelect = document.getElementById('contact-service');
            var params = new URLSearchParams(window.location.search);
            var preselected = params.get('service');
            if (preselected && serviceSelect) {
                serviceSelect.value = preselected;
            }

            function setButtonLoading(button, loading, i18n) {
                if (!button) return;
                var label = button.querySelector('span');
                var icon = button.querySelector('i');
                button.disabled = loading;
                button.classList.toggle('is-loading', loading);
                button.setAttribute('aria-busy', loading ? 'true' : 'false');
                if (label) {
                    label.textContent = loading
                        ? (button.getAttribute('data-loading-label') || i18n.sending || 'جاري الإرسال...')
                        : (button.getAttribute('data-default-label') || i18n.sendRequest || 'إرسال الطلب');
                }
                if (icon) {
                    icon.className = loading ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-envelope';
                }
            }

            function firstError(data) {
                if (data && data.errors) {
                    var values = Object.values(data.errors);
                    if (values.length && values[0] && values[0][0]) return values[0][0];
                }
                return (data && data.message) || '';
            }

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var name = (document.getElementById('contact-name').value || '').trim();
                var phone = (document.getElementById('contact-phone').value || '').trim();
                var emailInput = document.getElementById('contact-email');
                var email = emailInput ? (emailInput.value || '').trim() : '';
                var serviceValue = serviceSelect ? (serviceSelect.value || '') : '';
                var message = (document.getElementById('contact-message').value || '').trim();
                var honeypot = document.getElementById('contact-website');
                var token = document.querySelector('meta[name="csrf-token"]');
                var action = form.getAttribute('action') || '/leads';
                var button = form.querySelector('button[type="submit"]');
                var i18n = (window.Rakeeza && window.Rakeeza.i18n) || {};

                if (note) {
                    note.classList.remove('is-visible', 'is-success', 'is-error');
                    note.textContent = '';
                }

                if (!name || !phone || !email || !serviceValue || !message) {
                    if (note) {
                        note.textContent = i18n.requiredFields || 'يرجى تعبئة جميع الحقول المطلوبة.';
                        note.classList.add('is-visible', 'is-error');
                    }
                    return;
                }

                var payload = {
                    name: name,
                    phone: phone,
                    email: email,
                    service_id: serviceValue === '' ? null : (serviceValue === 'general' ? 'general' : Number(serviceValue)),
                    message: message,
                    website: honeypot ? honeypot.value : '',
                };

                setButtonLoading(button, true, i18n);

                fetch(action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': token ? token.getAttribute('content') : '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload),
                }).then(function (res) {
                    return res.json().then(function (data) {
                        return { ok: res.ok, data: data };
                    }).catch(function () {
                        return { ok: res.ok, data: {} };
                    });
                }).then(function (result) {
                    var succeeded = result.ok && result.data && result.data.success !== false;
                    if (note) {
                        note.textContent = succeeded
                            ? ((result.data && result.data.message) || i18n.emailSuccess || 'تم إرسال طلبك بنجاح، سيتواصل معك فريق ركيزة قريباً')
                            : (firstError(result.data) || i18n.sendFailed || 'تعذر إرسال الطلب. حاول مرة أخرى.');
                        note.classList.add('is-visible', succeeded ? 'is-success' : 'is-error');
                    }
                    if (succeeded) {
                        form.reset();
                        if (serviceSelect && preselected) {
                            serviceSelect.value = preselected;
                        }
                    }
                }).catch(function () {
                    if (note) {
                        note.textContent = i18n.sendFailed || 'تعذر إرسال الطلب. حاول مرة أخرى.';
                        note.classList.add('is-visible', 'is-error');
                    }
                }).finally(function () {
                    setButtonLoading(button, false, i18n);
                });
            });
        })();

        (function () {
            var form = document.getElementById('craftsman-form');
            var note = document.getElementById('craftsman-form-note');
            if (!form) return;

            function openCraftsmanWhatsApp(payload) {
                var whatsapp = (form.getAttribute('data-whatsapp') || '').replace(/\D/g, '');
                if (!whatsapp) return;

                var i18n = (window.Rakeeza && window.Rakeeza.i18n) || {};
                var lines = [
                    i18n.craftsmanWhatsappIntro || 'مرحباً ركيزة، أود الانضمام كحرفي.',
                    '',
                    (i18n.craftsmanLabelName || 'الاسم') + ': ' + payload.name,
                    (i18n.craftsmanLabelPhone || 'الجوال') + ': ' + payload.phone,
                    (i18n.craftsmanLabelCity || 'المدينة') + ': ' + payload.city,
                    (i18n.craftsmanLabelSpecialty || 'التخصص') + ': ' + payload.specialty,
                    (i18n.craftsmanLabelExperience || 'سنوات الخبرة') + ': ' + payload.experience_years,
                    (i18n.craftsmanLabelTools || 'معدات خاصة') + ': ' + (payload.has_tools
                        ? (i18n.craftsmanYes || 'نعم')
                        : (i18n.craftsmanNo || 'لا')),
                ];

                if (payload.bio) {
                    lines.push((i18n.craftsmanLabelBio || 'نبذة') + ': ' + payload.bio);
                }

                var url = 'https://wa.me/' + whatsapp + '?text=' + encodeURIComponent(lines.join('\n'));
                window.open(url, '_blank', 'noopener,noreferrer');
            }

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var name = (document.getElementById('craftsman-name').value || '').trim();
                var phone = (document.getElementById('craftsman-phone').value || '').trim();
                var city = (document.getElementById('craftsman-city').value || '').trim();
                var specialtySelect = document.getElementById('craftsman-specialty');
                var specialty = specialtySelect ? specialtySelect.value || '' : '';
                var specialtyLabel = specialty;
                if (specialtySelect && specialtySelect.selectedIndex >= 0) {
                    specialtyLabel = (specialtySelect.options[specialtySelect.selectedIndex].text || specialty).trim();
                }
                var experience = Number(document.getElementById('craftsman-experience').value || 0);
                var hasTools = document.getElementById('craftsman-tools').checked;
                var bio = (document.getElementById('craftsman-bio').value || '').trim();
                var token = document.querySelector('meta[name="csrf-token"]');
                var button = form.querySelector('button[type="submit"]');
                var i18n = (window.Rakeeza && window.Rakeeza.i18n) || {};
                var payload = {
                    name: name,
                    phone: phone,
                    city: city,
                    specialty: specialty,
                    experience_years: experience,
                    has_tools: hasTools,
                    bio: bio,
                };

                if (note) {
                    note.classList.remove('is-visible', 'is-success', 'is-error');
                    note.textContent = '';
                }

                if (button) button.disabled = true;

                fetch(form.getAttribute('action') || '/craftsman', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': token ? token.getAttribute('content') : '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload),
                })
                    .then(function (response) {
                        return response.json().then(function (data) {
                            return { ok: response.ok, data: data };
                        });
                    })
                    .then(function (result) {
                        if (!note) return;
                        if (result.ok) {
                            note.textContent = i18n.craftsmanWhatsappReady || result.data.message || i18n.craftsmanSuccess || 'تم استلام طلبك بنجاح.';
                            note.classList.add('is-visible', 'is-success');
                            openCraftsmanWhatsApp({
                                name: name,
                                phone: phone,
                                city: city,
                                specialty: specialtyLabel,
                                experience_years: experience,
                                has_tools: hasTools,
                                bio: bio,
                            });
                            form.reset();
                        } else {
                            var firstError = result.data.errors
                                ? Object.values(result.data.errors)[0][0]
                                : result.data.message;
                            note.textContent = firstError || i18n.craftsmanFailed || 'تعذر إرسال الطلب.';
                            note.classList.add('is-visible', 'is-error');
                        }
                    })
                    .catch(function () {
                        if (note) {
                            note.textContent = i18n.sendFailed || 'تعذر إرسال الطلب. حاول مرة أخرى.';
                            note.classList.add('is-visible', 'is-error');
                        }
                    })
                    .finally(function () {
                        if (button) button.disabled = false;
                    });
            });
        })();

(function () {
            var slides = document.querySelectorAll('.hero-slide');
            if (slides.length < 2) return;
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

            var index = 0;
            setInterval(function () {
                slides[index].classList.remove('is-active');
                index = (index + 1) % slides.length;
                slides[index].classList.add('is-active');
            }, 6500);
        })();

        (function () {
            if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;

            gsap.registerPlugin(ScrollTrigger);
            gsap.config({ nullTargetWarn: false });

            var mm = gsap.matchMedia();

            mm.add('(prefers-reduced-motion: reduce)', function () {
                gsap.set('.hero-kicker, .hero-content h1, .hero-content p, .hero-btns', { clearProps: 'all' });
            });

            mm.add('(prefers-reduced-motion: no-preference)', function () {
                var body = document.body;
                var progress = document.querySelector('.scroll-progress');

                if (progress) {
                    gsap.to(progress, {
                        scaleX: 1,
                        ease: 'none',
                        scrollTrigger: {
                            trigger: body,
                            start: 'top top',
                            end: 'bottom bottom',
                            scrub: 0.35
                        }
                    });
                }

                var isDesktop = window.matchMedia('(min-width: 993px)').matches;

                var heroIntro = gsap.timeline({ defaults: { ease: 'power3.out' } });
                heroIntro
                    .from('.hero-kicker', { y: 32, opacity: 0, duration: 0.8 })
                    .from('.hero-content h1', { y: 24, opacity: 0, duration: 0.6 }, '-=0.5')
                    .from('.hero-content p', { y: 18, opacity: 0, duration: 0.5 }, '-=0.38')
                    .from('.hero-btns', { y: 16, opacity: 0, duration: 0.45 }, '-=0.3');

                if (isDesktop) {
                    gsap.to('.hero-slides', {
                        yPercent: 18,
                        ease: 'none',
                        scrollTrigger: {
                            trigger: '.hero',
                            start: 'top top',
                            end: 'bottom top',
                            scrub: true
                        }
                    });

                    gsap.to('.hero-content', {
                        y: 70,
                        opacity: 0.2,
                        ease: 'none',
                        scrollTrigger: {
                            trigger: '.hero',
                            start: 'top top',
                            end: 'bottom top',
                            scrub: true
                        }
                    });
                }

                function revealOnScroll(selector, fromVars, options) {
                    var els = gsap.utils.toArray(selector);
                    if (!els.length) return;

                    options = options || {};
                    var toVars = { opacity: 1, x: 0, y: 0, duration: options.duration || 0.8, stagger: options.stagger || 0, ease: options.ease || 'power3.out', immediateRender: true };

                    if (options.clearTransform) {
                        toVars.onComplete = function () {
                            gsap.set(els, { clearProps: 'transform' });
                        };
                    }

                    gsap.timeline({
                        scrollTrigger: {
                            trigger: options.trigger || els[0],
                            start: options.start || 'top 90%',
                            once: true,
                            toggleActions: 'play none none none'
                        }
                    }).fromTo(els, fromVars, toVars);
                }

                var slideAmt = isDesktop ? 70 : 28;
                revealOnScroll('#about .about-text', { x: slideAmt, opacity: 0 }, { trigger: '#about', start: 'top 82%', duration: 0.85 });
                revealOnScroll('#about .about-photo-card', { x: -slideAmt, opacity: 0 }, { trigger: '#about', start: 'top 82%', duration: 0.85 });
                revealOnScroll('#about .about-highlight-item', { x: isDesktop ? 40 : 16, opacity: 0 }, { trigger: '#about .about-highlights', start: 'top 90%', duration: 0.55, stagger: 0.1, ease: 'power2.out' });

                if (isDesktop) {
                    gsap.fromTo('#about .about-photo-card img',
                        { scale: 1.06, yPercent: -2 },
                        {
                            scale: 1,
                            yPercent: 2,
                            ease: 'none',
                            scrollTrigger: {
                                trigger: '#about .about-photo-card',
                                start: 'top bottom',
                                end: 'bottom top',
                                scrub: true
                            }
                        }
                    );
                }

                revealOnScroll('#services .section-title', { y: 36, opacity: 0 }, { trigger: '#services .section-header', start: 'top 90%' });

                (function animateServiceCards() {
                    var cards = gsap.utils.toArray('.service-card');
                    var grid = document.querySelector('.services-grid');
                    if (!cards.length || !grid) return;

                    var dropTl = gsap.timeline({
                        defaults: { ease: 'power4.out' },
                        scrollTrigger: {
                            trigger: '#services',
                            start: 'top 82%',
                            once: true,
                            toggleActions: 'play none none none'
                        }
                    });

                    dropTl.fromTo(cards, {
                        y: isDesktop ? -110 : -48,
                        opacity: 0,
                        rotateX: isDesktop ? -18 : 0,
                        scale: isDesktop ? 0.94 : 1
                    }, {
                        y: 0,
                        opacity: 1,
                        rotateX: 0,
                        scale: 1,
                        duration: isDesktop ? 1.05 : 0.7,
                        stagger: { each: isDesktop ? 0.14 : 0.08, from: 'start' },
                        immediateRender: true,
                        onComplete: function () {
                            gsap.set(cards, { clearProps: 'transform' });
                            cards.forEach(function (card) {
                                card.classList.add('is-ready');
                            });
                        }
                    });

                    if (isDesktop) {
                        dropTl.fromTo(grid.querySelectorAll('.service-image img'), {
                            yPercent: -18,
                            scale: 1.12
                        }, {
                            yPercent: 0,
                            scale: 1,
                            duration: 1.15,
                            stagger: { each: 0.14, from: 'start' },
                            ease: 'power3.out'
                        }, 0.12);
                    }
                })();

                revealOnScroll('.craftsman-banner', { y: 40, opacity: 0 }, { trigger: '.craftsman-banner', start: 'top 90%', duration: 0.85 });
                revealOnScroll('.vision-card', { y: 40, opacity: 0 }, { trigger: '.vision-card', start: 'top 92%', ease: 'power2.out' });
                revealOnScroll('#projects .section-title', { y: 36, opacity: 0 }, { trigger: '#projects .section-header', start: 'top 90%' });
                revealOnScroll('.project-card', { y: 56, opacity: 0 }, {
                    trigger: '#projects',
                    start: 'top 85%',
                    duration: 0.9,
                    stagger: 0.18,
                    clearTransform: true
                });

                revealOnScroll('#why-us .why-us-content > *', { y: 36, opacity: 0 }, { trigger: '#why-us', start: 'top 80%', duration: 0.7, stagger: 0.12 });
                revealOnScroll('#why-us .why-item', { x: 36, opacity: 0 }, { trigger: '#why-us .why-list', start: 'top 90%', duration: 0.55, stagger: 0.08, ease: 'power2.out' });
                revealOnScroll('#why-us .why-us-photo', { x: -50, opacity: 0 }, { trigger: '#why-us', start: 'top 80%', duration: 1 });
                revealOnScroll('#contact .section-title, #contact .contact-intro', { y: 28, opacity: 0 }, { trigger: '#contact', start: 'top 85%', duration: 0.7, stagger: 0.1 });
                revealOnScroll('#contact .contact-info-item', { y: 32, opacity: 0 }, { trigger: '#contact .contact-info-stack', start: 'top 88%', duration: 0.6, stagger: 0.08, ease: 'power2.out' });
                revealOnScroll('#contact .contact-form-card', { y: 40, opacity: 0 }, { trigger: '#contact .contact-form-card', start: 'top 88%', duration: 0.8 });
                revealOnScroll('footer .footer-grid > *', { y: 28, opacity: 0 }, { trigger: 'footer', start: 'top 92%', duration: 0.7, stagger: 0.1, ease: 'power2.out' });

                window.addEventListener('load', function () {
                    ScrollTrigger.refresh();
                });
            });
        })();

        (function () {
            var openers = document.querySelectorAll('[data-lightbox-open]');
            if (!openers.length) {
                return;
            }

            var root = document.createElement('div');
            root.className = 'lightbox';
            root.setAttribute('role', 'dialog');
            root.setAttribute('aria-modal', 'true');
            root.setAttribute('aria-hidden', 'true');
            var i18n = (window.Rakeeza && window.Rakeeza.i18n) || {};
            var isRtl = !window.Rakeeza || window.Rakeeza.dir !== 'ltr';
            var prevIcon = isRtl ? 'fa-chevron-right' : 'fa-chevron-left';
            var nextIcon = isRtl ? 'fa-chevron-left' : 'fa-chevron-right';

            root.innerHTML =
                '<div class="lightbox-backdrop" data-lightbox-close></div>' +
                '<div class="lightbox-dialog">' +
                    '<button type="button" class="lightbox-close" data-lightbox-close aria-label="' + (i18n.lightboxClose || 'إغلاق المعرض') + '">' +
                        '<i class="fa-solid fa-xmark"></i>' +
                    '</button>' +
                    '<button type="button" class="lightbox-nav is-prev" data-lightbox-prev aria-label="' + (i18n.lightboxPrev || 'الصورة السابقة') + '">' +
                        '<i class="fa-solid ' + prevIcon + '"></i>' +
                    '</button>' +
                    '<figure class="lightbox-figure">' +
                        '<img alt="">' +
                        '<figcaption>' +
                            '<div class="lightbox-meta">' +
                                '<strong data-lightbox-title></strong>' +
                                '<span data-lightbox-counter dir="ltr"></span>' +
                            '</div>' +
                            '<p data-lightbox-details></p>' +
                        '</figcaption>' +
                    '</figure>' +
                    '<button type="button" class="lightbox-nav is-next" data-lightbox-next aria-label="' + (i18n.lightboxNext || 'الصورة التالية') + '">' +
                        '<i class="fa-solid ' + nextIcon + '"></i>' +
                    '</button>' +
                '</div>';
            document.body.appendChild(root);

            var img = root.querySelector('img');
            var titleEl = root.querySelector('[data-lightbox-title]');
            var detailsEl = root.querySelector('[data-lightbox-details]');
            var counterEl = root.querySelector('[data-lightbox-counter]');
            var prevBtn = root.querySelector('[data-lightbox-prev]');
            var nextBtn = root.querySelector('[data-lightbox-next]');
            var closeBtn = root.querySelector('.lightbox-close');
            var state = { images: [], index: 0, title: '', details: '', lastFocus: null, touchX: null };

            function payloadFrom(el) {
                var host = el.closest('[data-lightbox]');
                if (!host) {
                    return null;
                }

                try {
                    return JSON.parse(host.getAttribute('data-lightbox'));
                } catch (error) {
                    return null;
                }
            }

            function render() {
                var total = state.images.length;
                var current = state.images[state.index] || '';
                img.setAttribute('src', current);
                img.setAttribute('alt', state.title);
                titleEl.textContent = state.title;
                detailsEl.textContent = state.details || '';
                detailsEl.hidden = !state.details;
                counterEl.textContent = total > 1 ? (state.index + 1) + ' / ' + total : '';
                counterEl.hidden = total < 2;
                prevBtn.hidden = total < 2;
                nextBtn.hidden = total < 2;
            }

            function open(payload, start) {
                if (!payload || !payload.images || !payload.images.length) {
                    return;
                }

                state.images = payload.images;
                state.index = ((start % payload.images.length) + payload.images.length) % payload.images.length;
                state.title = payload.title || '';
                state.details = payload.details || '';
                state.lastFocus = document.activeElement;
                render();
                root.classList.add('is-open');
                root.setAttribute('aria-hidden', 'false');
                document.body.classList.add('lightbox-open');
                closeBtn.focus();
            }

            function close() {
                if (!root.classList.contains('is-open')) {
                    return;
                }

                root.classList.remove('is-open');
                root.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('lightbox-open');
                if (state.lastFocus && typeof state.lastFocus.focus === 'function') {
                    state.lastFocus.focus();
                }
            }

            function step(delta) {
                if (state.images.length < 2) {
                    return;
                }

                state.index = (state.index + delta + state.images.length) % state.images.length;
                render();
            }

            openers.forEach(function (opener) {
                opener.addEventListener('click', function (event) {
                    event.preventDefault();
                    var payload = payloadFrom(opener);
                    var start = Number(opener.getAttribute('data-lightbox-index') || 0);
                    open(payload, start);
                });
            });

            root.addEventListener('click', function (event) {
                if (event.target.closest('[data-lightbox-close]')) {
                    close();
                }
                if (event.target.closest('[data-lightbox-prev]')) {
                    step(-1);
                }
                if (event.target.closest('[data-lightbox-next]')) {
                    step(1);
                }
            });

            document.addEventListener('keydown', function (event) {
                if (!root.classList.contains('is-open')) {
                    return;
                }

                if (event.key === 'Escape') {
                    close();
                }
                if (event.key === 'ArrowRight') {
                    step(isRtl ? -1 : 1);
                }
                if (event.key === 'ArrowLeft') {
                    step(isRtl ? 1 : -1);
                }
            });

            root.addEventListener('touchstart', function (event) {
                if (!event.changedTouches.length) {
                    return;
                }
                state.touchX = event.changedTouches[0].clientX;
            }, { passive: true });

            root.addEventListener('touchend', function (event) {
                if (state.touchX === null || !event.changedTouches.length) {
                    return;
                }

                var delta = event.changedTouches[0].clientX - state.touchX;
                state.touchX = null;
                if (Math.abs(delta) < 50) {
                    return;
                }

                step(delta > 0 ? -1 : 1);
            }, { passive: true });
        })();

        (function () {
            var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            var autoplayMs = 4000;

            function payloadFrom(host) {
                try {
                    return JSON.parse(host.getAttribute('data-lightbox'));
                } catch (error) {
                    return null;
                }
            }

            function bindAutoplay(root, advance) {
                if (reduceMotion) {
                    return function () {};
                }

                var timer = null;
                var hovering = false;

                function stop() {
                    clearInterval(timer);
                    timer = null;
                }

                function start() {
                    stop();
                    if (hovering || document.body.classList.contains('lightbox-open')) {
                        return;
                    }
                    timer = setInterval(function () {
                        if (document.body.classList.contains('lightbox-open')) {
                            return;
                        }
                        advance();
                    }, autoplayMs);
                }

                root.addEventListener('mouseenter', function () {
                    hovering = true;
                    stop();
                });
                root.addEventListener('mouseleave', function () {
                    hovering = false;
                    start();
                });

                if ('IntersectionObserver' in window) {
                    var observer = new IntersectionObserver(function (entries) {
                        entries.forEach(function (entry) {
                            if (entry.isIntersecting && !hovering) {
                                start();
                            } else {
                                stop();
                            }
                        });
                    }, { threshold: 0.35 });
                    observer.observe(root);
                } else {
                    start();
                }

                return function restart() {
                    start();
                };
            }

            document.querySelectorAll('.service-work[data-lightbox]').forEach(function (work) {
                var payload = payloadFrom(work);
                var images = payload && payload.images ? payload.images : [];
                if (images.length < 2) {
                    return;
                }

                var index = 0;
                var switchTimer = null;
                var mainBtn = work.querySelector('.service-work-main');
                var mainImg = work.querySelector('[data-work-image]');
                var countEl = work.querySelector('[data-work-count]');
                var thumbs = work.querySelectorAll('.service-work-thumb');
                var prev = work.querySelector('[data-work-prev]');
                var next = work.querySelector('[data-work-next]');
                var restart = bindAutoplay(work, function () {
                    show(index + 1);
                });

                function show(nextIndex) {
                    index = (nextIndex + images.length) % images.length;
                    if (mainImg) {
                        window.clearTimeout(switchTimer);
                        mainImg.classList.add('is-switching');
                        var src = images[index];
                        switchTimer = window.setTimeout(function () {
                            mainImg.setAttribute('src', src);
                            mainImg.classList.remove('is-switching');
                        }, 180);
                    }
                    if (mainBtn) {
                        mainBtn.setAttribute('data-lightbox-index', String(index));
                    }
                    if (countEl) {
                        countEl.textContent = (index + 1) + ' / ' + images.length;
                    }
                    thumbs.forEach(function (thumb, thumbIndex) {
                        thumb.classList.toggle('is-active', thumbIndex === index);
                    });
                }

                if (prev) {
                    prev.addEventListener('click', function (event) {
                        event.preventDefault();
                        event.stopPropagation();
                        show(index - 1);
                        restart();
                    });
                }

                if (next) {
                    next.addEventListener('click', function (event) {
                        event.preventDefault();
                        event.stopPropagation();
                        show(index + 1);
                        restart();
                    });
                }
            });

            document.querySelectorAll('.project-card[data-lightbox]').forEach(function (card) {
                var payload = payloadFrom(card);
                var images = payload && payload.images ? payload.images : [];
                if (images.length < 2) {
                    return;
                }

                var index = 0;
                var mainImg = card.querySelector('[data-project-image]');
                var opener = card.querySelector('[data-lightbox-open]');
                if (!mainImg) {
                    return;
                }

                function show(nextIndex) {
                    index = (nextIndex + images.length) % images.length;
                    mainImg.classList.add('is-switching');
                    window.setTimeout(function () {
                        mainImg.setAttribute('src', images[index]);
                        mainImg.classList.remove('is-switching');
                    }, 180);
                    if (opener) {
                        opener.setAttribute('data-lightbox-index', String(index));
                    }
                }

                bindAutoplay(card, function () {
                    show(index + 1);
                });
            });
        })();
