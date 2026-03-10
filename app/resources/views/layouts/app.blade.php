<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Rezervačný systém 1.0')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!--suppress HtmlUnknownTarget HtmlMissingLocalResource -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
        crossorigin="anonymous"
    >

    <style>
        :root {
            --topbar-height: 70px;
            --brand-orange: #f97316;
            --header-bg: #050505;
        }

        @media (min-width: 768px) {
            :root {
                --topbar-height: 80px;
            }
        }

        body {
            padding-top: var(--topbar-height);
            background: #fff;
        }

        body.overlay-topbar {
            padding-top: 0;
        }

        body.no-topbar {
            padding-top: 0;
            background-image: url("{{ asset('img/pozadie1.jpg') }}");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        body.no-topbar main {
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        body.no-topbar main > .container {
            width: 100%;
        }

        /* NEW: clean, fixed-height header */

        .app-topbar {
            position: fixed;
            inset: 0 0 auto 0;
            height: var(--topbar-height);
            z-index: 1030;
            background-color: rgba(0, 0, 0, 0.92);
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
        }

        .app-topbar-inner {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .app-logo-wrapper {
            display: inline-flex;
            align-items: center;
            max-height: 100%;
        }

        .app-logo {
            display: block;
            max-height: calc(var(--topbar-height) - 18px);
            width: auto;
            object-fit: contain;
        }

        @media (min-width: 768px) {
            .app-logo {
                max-height: calc(var(--topbar-height) - 20px);
            }
        }

        /* Desktop nav (≥ 992px): standard inline layout */

        .app-nav-desktop {
            display: none;
        }

        @media (min-width: 992px) {
            .app-nav-desktop {
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .app-nav-desktop .navbar-nav {
                flex-direction: row;
                align-items: center;
                gap: 0.25rem;
            }
        }

        /* Mobile hamburger button ( < 992px ) */

        .app-nav-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 9999px;
            border: 1px solid rgba(255, 255, 255, 0.32);
            background: transparent;
            color: #fff;
            padding: 0;
            cursor: pointer;
        }

        .app-nav-toggle-icon {
            position: relative;
            width: 20px;
            height: 18px;
        }

        .app-nav-toggle-icon span {
            position: absolute;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: #fff;
            border-radius: 9999px;
            transition:
                transform .18s ease,
                opacity .18s ease,
                top .18s ease,
                bottom .18s ease;
        }

        .app-nav-toggle-icon span:nth-child(1) {
            top: 0;
        }

        .app-nav-toggle-icon span:nth-child(2) {
            top: 8px;
        }

        .app-nav-toggle-icon span:nth-child(3) {
            bottom: 0;
        }

        .app-nav-toggle[aria-expanded="true"] .app-nav-toggle-icon span:nth-child(1) {
            top: 8px;
            transform: rotate(45deg);
        }

        .app-nav-toggle[aria-expanded="true"] .app-nav-toggle-icon span:nth-child(2) {
            opacity: 0;
        }

        .app-nav-toggle[aria-expanded="true"] .app-nav-toggle-icon span:nth-child(3) {
            bottom: 8px;
            transform: rotate(-45deg);
        }

        /* Mobile menu overlay (panel + backdrop) */

        .app-mobile-shell {
            position: fixed;
            top: var(--topbar-height);
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1025;
            pointer-events: none;
        }

        .app-mobile-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.65);
            opacity: 0;
            visibility: hidden;
            transition:
                opacity .18s ease,
                visibility 0s linear .18s;
        }

        .app-mobile-panel {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            max-height: 100%;
            background-color: #050505;
            padding: 0.75rem 1.0rem 1.25rem;
            transform: translateY(-12px);
            opacity: 0;
            visibility: hidden;
            transition:
                opacity .18s ease-out,
                transform .18s ease-out,
                visibility 0s linear .18s;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .app-mobile-shell.is-open {
            pointer-events: auto;
        }

        .app-mobile-shell.is-open .app-mobile-backdrop {
            opacity: 1;
            visibility: visible;
            transition:
                opacity .18s ease,
                visibility 0s linear 0s;
        }

        .app-mobile-shell.is-open .app-mobile-panel {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
            transition:
                opacity .18s ease-out,
                transform .18s ease-out,
                visibility 0s linear 0s;
        }

        /* Mobile menu content */

        .app-mobile-nav-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: .75rem;
        }

        .app-mobile-user {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.85);
        }

        .app-mobile-credits-badge {
            align-self: flex-start;
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.6rem;
            border-radius: 9999px;
            background-color: var(--brand-orange);
            color: #fff;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .app-mobile-nav-section + .app-mobile-nav-section {
            margin-top: .75rem;
            padding-top: .75rem;
            border-top: 1px solid rgba(255, 255, 255, 0.16);
        }

        .app-mobile-nav-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .app-mobile-nav-list > li + li {
            margin-top: 0.25rem;
        }

        .app-mobile-link,
        .app-mobile-link-button {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            width: 100%;
            border-radius: 10px;
            padding: 0.55rem 0.75rem;
            color: rgba(255, 255, 255, 0.90);
            text-decoration: none;
            border: 1px solid transparent;
            background: transparent;
            font-size: 0.95rem;
        }

        .app-mobile-link:hover,
        .app-mobile-link:focus {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.06);
            border-color: rgba(255, 255, 255, 0.14);
            text-decoration: none;
        }

        .app-mobile-link.active {
            background-color: rgba(249, 115, 22, 0.18);
            border-color: rgba(249, 115, 22, 0.5);
            color: #fff;
        }

        .app-mobile-link-button {
            justify-content: center;
            background-color: var(--brand-orange);
            border-color: var(--brand-orange);
            color: #fff;
            font-weight: 600;
        }

        .app-mobile-link-button:hover,
        .app-mobile-link-button:focus {
            background-color: #ea6a0f;
            border-color: #ea6a0f;
            color: #fff;
        }

        .app-mobile-subtitle {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: rgba(255, 255, 255, 0.5);
            margin-bottom: 0.3rem;
        }

        .text-brand-orange {
            color: var(--brand-orange) !important;
        }

        body.homepage .dropdown-menu {
            z-index: 99999999 !important;
        }
    </style>
