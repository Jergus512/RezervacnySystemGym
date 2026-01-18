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

        .hero {
            position: relative;
            overflow: hidden;
            background: radial-gradient(1200px circle at 20% 10%, rgba(13,110,253,.15), transparent 45%),
                        radial-gradient(900px circle at 80% 0%, rgba(25,135,84,.12), transparent 40%),
                        linear-gradient(180deg, #0b1220, #0a0f1c);
            color: #fff;
        }

        .hero::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, rgba(0,0,0,.65), rgba(0,0,0,.25));
            pointer-events: none;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-card {
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.12);
            backdrop-filter: blur(10px);
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

        .hero-img-wrap::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(0,0,0,.0), rgba(0,0,0,.35));
            pointer-events: none;
        }

        .hero-badge {
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.18);
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
            padding-top: 7.25rem; /* push title below the overlaid topbar */
            pointer-events: none;
            text-align: center;
        }

        .hero-bleed-title h1 {
            margin: 0;
            color: #fff;
            font-weight: 800;
            letter-spacing: -0.02em;
            text-shadow: 0 10px 30px rgba(0,0,0,.55);
            font-size: clamp(3rem, 8vw, 6rem);
            line-height: 1.02;
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
    </style>
</head>
<body class="bg-light">
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
    <img class="hero-bleed-img" src="{{ asset('img/hero-gym-1.png') }}" alt="Činky" loading="eager">
    <div class="hero-bleed-title">
        <div class="container">
            <h1>Super Gym</h1>
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
                <p class="lead text-white-50 mb-4">
                    Prehľadný systém pre klientov, trénerov a adminov. Sleduj tréningy v kalendári, spravuj kapacity,
                    kredity a registrácie bez chaosu.
                </p>

                @guest
                    <div class="d-flex flex-column flex-sm-row gap-2 mb-4">
                        <a href="{{ route('register') }}" class="btn btn-orange btn-lg px-4">Začať (Registrácia)</a>
                        <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg px-4">Prihlásiť sa</a>
                    </div>
                @else
                    <div class="d-flex flex-column flex-sm-row gap-2 mb-4">
                        <a href="{{ route('training-calendar.index') }}" class="btn btn-primary btn-lg px-4">Otvoriť kalendár</a>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-light btn-lg px-4">Dashboard</a>
                    </div>
                @endguest

                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <div class="p-3 rounded-3 hero-card h-100">
                            <div class="fw-semibold">Kalendár</div>
                            <div class="text-white-50 small">Rýchly prehľad tréningov.</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 rounded-3 hero-card h-100">
                            <div class="fw-semibold">Kredity</div>
                            <div class="text-white-50 small">Platby tréningov kreditmi.</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 rounded-3 hero-card h-100">
                            <div class="fw-semibold">Správa</div>
                            <div class="text-white-50 small">Admin a tréner nástroje.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="hero-img-wrap" style="aspect-ratio: 16/9;">
                            <img class="hero-img" src="{{ asset('img/hero-gym-2.png') }}" alt="Gym interiér" loading="lazy">
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="hero-img-wrap" style="aspect-ratio: 1 / 1;">
                            <img class="hero-img" src="{{ asset('img/hero-gym-1.png') }}" alt="Činky v gyme" loading="lazy">
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="p-4 rounded-4 hero-card h-100">
                            <h2 class="h5 fw-semibold">Čo tu nájdeš</h2>
                            <ul class="text-white-50 mb-0">
                                <li>Registrácia na tréningy jedným klikom</li>
                                <li>Kapacita, cena a zoznam prihlásených</li>
                                <li>Admin editácia tréningov aj používateľov</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <p class="text-white-50 small mt-3 mb-0">
                    Tip: Ak chceš zmeniť obrázky, nahraj ich do <code class="text-white">public/img</code> a uprav názvy v šablóne.
                </p>
            </div>
        </div>
    </div>
</main>

<footer class="py-4 bg-dark text-white-50">
    <div class="container d-flex flex-column flex-md-row justify-content-between gap-2">
        <div>© {{ date('Y') }} Rezervačný systém Gym</div>
        <div class="small">Vytvorené pre prezentáciu • Laravel</div>
    </div>
</footer>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
    crossorigin="anonymous"
></script>
</body>
</html>
