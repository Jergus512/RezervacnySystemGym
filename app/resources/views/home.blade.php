<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>Rezervačný systém - Gym</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
        crossorigin="anonymous"
    >

    <style>
        /* Ensure the page starts at the very top with no default browser spacing */
        html, body {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            background: #1f1f1f; /* match footer to avoid white overscroll area */
        }

        /* Orange CTA button (match auth forms) */
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

        /* Optional: make outline-light a bit clearer on dark bg */
        .btn-outline-light:hover {
            color: #000;
        }

        /* Home: make “Prihlásiť sa” button visible on light background */
        .btn-outline-home {
            color: #000;
            border-color: #000;
        }

        .btn-outline-home:hover,
        .btn-outline-home:focus {
            color: #000;
            background-color: #e9ecef; /* light gray */
            border-color: #000;
        }

        .hero {
            position: relative;
            overflow: hidden;
            background: #fff;
            color: #111;
        }

        .hero::after {
            content: none;
        }

        .hero-card {
            background: #fff;
            border: 1px solid rgba(0,0,0,.08);
            box-shadow: 0 10px 30px rgba(0,0,0,.08);
            backdrop-filter: none;
        }

        .hero-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 1rem;
        }

        .hero-img-wrap {
            position: relative;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 18px 50px rgba(0,0,0,.45);
        }

        /* Home gallery (under hero images) */
        .home-gallery {
            margin-top: 1.25rem;
        }

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

        .topbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 20;
            background: rgba(0,0,0,.55);
            backdrop-filter: blur(10px);
            border-bottom: 0;
            margin: 0;
            padding: 0;
        }

        .topbar-inner {
            padding-top: .35rem;
            padding-bottom: .35rem;
        }

        .topbar-brand {
            width: 100%;
            display: flex;
            justify-content: center;
        }

        .topbar-logo {
            height: 76px;
            width: auto;
            max-width: 100%;
            display: block;
            filter: drop-shadow(0 8px 18px rgba(0,0,0,.35));
            /* Nudge logo up a bit without changing topbar height */
            transform: translateY(-6px);
        }

        .topbar-actions {
            width: 100%;
            display: flex;
            justify-content: center;
            gap: .5rem;
            margin-top: .2rem;
            flex-wrap: wrap;
        }

        @media (min-width: 768px) {
            .topbar-inner {
                padding-top: .5rem;
                padding-bottom: .5rem;
            }

            .topbar-logo {
                height: 92px;
                /* Keep similar nudge on desktop */
                transform: translateY(-6px);
            }

            .topbar-actions {
                justify-content: flex-end;
                margin-top: 0;
                width: auto;
            }

            .topbar-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
            }

            .topbar-brand {
                justify-content: flex-start;
                width: auto;
            }
        }

        /* Mobile (vertical) optical alignment: nudge logo slightly to the right */
        @media (max-width: 767.98px) {
            .topbar-logo {
                transform: translate(6px, -6px);
            }
        }

        /* Full-bleed hero image right under the top bar */
        .hero-bleed {
            position: relative;
            width: 100%;
            max-width: none;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        .hero-bleed-img {
            width: 100%;
            height: min(62vh, 720px);
            object-fit: cover;
            object-position: left top;
            display: block;
            margin: 0;
            /* Fix rare subpixel gaps on the right edge */
            transform: translateZ(0);
        }

        /* Small safety cover for subpixel seams */
        .hero-bleed::before {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            width: 1px;
            background: #000;
            z-index: 1;
            pointer-events: none;
        }

        /* Big title overlay on the hero image */
        .hero-bleed-title {
            position: absolute;
            inset: 0;
            z-index: 2;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 1.25rem;
            padding-top: 8.25rem; /* more space under topbar on mobile */
            pointer-events: none;
            text-align: center;
        }

        .hero-bleed-title h1 {
            margin: 0;
            color: #fff;
            font-weight: 800;
            letter-spacing: -0.02em;
            text-shadow: 0 10px 30px rgba(0,0,0,.55);
            font-size: clamp(3.6rem, 9vw, 7.2rem);
            line-height: 1.02;

            /* Default font */
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
        }

        @media (min-width: 768px) {
            .hero-bleed-title {
                padding-top: 8.5rem;
            }
        }

        @media (min-width: 992px) {
            .hero-bleed-img {
                height: min(70vh, 820px);
            }
            .hero-bleed-overlay {
                padding: 2.5rem;
            }
            .hero-bleed-title {
                padding-top: 9.25rem;
            }
        }

        .cap {
            display: inline-block;
            font-size: 1.18em;
            line-height: 1;
            transform: none;
        }

        /* Home footer (inspired by provided design) */
        .home-footer {
            background: #1f1f1f;
            color: rgba(255,255,255,.8);
            padding: 4rem 0 0;
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

        /* Mobile footer alignment: center everything on narrow screens */
        @media (max-width: 991.98px) {
            .home-footer {
                text-align: center;
            }

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
    </style>
</head>
<body>
<header class="topbar">
    <div class="container topbar-inner">
        <div class="topbar-row">
            <div class="topbar-brand">
                <a href="{{ url('/') }}" class="d-inline-flex align-items-center text-decoration-none">
                    <img class="topbar-logo" src="{{ asset('img/logo1.png') }}" alt="Super Gym logo" loading="eager">
                </a>
            </div>

            <div class="topbar-actions">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-success btn-sm">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-outline-light btn-sm">Odhlásiť</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm">Prihlásiť sa</a>
                    <a href="{{ route('register') }}" class="btn btn-orange btn-sm">Registrácia</a>
                @endauth
            </div>
        </div>
    </div>
</header>

{{-- Big full-width image under the top bar (edge-to-edge) --}}
<section class="hero-bleed">
    <img class="hero-bleed-img" src="{{ asset('img/pozadie7.png') }}" alt="Činky" loading="eager">
    <div class="hero-bleed-title">
        <div class="container">
            <h1><span class="cap">S</span>UPER <span class="cap">G</span>YM</h1>
        </div>
    </div>
</section>

<main class="hero py-5">
    <div class="container hero-content">
        <div class="row align-items-center g-4">
            <div class="col-12 col-lg-6">
                <div class="mb-4">
                    <span class="badge hero-badge">Kalendár • Kredity • Rezervácie</span>
                </div>

                <h1 class="display-5 fw-bold mb-3">Rezervuj si tréning jednoducho.</h1>
                <p class="lead text-muted mb-4">
                    Prehľadný systém pre klientov, trénerov a adminov. Sleduj tréningy v kalendári, spravuj kapacity,
                    kredity a registrácie bez chaosu.
                </p>

                @guest
                    <div class="d-flex flex-column flex-sm-row gap-2 mb-4">
                        <a href="{{ route('register') }}" class="btn btn-orange btn-lg px-4">Začať (Registrácia)</a>
                        <a href="{{ route('login') }}" class="btn btn-outline-home btn-lg px-4">Prihlásiť sa</a>
                    </div>
                @else
                    <div class="d-flex flex-column flex-sm-row gap-2 mb-4">
                        <a href="{{ route('training-calendar.index') }}" class="btn btn-orange btn-lg px-4">Otvoriť kalendár</a>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-light btn-lg px-4">Dashboard</a>
                    </div>
                @endguest

                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <div class="p-3 rounded-3 hero-card h-100">
                            <div class="fw-semibold">Kalendár</div>
                            <div class="text-muted small">Rýchly prehľad tréningov.</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 rounded-3 hero-card h-100">
                            <div class="fw-semibold">Kredity</div>
                            <div class="text-muted small">Platby tréningov kreditmi.</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 rounded-3 hero-card h-100">
                            <div class="fw-semibold">Správa</div>
                            <div class="text-muted small">Admin a tréner nástroje.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="hero-img-wrap" style="aspect-ratio: 1 / 1;">
                            <img class="hero-img" src="{{ asset('img/pozadie7.png') }}" alt="Činky v gyme" loading="lazy">
                        </div>
                    </div>
                </div>

                <section class="home-gallery" aria-label="Galéria">
                    <div class="row g-3 mt-1">
                        <div class="col-6 col-md-4">
                            <a class="gallery-item" href="{{ asset('img/galeria1.png') }}" target="_blank" rel="noopener" aria-label="Galéria obrázok 1">
                                <img src="{{ asset('img/galeria1.png') }}" alt="Galéria 1" loading="lazy">
                            </a>
                        </div>
                        <div class="col-6 col-md-4">
                            <a class="gallery-item" href="{{ asset('img/galeria2.png') }}" target="_blank" rel="noopener" aria-label="Galéria obrázok 2">
                                <img src="{{ asset('img/galeria2.png') }}" alt="Galéria 2" loading="lazy">
                            </a>
                        </div>
                        <div class="col-6 col-md-4">
                            <a class="gallery-item" href="{{ asset('img/galeria3.png') }}" target="_blank" rel="noopener" aria-label="Galéria obrázok 3">
                                <img src="{{ asset('img/galeria3.png') }}" alt="Galéria 3" loading="lazy">
                            </a>
                        </div>
                        <div class="col-6 col-md-4">
                            <a class="gallery-item" href="{{ asset('img/galeria4.png') }}" target="_blank" rel="noopener" aria-label="Galéria obrázok 4">
                                <img src="{{ asset('img/galeria4.png') }}" alt="Galéria 4" loading="lazy">
                            </a>
                        </div>
                        <div class="col-6 col-md-4">
                            <a class="gallery-item" href="{{ asset('img/galeria5.png') }}" target="_blank" rel="noopener" aria-label="Galéria obrázok 5">
                                <img src="{{ asset('img/galeria5.png') }}" alt="Galéria 5" loading="lazy">
                            </a>
                        </div>
                        <div class="col-6 col-md-4">
                            <a class="gallery-item" href="{{ asset('img/galeria6.png') }}" target="_blank" rel="noopener" aria-label="Galéria obrázok 6">
                                <img src="{{ asset('img/galeria6.png') }}" alt="Galéria 6" loading="lazy">
                            </a>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</main>

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
                        <a class="footer-link" href="{{ route('dashboard') }}">Dashboard</a><br>
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

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
    crossorigin="anonymous"
></script>
</body>
</html>