</head>
<body class="{{ url()->current() === url('/') ? 'homepage' : '' }} @if(!empty($hideTopbar)) no-topbar @elseif(!empty($overlayTopbar)) overlay-topbar @endif">

@if(empty($hideTopbar))
    @php
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        $isAdmin      = $user && $user->isAdmin();
        $isReception  = $user && $user->isReception();
        $isTrainer    = $user && $user->isTrainer();
        $isRegular    = $user && $user->isRegularUser();
        $trainOpen    = request()->routeIs('admin.trainings.*');
        $annOpen      = request()->routeIs('admin.announcements.*') || request()->routeIs('announcements.*');
    @endphp

    <nav class="app-topbar">
        <div class="container app-topbar-inner">

            {{-- Left: Logo (always fully inside header) --}}
            <a class="app-logo-wrapper" href="{{ url('/') }}">
                <img class="app-logo" src="{{ asset('img/logo1.png') }}" alt="Super Gym logo" loading="eager">
            </a>

            {{-- Desktop nav (≥ 992px) --}}
            <div class="app-nav-desktop w-100 ms-3">
                <div class="d-flex align-items-center justify-content-between w-100">
                    {{-- Left: nav links --}}
                    <ul class="navbar-nav me-auto mb-0">
                        @auth
                            @if($isReception)
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ url('/') }}">Home</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('reception.calendar') }}">Kalendár tréningov</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('announcements.index') }}">Oznamy</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('reception.credits.create') }}">Pridanie kreditov</a>
                                </li>
                            @else
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ url('/') }}">Home</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('training-calendar.index') }}">Kalendár tréningov</a>
                                </li>

                                @if($isRegular)
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('my-trainings.index') }}">Moje tréningy</a>
                                    </li>
                                @endif

                                @if($isAdmin)
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('admin.users.index') }}">Používatelia</a>
                                    </li>

                                    {{-- Tréningy dropdown (desktop) --}}
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle {{ $trainOpen ? 'active' : '' }}"
                                           href="#"
                                           id="trainingsDropdown"
                                           role="button"
                                           data-bs-toggle="dropdown"
                                           aria-expanded="false">
                                            Tréningy
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-dark shadow" aria-labelledby="trainingsDropdown">
                                            <li>
                                                <a class="dropdown-item {{ request()->routeIs('admin.trainings.index') ? 'active' : '' }}" href="{{ route('admin.trainings.index') }}">
                                                    Správa aktuálnych tréningov
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item {{ request()->routeIs('admin.trainings.archive') ? 'active' : '' }}" href="{{ route('admin.trainings.archive') }}">
                                                    Archív tréningov
                                                </a>
                                            </li>
                                        </ul>
                                    </li>

                                    {{-- Oznamy dropdown (desktop) --}}
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle {{ $annOpen ? 'active' : '' }}"
                                           href="#"
                                           id="announcementsDropdown"
                                           role="button"
                                           data-bs-toggle="dropdown"
                                           aria-expanded="false">
                                            Oznamy
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-dark shadow" aria-labelledby="announcementsDropdown">
                                            <li>
                                                <a class="dropdown-item {{ request()->routeIs('admin.announcements.index') ? 'active' : '' }}" href="{{ route('admin.announcements.index') }}">
                                                    Správa oznamov
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item {{ request()->routeIs('admin.announcements.archive') ? 'active' : '' }}" href="{{ route('admin.announcements.archive') }}">
                                                    Archív oznamov
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item {{ request()->routeIs('announcements.index') ? 'active' : '' }}" href="{{ route('announcements.index') }}">
                                                    Oznamy (zobrazenie)
                                                </a>
                                            </li>
                                        </ul>
                                    </li>

                                    {{-- Nastavenia as last admin item --}}
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.edit') }}">Nastavenia</a>
                                    </li>

                                @else
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('announcements.index') }}">Oznamy</a>
                                    </li>
                                @endif
                            @endif

                            @if($isTrainer)
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('trainer.trainings.create') }}">Vytvorenie tréningu</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('trainer.trainings.index') }}">Vytvorené tréningy</a>
                                </li>
                            @endif
                        @endauth
                    </ul>

                    {{-- Right: user controls --}}
                    <ul class="navbar-nav ms-auto mb-0">
                        @guest
                            <li class="nav-item me-2">
                                <a class="nav-link text-light" href="{{ route('login') }}">Prihlásiť sa</a>
                            </li>
                            <li class="nav-item">
                                <a class="btn btn-sm" style="background-color: var(--brand-orange); color:#fff; border-radius: 9999px; padding-inline: 1rem;"
                                   href="{{ route('register') }}">
                                    Registrácia
                                </a>
                            </li>
                        @else
                            <li class="nav-item d-flex align-items-center me-3">
                                @if($isRegular)
                                    <span class="badge me-2" id="userCreditsBadge" style="background-color: var(--brand-orange); color:#fff; border-radius:9999px;">
                                        Kredity: {{ $user->credits ?? 0 }}
                                    </span>
                                @endif
                                <span class="navbar-text small text-white-50">{{ $user->name }}</span>
                            </li>
                            <li class="nav-item">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-light btn-sm">Odhlásiť</button>
                                </form>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>

            {{-- Mobile hamburger ( < 992px ) --}}
            <button
                class="app-nav-toggle d-lg-none"
                type="button"
                id="appMobileToggle"
                aria-expanded="false"
                aria-controls="appMobileMenu"
            >
                <span class="visually-hidden">Toggle navigation</span>
                <span class="app-nav-toggle-icon" aria-hidden="true">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </button>
        </div>
    </nav>

    {{-- Mobile overlay menu (full-width under header) --}}
    <div class="app-mobile-shell d-lg-none" id="appMobileShell">
        <div class="app-mobile-backdrop" id="appMobileBackdrop"></div>
        <div class="app-mobile-panel" id="appMobileMenu" role="menu">
            <div class="app-mobile-nav-header">
                @auth
                    <div class="app-mobile-user">
                        <span class="fw-semibold">{{ $user->name }}</span>
                        @if($isRegular)
                            <span class="app-mobile-credits-badge" id="userCreditsBadgeMobile">
                                Kredity: {{ $user->credits ?? 0 }}
                            </span>
                        @endif
                    </div>
                @else
                    <div class="app-mobile-user">
                        <span class="fw-semibold">Vitajte v Super Gym</span>
                        <span style="font-size: 0.8rem; color: rgba(255,255,255,0.6);">Prihláste sa alebo si vytvorte účet.</span>
                    </div>
                @endauth

                {{-- Close button inside panel for easy reach --}}
                <button
                    type="button"
                    class="btn btn-sm btn-outline-light rounded-pill ms-3"
                    id="appMobileClose"
                >
                    Zavrieť
                </button>
            </div>

            {{-- Main navigation sections --}}
            <div class="app-mobile-nav-section">
                <div class="app-mobile-subtitle">Navigácia</div>
                <ul class="app-mobile-nav-list">
                    @auth
                        @if($isReception)
                            <li><a class="app-mobile-link" href="{{ url('/') }}">Home</a></li>
                            <li><a class="app-mobile-link" href="{{ route('reception.calendar') }}">Kalendár tréningov</a></li>
                            <li><a class="app-mobile-link" href="{{ route('announcements.index') }}">Oznamy</a></li>
                            <li><a class="app-mobile-link" href="{{ route('reception.credits.create') }}">Pridanie kreditov</a></li>
                        @else
                            <li><a class="app-mobile-link" href="{{ url('/') }}">Home</a></li>
                            <li><a class="app-mobile-link" href="{{ route('training-calendar.index') }}">Kalendár tréningov</a></li>

                            @if($isRegular)
                                <li><a class="app-mobile-link" href="{{ route('my-trainings.index') }}">Moje tréningy</a></li>
                            @endif

                            @if($isAdmin)
                                <li>
                                    <div class="app-mobile-subtitle mt-2">Administrácia</div>
                                </li>
                                <li><a class="app-mobile-link {{ request()->routeIs('admin.users.index') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">Používatelia</a></li>
                                <li><a class="app-mobile-link {{ request()->routeIs('admin.trainings.index') ? 'active' : '' }}" href="{{ route('admin.trainings.index') }}">Správa aktuálnych tréningov</a></li>
                                <li><a class="app-mobile-link {{ request()->routeIs('admin.trainings.archive') ? 'active' : '' }}" href="{{ route('admin.trainings.archive') }}">Archív tréningov</a></li>
                                <li><a class="app-mobile-link {{ request()->routeIs('admin.announcements.index') ? 'active' : '' }}" href="{{ route('admin.announcements.index') }}">Správa oznamov</a></li>
                                <li><a class="app-mobile-link {{ request()->routeIs('admin.announcements.archive') ? 'active' : '' }}" href="{{ route('admin.announcements.archive') }}">Archív oznamov</a></li>
                                <li><a class="app-mobile-link {{ request()->routeIs('announcements.index') ? 'active' : '' }}" href="{{ route('announcements.index') }}">Oznamy (zobrazenie)</a></li>
                                <li><a class="app-mobile-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.edit') }}">Nastavenia</a></li>
                            @else
                                <li><a class="app-mobile-link {{ request()->routeIs('announcements.index') ? 'active' : '' }}" href="{{ route('announcements.index') }}">Oznamy</a></li>
                            @endif
                        @endif

                        @if($isTrainer)
                            <li class="mt-2">
                                <div class="app-mobile-subtitle">Tréner</div>
                            </li>
                            <li><a class="app-mobile-link" href="{{ route('trainer.trainings.create') }}">Vytvorenie tréningu</a></li>
                            <li><a class="app-mobile-link" href="{{ route('trainer.trainings.index') }}">Vytvorené tréningy</a></li>
                        @endif
                    @endauth

                    @guest
                        <li><a class="app-mobile-link" href="{{ url('/') }}">Home</a></li>
                        <li><a class="app-mobile-link" href="{{ route('training-calendar.index') }}">Kalendár tréningov</a></li>
                        <li><a class="app-mobile-link" href="{{ route('announcements.index') }}">Oznamy</a></li>
                    @endguest
                </ul>
            </div>

            <div class="app-mobile-nav-section">
                <div class="app-mobile-subtitle">Účet</div>
                <ul class="app-mobile-nav-list">
                    @guest
                        <li>
                            <a class="app-mobile-link" href="{{ route('login') }}">Prihlásiť sa</a>
                        </li>
                        <li>
                            <a class="app-mobile-link-button" href="{{ route('register') }}">
                                Registrácia
                            </a>
                        </li>
                    @else
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="app-mobile-link-button">
                                    Odhlásiť
                                </button>
                            </form>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </div>
