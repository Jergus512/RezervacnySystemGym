@extends('layouts.app')

@section('title', 'Rezervačný systém - Gym')

@php($overlayTopbar = true)
@php($homeAuthBurger = auth()->check())

@section('content')
    <style>
        /* Home page styles scoped to this view */

        /* Home only: page background should match footer (dark) */
        html, body {
            background: #1f1f1f;
            overflow-x: hidden;
        }

        /* Prefer clip where supported to avoid horizontal scrollbar without affecting vertical scrolling */
        @supports (overflow-x: clip) {
            html, body { overflow-x: clip; }
        }

        /* Home feature cards (3 blocks) */
        .home-feature-card {
            height: 100%;
            min-height: 92px;
            background: #fff;
            border: 1px solid rgba(0,0,0,.08);
            box-shadow: 0 10px 30px rgba(0,0,0,.08);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: .25rem;
        }

        .home-feature-card .fw-semibold {
            line-height: 1.15;
        }

        .home-feature-card .text-muted.small {
            line-height: 1.25;
            margin-bottom: 0;
        }

        @media (min-width: 768px) {
            .home-feature-card { min-height: 104px; }
        }

        /* Keep the actual content area under hero white */
        .btn-orange {
            background: #f97316;
            border-color: #f97316;
            color: #fff;
        }

        .btn-orange:hover,
        .btn-orange:focus {
            background: #ea6a0f;
            border-color: #ea6a0f;
            color: #fff;
        }

        /* Home: make “Prihlásiť sa” button visible on light background */
        .btn-outline-home {
            color: #000;
            border-color: #000;
        }

        .btn-outline-home:hover,
        .btn-outline-home:focus {
            color: #000;
            background-color: #e9ecef;
            border-color: #000;
        }

        /* Full-bleed hero image right under the top bar */
        .home-hero-bleed {
            position: relative;
            width: 100vw;
            margin-left: calc(50% - 50vw);
            margin-right: calc(50% - 50vw);
            overflow: hidden;
            margin-top: 0;
            padding-top: 0;
        }

        .home-hero-bleed-img {
            width: 100%;
            height: min(62vh, 720px);
            object-fit: cover;
            object-position: left top;
            display: block;
        }

        .home-hero-bleed-title {
            position: absolute;
            inset: 0;
            z-index: 2;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 1.25rem;
            padding-top: 6.1rem;
            pointer-events: none;
            text-align: center;
        }

        .home-hero-bleed-title h1 {
            margin: 0;
            color: #fff;
            font-weight: 800;
            letter-spacing: -0.02em;
            text-shadow: 0 10px 30px rgba(0,0,0,.55);
            font-size: clamp(3.6rem, 9vw, 7.2rem);
            line-height: 1.02;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
        }

        @media (min-width: 768px) {
            .home-hero-bleed-title { padding-top: 6.9rem; }
        }

        @media (min-width: 992px) {
            .home-hero-bleed-img { height: min(70vh, 820px); }
            .home-hero-bleed-title { padding-top: 7.4rem; }
        }

        .cap {
            display: inline-block;
            font-size: 1.18em;
            line-height: 1;
        }

        /* Home gallery */
        .home-gallery .gallery-item {
            display: block;
            width: 100%;
            aspect-ratio: 4 / 3;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 16px 44px rgba(0,0,0,.25);
        }

        .home-gallery .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform .18s ease, filter .18s ease;
        }

        .home-gallery .gallery-item:hover img {
            transform: scale(1.03);
            filter: saturate(1.03);
        }

        /* Gallery lightbox */
        .gallery-lightbox {
            position: fixed;
            inset: 0;
            z-index: 3000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: rgba(0, 0, 0, .82);
            backdrop-filter: blur(2px);
        }

        /*noinspection CssUnusedSymbol */
        .gallery-lightbox.is-open { display: flex; }

        .gallery-lightbox .lb-inner {
            position: relative;
            width: min(1100px, 94vw);
            max-height: 88vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .gallery-lightbox img {
            width: 100%;
            height: auto;
            max-height: 88vh;
            object-fit: contain;
            border-radius: 16px;
            box-shadow: 0 24px 70px rgba(0,0,0,.55);
            background: rgba(0,0,0,.2);
        }

        .gallery-lightbox .lb-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 48px;
            height: 48px;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,.35);
            background: rgba(0,0,0,.35);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            line-height: 1;
            cursor: pointer;
            user-select: none;
            transition: transform .12s ease, background-color .12s ease, border-color .12s ease;
        }

        .gallery-lightbox .lb-btn:hover {
            background: rgba(249, 115, 22, .22);
            border-color: rgba(249, 115, 22, .65);
            transform: translateY(-50%) scale(1.03);
        }

        .gallery-lightbox .lb-prev { left: -18px; }
        .gallery-lightbox .lb-next { right: -18px; }

        .gallery-lightbox .lb-close {
            position: absolute;
            top: -18px;
            right: -18px;
            transform: none;
            font-size: 22px;
        }

        @media (max-width: 575.98px) {
            .gallery-lightbox .lb-prev { left: 6px; }
            .gallery-lightbox .lb-next { right: 6px; }
            .gallery-lightbox .lb-close { top: 6px; right: 6px; }
        }

        /* Home footer */
        .home-footer {
            background: #1f1f1f;
            color: rgba(255,255,255,.8);
            padding: 4rem 0 0;
            width: 100vw;
            margin-left: calc(50% - 50vw);
            margin-right: calc(50% - 50vw);
        }

        .home-footer .footer-title {
            color: #f97316;
            font-weight: 800;
            letter-spacing: .02em;
            margin-bottom: 1rem;
            text-transform: uppercase;
            font-size: .95rem;
        }

        .home-footer .footer-link {
            color: rgba(255,255,255,.85);
            text-decoration: none;
            display: inline-block;
            padding: .25rem 0;
        }

        .home-footer .footer-link:hover {
            color: #f97316;
            text-decoration: underline;
        }

        .home-footer .footer-meta {
            color: rgba(255,255,255,.75);
            font-size: .95rem;
            line-height: 1.45;
        }

        .home-footer .footer-logo {
            max-width: 210px;
            width: 210px;
            height: auto;
            display: block;
            margin-left: auto;
            margin-right: auto;
            transform: translateY(-30px);
        }

        .home-footer .footer-divider {
            border-top: 1px solid rgba(255,255,255,.18);
            margin-top: 2.5rem;
        }

        .home-footer .footer-bottom {
            padding: 1rem 0;
            color: rgba(255,255,255,.55);
            font-size: .9rem;
        }

        .home-footer .accent {
            color: #f97316;
            font-weight: 700;
        }

        .home-footer .social-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: transparent;
            border: 1px solid rgba(255,255,255,.22);
            transition: transform .12s ease, filter .12s ease, background-color .12s ease, border-color .12s ease;
            text-decoration: none;
        }

        .home-footer .social-link:hover {
            background: rgba(249, 115, 22, .14);
            border-color: rgba(249, 115, 22, .7);
            transform: translateY(-1px);
            filter: brightness(1.02);
        }

        .home-footer .social-icon {
            width: 22px;
            height: 22px;
            object-fit: contain;
            display: block;
        }

        @media (max-width: 991.98px) {
            .home-footer { text-align: center; }

            .home-footer .footer-logo {
                margin-left: auto;
                margin-right: auto;
                transform: translate(20px, -30px);
            }

            .home-footer .footer-meta .d-flex {
                justify-content: center !important;
                gap: 1.25rem;
            }

            .home-footer .footer-meta .d-flex > span {
                display: inline-block;
                min-width: 0;
            }

            .home-footer .social-wrap {
                justify-content: center !important;
            }
        }

        /* Ensure no white strip above the hero on Home */
        .home-footer-wrap {
            background: #1f1f1f;
            width: 100vw;
            margin-left: calc(50% - 50vw);
            margin-right: calc(50% - 50vw);
        }

        /* Extend dark background past footer content area */
        .home-footer-wrap,
        .home-footer {
            background: #1f1f1f;
        }

        /* Home main section full-width background */
        .home-main-bleed {
            position: relative;
            width: 100vw;
            margin-left: calc(50% - 50vw);
            margin-right: calc(50% - 50vw);
            overflow: hidden;
            padding-top: 0;
            background: #fff;
        }
    </style>

    {{-- Big full-width image under the top bar (edge-to-edge) --}}
    <section class="home-hero-bleed">
        <img class="home-hero-bleed-img" src="{{ asset('img/pozadie7.png') }}" alt="Činky" loading="eager">
        <div class="home-hero-bleed-title">
            <div class="container">
                <h1><span class="cap">S</span>UPER <span class="cap">G</span>YM</h1>
            </div>
        </div>
    </section>

    <section class="home-main-bleed">
        <div class="container py-5">
            <div class="row align-items-center g-4">
                <div class="col-12 col-lg-6">
                    <div class="mb-4">
                        <span class="badge" style="background:#111;color:#fff;">Kalendár • Kredity • Rezervácie</span>
                    </div>

                    <h1 class="display-5 fw-bold mb-3">Rezervuj si tréning jednoducho.</h1>
                    <p class="lead text-muted mb-4">
                        Prehľadný systém pre klientov, trénerov, adminov a recepciu. Sleduj tréningy v kalendári, spravuj kapacity,
                        kredity a registrácie bez chaosu.
                    </p>

                    @guest
                        <div class="d-flex flex-column flex-sm-row gap-2 mb-4">
                            <a href="{{ route('register') }}" class="btn btn-orange btn-lg px-4">Začať (Registrácia)</a>
                            <a href="{{ route('login') }}" class="btn btn-outline-home btn-lg px-4">Prihlásiť sa</a>
                        </div>
                    @else
                        <div class="d-flex flex-column flex-sm-row gap-2 mb-4">
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.users.index') }}" class="btn btn-orange btn-lg px-4">Správa používateľov</a>
                                <a href="{{ route('admin.trainings.index') }}" class="btn btn-outline-dark btn-lg px-4">Správa aktuálnych tréningov</a>
                            @elseif(auth()->user()->isTrainer())
                                <a href="{{ route('trainer.trainings.index') }}" class="btn btn-orange btn-lg px-4">Vytvorené tréningy</a>
                                <a href="{{ route('trainer.trainings.create') }}" class="btn btn-outline-dark btn-lg px-4">Vytvoriť tréning</a>
                            @elseif(auth()->user()->isReception())
                                <a href="{{ route('reception.calendar') }}" class="btn btn-orange btn-lg px-4">Kalendár tréningov</a>
                                <a href="{{ route('reception.credits.create') }}" class="btn btn-outline-dark btn-lg px-4">Pridanie kreditov</a>
                            @else
                                <a href="{{ route('training-calendar.index') }}" class="btn btn-orange btn-lg px-4">Otvoriť kalendár</a>
                            @endif
                        </div>
                    @endguest

                    <div class="row g-3 align-items-stretch">
                        <div class="col-12 col-md-4">
                            <div class="home-feature-card p-3 rounded-3 h-100 d-flex flex-column">
                                <div class="fw-semibold">Kalendár</div>
                                <div class="text-muted small">Rýchly prehľad tréningov.</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="home-feature-card p-3 rounded-3 h-100 d-flex flex-column">
                                <div class="fw-semibold">Kredity</div>
                                <div class="text-muted small">Platby tréningov kreditmi.</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="home-feature-card p-3 rounded-3 h-100 d-flex flex-column">
                                <div class="fw-semibold">Správa</div>
                                <div class="text-muted small">Admin, tréner a recepcia nástroje.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <section class="home-gallery" aria-label="Galéria">
                        <div class="row g-3 mt-1">
                            <div class="col-6 col-md-4">
                                <a class="gallery-item js-gallery-item" href="{{ asset('img/galeria1.png') }}" data-gallery-src="{{ asset('img/galeria1.png') }}" aria-label="Galéria obrázok 1">
                                    <img src="{{ asset('img/galeria1.png') }}" alt="Galéria 1" loading="lazy">
                                </a>
                            </div>
                            <div class="col-6 col-md-4">
                                <a class="gallery-item js-gallery-item" href="{{ asset('img/galeria2.png') }}" data-gallery-src="{{ asset('img/galeria2.png') }}" aria-label="Galéria obrázok 2">
                                    <img src="{{ asset('img/galeria2.png') }}" alt="Galéria 2" loading="lazy">
                                </a>
                            </div>
                            <div class="col-6 col-md-4">
                                <a class="gallery-item js-gallery-item" href="{{ asset('img/galeria3.png') }}" data-gallery-src="{{ asset('img/galeria3.png') }}" aria-label="Galéria obrázok 3">
                                    <img src="{{ asset('img/galeria3.png') }}" alt="Galéria 3" loading="lazy">
                                </a>
                            </div>
                            <div class="col-6 col-md-4">
                                <a class="gallery-item js-gallery-item" href="{{ asset('img/galeria4.png') }}" data-gallery-src="{{ asset('img/galeria4.png') }}" aria-label="Galéria obrázok 4">
                                    <img src="{{ asset('img/galeria4.png') }}" alt="Galéria 4" loading="lazy">
                                </a>
                            </div>
                            <div class="col-6 col-md-4">
                                <a class="gallery-item js-gallery-item" href="{{ asset('img/galeria5.png') }}" data-gallery-src="{{ asset('img/galeria5.png') }}" aria-label="Galéria obrázok 5">
                                    <img src="{{ asset('img/galeria5.png') }}" alt="Galéria 5" loading="lazy">
                                </a>
                            </div>
                            <div class="col-6 col-md-4">
                                <a class="gallery-item js-gallery-item" href="{{ asset('img/galeria6.png') }}" data-gallery-src="{{ asset('img/galeria6.png') }}" aria-label="Galéria obrázok 6">
                                    <img src="{{ asset('img/galeria6.png') }}" alt="Galéria 6" loading="lazy">
                                </a>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </section>

    <div class="gallery-lightbox" id="galleryLightbox" aria-hidden="true">
        <div class="lb-inner" role="dialog" aria-modal="true" aria-label="Galéria">
            <button type="button" class="lb-btn lb-prev" id="galleryPrev" aria-label="Predchádzajúci">‹</button>
            <img id="galleryLightboxImage" src="" alt="Zväčšený obrázok">
            <button type="button" class="lb-btn lb-next" id="galleryNext" aria-label="Nasledujúci">›</button>
            <button type="button" class="lb-btn lb-close" id="galleryClose" aria-label="Zavrieť">✕</button>
        </div>
    </div>

    <div class="home-footer-wrap">
        <footer class="home-footer">
            <div class="container">
                <div class="row g-4 align-items-start">
                    <div class="col-12 col-lg-3">
                        <img class="footer-logo" src="{{ asset('img/logo1.png') }}" alt="Super Gym logo" loading="lazy">
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="footer-title">Kontakt</div>
                        <div class="footer-meta">
                            <div class="mb-2"><span class="accent">Tel:</span> +421 900 000 000</div>
                            <div class="mb-2"><span class="accent">Email:</span> info@supergym.sk</div>
                            <div class="footer-title mt-4">Nájdete nás na adrese</div>
                            <div>Žilina, Vysokoškolákov</div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="footer-title">Navigácia</div>
                        <div>
                            <a class="footer-link" href="{{ url('/') }}">Domov</a><br>
                            <a class="footer-link" href="{{ route('training-calendar.index') }}">Rozvrh cvičení</a><br>
                            <a class="footer-link" href="{{ route('register') }}">Registrácia</a><br>
                            <a class="footer-link" href="{{ route('login') }}">Prihlásenie</a><br>
                            @auth
                                @if(auth()->user()->isAdmin())
                                    <a class="footer-link" href="{{ route('admin.users.index') }}">Admin</a><br>
                                @elseif(auth()->user()->isTrainer())
                                    <a class="footer-link" href="{{ route('trainer.trainings.index') }}">Tréner</a><br>
                                @elseif(auth()->user()->isReception())
                                    <a class="footer-link" href="{{ route('reception.calendar') }}">Recepcia</a><br>
                                @else
                                    <a class="footer-link" href="{{ route('training-calendar.index') }}">Kalendár tréningov</a><br>
                                @endif
                            @endauth
                        </div>
                    </div>

                    <div class="col-12 col-lg-3">
                        <div class="footer-title">Otváracie hodiny</div>
                        <div class="footer-meta">
                            <div class="d-flex justify-content-between"><span>Pon – Pia</span><span>06:00 – 22:00</span></div>
                            <div class="d-flex justify-content-between"><span>So – Ne</span><span>07:00 – 22:00</span></div>
                        </div>

                        <div class="footer-title mt-4">Sociálne siete</div>
                        <div class="d-flex gap-2 social-wrap">
                            <a class="social-link" href="#" aria-label="Facebook" title="Facebook">
                                <img class="social-icon" src="{{ asset('img/fb-ikonka.webp') }}" alt="Facebook" loading="lazy">
                            </a>
                            <a class="social-link" href="#" aria-label="Instagram" title="Instagram">
                                <img class="social-icon" src="{{ asset('img/instagram-ikonka.png') }}" alt="Instagram" loading="lazy">
                            </a>
                            <a class="social-link" href="#" aria-label="YouTube" title="YouTube">
                                <img class="social-icon" src="{{ asset('img/youtube-ikonka.png') }}" alt="YouTube" loading="lazy">
                            </a>
                        </div>
                    </div>
                </div>

                <div class="footer-divider"></div>

                <div class="footer-bottom text-center">
                    © <span class="accent">SUPER GYM</span> {{ date('Y') }} – Všetky práva vyhradené.
                </div>
            </div>
        </footer>
    </div>


    <!--suppress HtmlUnknownTarget HtmlMissingLocalResource -->
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"
    ></script>
    <script>
        (function () {
            const items = Array.from(document.querySelectorAll('.js-gallery-item'));
            if (!items.length) return;

            const overlay = document.getElementById('galleryLightbox');
            const img = document.getElementById('galleryLightboxImage');
            const btnPrev = document.getElementById('galleryPrev');
            const btnNext = document.getElementById('galleryNext');
            const btnClose = document.getElementById('galleryClose');

            let currentIndex = 0;

            function setOpen(isOpen) {
                overlay.classList.toggle('is-open', isOpen);
                overlay.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
                document.body.style.overflow = isOpen ? 'hidden' : '';
            }

            function showIndex(idx) {
                currentIndex = (idx + items.length) % items.length;
                img.src = items[currentIndex].getAttribute('data-gallery-src') || items[currentIndex].getAttribute('href') || '';
            }

            function openAt(idx) {
                showIndex(idx);
                setOpen(true);
            }

            function close() {
                setOpen(false);
                img.src = '';
            }

            items.forEach((a, idx) => {
                a.addEventListener('click', (e) => {
                    e.preventDefault();
                    openAt(idx);
                });
            });

            btnPrev.addEventListener('click', () => showIndex(currentIndex - 1));
            btnNext.addEventListener('click', () => showIndex(currentIndex + 1));
            btnClose.addEventListener('click', close);

            overlay.addEventListener('click', (e) => {
                // click outside the image / controls closes
                if (e.target === overlay) close();
            });

            document.addEventListener('keydown', (e) => {
                if (!overlay.classList.contains('is-open')) return;
                if (e.key === 'Escape') close();
                if (e.key === 'ArrowLeft') showIndex(currentIndex - 1);
                if (e.key === 'ArrowRight') showIndex(currentIndex + 1);
            });
        })();
    </script>
@endsection
