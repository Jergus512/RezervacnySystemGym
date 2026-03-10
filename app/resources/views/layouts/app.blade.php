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
            gap: 1.5rem;
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
                gap: 1.5rem;
            }

            .app-nav-desktop .navbar-nav {
                flex-direction: row;
                align-items: center;
                gap: 1.25rem;
            }

            .app-nav-desktop .nav-link {
                color: #fff !important;
                font-weight: 500;
                padding-inline: 0.25rem;
            }

            .app-nav-desktop .nav-link:hover,
            .app-nav-desktop .nav-link:focus,
            .app-nav-desktop .nav-link.active {
                color: var(--brand-orange) !important;
            }

            .app-nav-desktop .dropdown-menu-dark {
                background-color: #111827;
            }
        }

        .app-topbar-user {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            white-space: nowrap;
        }

        .app-topbar-username {
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.9rem;
        }

        .app-topbar-credits {
            display: inline-flex;
            align-items: center;
            padding: 0.15rem 0.6rem;
            border-radius: 9999px;
            background-color: var(--brand-orange);
            color: #fff;
            font-size: 0.8rem;
            font-weight: 600;
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
            padding: 0.75rem 1rem 1.25rem;
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

        .app-mobile-submenu-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            border-radius: 10px;
            padding: 0.55rem 0.75rem;
            color: rgba(255, 255, 255, 0.90);
            background: transparent;
            border: 1px solid transparent;
            font-size: 0.95rem;
            cursor: pointer;
        }

        .app-mobile-submenu-toggle:hover,
        .app-mobile-submenu-toggle:focus {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.06);
            border-color: rgba(255, 255, 255, 0.14);
        }

        .app-mobile-submenu-chevron {
            transition: transform .18s ease;
            font-size: 0.8rem;
        }

        .app-mobile-submenu[hidden] {
            display: none;
        }

        .app-mobile-submenu-toggle[aria-expanded="true"] .app-mobile-submenu-chevron {
            transform: rotate(180deg);
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

            <a class="app-logo-wrapper" href="{{ url('/') }}">
                <img class="app-logo" src="{{ asset('img/logo1.png') }}" alt="Super Gym logo" loading="eager">
            </a>

            {{-- Desktop nav (≥ 992px) --}}
            <div class="app-nav-desktop w-100 d-none d-lg-flex align-items-center">
                <div class="d-flex align-items-center justify-content-between w-100">
                    {{-- Left: nav links (AUTH ONLY) --}}
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

                    {{-- Right: user controls / auth links --}}
                    <ul class="navbar-nav ms-auto mb-0">
                        @guest
                            <li class="nav-item me-3">
                                <a class="nav-link" href="{{ route('login') }}">Prihlásiť sa</a>
                            </li>
                            <li class="nav-item">
                                <a class="btn btn-sm" style="background-color: var(--brand-orange); color:#fff; border-radius: 9999px; padding-inline: 1rem;"
                                   href="{{ route('register') }}">
                                    Registrácia
                                </a>
                            </li>
                        @else
                            <li class="nav-item d-flex align-items-center me-3">
                                <div class="app-topbar-user">
                                    @if($isRegular)
                                        <span class="app-topbar-credits" id="userCreditsBadge">
                                            Kredity: {{ $user->credits ?? 0 }}
                                        </span>
                                    @endif
                                    <span class="app-topbar-username">{{ $user->name }}</span>
                                </div>
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

            {{-- Right side for mobile: user + burger / auth links --}}
            <div class="d-flex align-items-center ms-auto d-lg-none" style="gap: 0.75rem;">
                @auth
                    <div class="d-flex flex-column align-items-end" style="line-height: 1.1;">
                        <span class="app-topbar-username small">{{ $user->name }}</span>
                        @if($isRegular)
                            <span class="app-topbar-credits mt-1" id="userCreditsBadgeMobile">
                                Kredity: {{ $user->credits ?? 0 }}
                            </span>
                        @endif
                    </div>

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
                @endauth

                @guest
                    <a class="nav-link me-2" style="color:#fff;" href="{{ route('login') }}">Prihlásiť sa</a>
                    <a class="btn btn-sm" style="background-color: var(--brand-orange); color:#fff; border-radius: 9999px; padding-inline: 1rem;" href="{{ route('register') }}">Registrácia</a>
                @endguest
            </div>
        </div>
    </nav>

    {{-- Mobile overlay menu (AUTH ONLY) --}}
    @auth
        <div class="app-mobile-shell d-lg-none" id="appMobileShell">
            <div class="app-mobile-backdrop" id="appMobileBackdrop"></div>
            <div class="app-mobile-panel" id="appMobileMenu" role="menu">
                <ul class="app-mobile-nav-list mb-3">
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
                            <li><a class="app-mobile-link {{ request()->routeIs('admin.users.index') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">Používatelia</a></li>

                            <li>
                                <button class="app-mobile-submenu-toggle" type="button" data-mobile-submenu-toggle="trainings">
                                    <span>Tréningy</span>
                                    <span class="app-mobile-submenu-chevron">▾</span>
                                </button>
                                <ul class="app-mobile-nav-list mt-1" data-mobile-submenu="trainings" hidden>
                                    <li><a class="app-mobile-link {{ request()->routeIs('admin.trainings.index') ? 'active' : '' }}" href="{{ route('admin.trainings.index') }}">Správa aktuálnych tréningov</a></li>
                                    <li><a class="app-mobile-link {{ request()->routeIs('admin.trainings.archive') ? 'active' : '' }}" href="{{ route('admin.trainings.archive') }}">Archív tréningov</a></li>
                                </ul>
                            </li>

                            <li>
                                <button class="app-mobile-submenu-toggle" type="button" data-mobile-submenu-toggle="announcements">
                                    <span>Oznamy</span>
                                    <span class="app-mobile-submenu-chevron">▾</span>
                                </button>
                                <ul class="app-mobile-nav-list mt-1" data-mobile-submenu="announcements" hidden>
                                    <li><a class="app-mobile-link {{ request()->routeIs('admin.announcements.index') ? 'active' : '' }}" href="{{ route('admin.announcements.index') }}">Správa oznamov</a></li>
                                    <li><a class="app-mobile-link {{ request()->routeIs('admin.announcements.archive') ? 'active' : '' }}" href="{{ route('admin.announcements.archive') }}">Archív oznamov</a></li>
                                    <li><a class="app-mobile-link {{ request()->routeIs('announcements.index') ? 'active' : '' }}" href="{{ route('announcements.index') }}">Oznamy (zobrazenie)</a></li>
                                </ul>
                            </li>

                            <li><a class="app-mobile-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.edit') }}">Nastavenia</a></li>
                        @else
                            <li><a class="app-mobile-link {{ request()->routeIs('announcements.index') ? 'active' : '' }}" href="{{ route('announcements.index') }}">Oznamy</a></li>
                        @endif
                    @endif

                    @if($isTrainer)
                        <li><a class="app-mobile-link" href="{{ route('trainer.trainings.create') }}">Vytvorenie tréningu</a></li>
                        <li><a class="app-mobile-link" href="{{ route('trainer.trainings.index') }}">Vytvorené tréningy</a></li>
                    @endif
                </ul>

                <ul class="app-mobile-nav-list">
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="app-mobile-link-button">
                                Odhlásiť
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    @endauth
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

            // close all mobile submenus
            panel.querySelectorAll('[data-mobile-submenu]').forEach(function (submenu) {
                submenu.hidden = true;
            });
            panel.querySelectorAll('[data-mobile-submenu-toggle]').forEach(function (btn) {
                btn.setAttribute('aria-expanded', 'false');
            });
        }

        // Toggle button
        toggleBtn.addEventListener('click', function () {
            isOpen ? closeMenu() : openMenu();
        });

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

        // Close on navigation (any link inside mobile menu)
        panel.addEventListener('click', function (e) {
            const link = e.target.closest('a');
            if (link && link.getAttribute('href') && !link.getAttribute('href').startsWith('#')) {
                closeMenu();
            }
        });

        // Mobile submenus (Oznamy, Tréningy, ...)
        panel.querySelectorAll('[data-mobile-submenu-toggle]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const key = btn.getAttribute('data-mobile-submenu-toggle');
                if (!key) return;

                const submenu = panel.querySelector('[data-mobile-submenu="' + key + '"]');
                if (!submenu) return;

                const isCurrentlyOpen = !submenu.hidden;

                // close all first for accordion-like behavior
                panel.querySelectorAll('[data-mobile-submenu]').forEach(function (s) {
                    s.hidden = true;
                });
                panel.querySelectorAll('[data-mobile-submenu-toggle]').forEach(function (b) {
                    b.setAttribute('aria-expanded', 'false');
                });

                // then open the clicked one if it was previously closed
                if (!isCurrentlyOpen) {
                    submenu.hidden = false;
                    btn.setAttribute('aria-expanded', 'true');
                }
            });
        });
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