@endif

<main class="@if(!empty($overlayTopbar)) p-0 @else py-4 @endif">
    <div class="container" @if(empty($overlayTopbar)) style="padding-top:20px;" @endif>
        @yield('content')
    </div>
</main>

<!--suppress HtmlUnknownTarget HtmlMissingLocalResource -->
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
    crossorigin="anonymous"
></script>

<script>
    (function () {
        const shell     = document.getElementById('appMobileShell');
        const panel     = document.getElementById('appMobileMenu');
        const backdrop  = document.getElementById('appMobileBackdrop');
        const toggleBtn = document.getElementById('appMobileToggle');
        const closeBtn  = document.getElementById('appMobileClose');

        if (!shell || !panel || !backdrop || !toggleBtn) return;

        let isOpen = false;

        function openMenu() {
            if (isOpen) return;
            isOpen = true;
            shell.classList.add('is-open');
            toggleBtn.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden'; // lock scroll
        }

        function closeMenu() {
            if (!isOpen) return;
            isOpen = false;
            shell.classList.remove('is-open');
            toggleBtn.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = ''; // restore scroll
        }

        // Toggle button
        toggleBtn.addEventListener('click', function () {
            if (isOpen) {
                closeMenu();
            } else {
                openMenu();
            }
        });

        // Close button inside panel
        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                closeMenu();
            });
        }

        // Click outside (backdrop)
        backdrop.addEventListener('click', function () {
            closeMenu();
        });

        // Close on ESC
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' || e.key === 'Esc' || e.keyCode === 27) {
                closeMenu();
            }
        });

        // Close on navigation link / form submit inside panel
        panel.addEventListener('click', function (e) {
            const link = e.target.closest('a');
            const button = e.target.closest('button');
            const form = e.target.closest('form');

            if (form && form.getAttribute('method')?.toLowerCase() === 'post') {
                // Let logout POST happen; menu will disappear on page load
                closeMenu();
                return;
            }

            if (link) {
                const href = link.getAttribute('href') || '';
                if (href && href !== '#') {
                    closeMenu();
                }
            } else if (button && button.type === 'submit') {
                closeMenu();
            }
        });

        // Ensure menu is closed if viewport becomes desktop
        function handleResize() {
            if (window.innerWidth >= 992) {
                closeMenu();
            }
        }

        window.addEventListener('resize', handleResize);
    })();
</script>

@auth
    @php
        /** @var \App\Models\User $user */
        $user = auth()->user();
    @endphp
    @if($user && $user->isRegularUser())
        <script>
            (function(){
                const badge = document.getElementById('userCreditsBadge');
                const badgeMobile = document.getElementById('userCreditsBadgeMobile');
                if (!badge && !badgeMobile) return;

                function setBadgeCredits(value) {
                    if (badge) badge.textContent = `Kredity: ${value}`;
                    if (badgeMobile) badgeMobile.textContent = `Kredity: ${value}`;
                }

                try {
                    window.addEventListener('credits:updated', function (ev) {
                        try {
                            const v = ev?.detail?.credits;
                            if (typeof v !== 'undefined') setBadgeCredits(v);
                        } catch (e) { /* ignore */ }
                    });
                } catch (e) {}

                async function pollMyCredits() {
                    try {
                        const routeUrl = @json(route('me.credits'));
                        if (!routeUrl) return;
                        const res = await fetch(routeUrl, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        });
                        if (!res.ok) return;

                        const data = await res.json().catch(() => null);
                        if (!data || typeof data.credits === 'undefined') return;

                        const sourceText = (badge?.textContent || badgeMobile?.textContent || '');
                        const current = parseInt(sourceText.replace(/\D+/g, ''), 10) || 0;
                        if (data.credits !== current) {
                            setBadgeCredits(data.credits);
                        }
                    } catch (e) {
                        // ignore transient network errors
                    }
                }

                pollMyCredits();
                setInterval(pollMyCredits, 5000);
            })();
        </script>
    @endif
@endauth
</body>
</html>
