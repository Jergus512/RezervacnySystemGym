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
            --topbar-height: 72px;
            --brand-orange: #f97316;
        }

        /* Home overlay-topbar: keep default Bootstrap behavior (no forced open/closed).
           We only do mobile centering when the burger menu is actually expanded. */

        @media (min-width: 768px) {
            :root {
                --topbar-height: 86px;
            }
        }

        body {
            padding-top: var(--topbar-height);
            background: #fff;
        }

        /* Pages that want the hero image to start at the very top (topbar overlays content). */
        body.overlay-topbar {
            padding-top: 0;
        }

        body.no-topbar {
            padding-top: 0;

            /* Full-page background image for auth pages */
            background-image: url("{{ asset('img/pozadie1.jpg') }}");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        /* Center auth pages (no topbar) */
        body.no-topbar main {
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        body.no-topbar main > .container {
            width: 100%;
        }

        .app-topbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;

            /* transparent dark glass */
            /* Move the visual backdrop into a pseudo-element so the blur is strictly
               confined to the topbar rectangle. This avoids leaving a blurred
               rectangle artifact below when the mobile menu opens/closes. */
            background: transparent;
            border-bottom: 0; /* moved to pseudo-element */
        }

        /* Background layer for the topbar (covers only the topbar area).
           Keep pointer-events disabled so it never blocks interactions. */
        .app-topbar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            /* keep the pseudo-element limited to the original topbar rectangle
               (when the mobile menu expands the .app-topbar element grows, but
               we don't want the blur to cover that expanded area). */
            /* extend the height slightly so logo drop-shadows don't visually protrude */
            height: calc(var(--topbar-height) + 12px);
            background: rgba(0, 0, 0, 0.55);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.14);
            pointer-events: none;
            z-index: 1030; /* sits at the same stacking context as the topbar */
            /* Smooth opacity/height transitions avoid a hard flicker when toggling */
            transition: opacity .12s linear, height .18s ease;
            opacity: 1;
        }

        /* When the mobile menu is opened we expand the pseudo-element so the
           collapsed mobile menu area also gets a blurred backdrop. We only do
           this for smaller screens so desktop dropdowns keep their normal behavior. */
        @media (max-width: 991.98px) {
            .app-topbar.menu-open::before {
                /* full viewport height so the expanded mobile menu has a blurred backdrop */
                height: 100vh;
            }
        }

        /* Ensure interactive content within the topbar appears above the pseudo-element. */
        .app-topbar .container,
        .app-topbar .navbar-brand,
        .app-topbar .navbar-toggler,
        .app-topbar .navbar-collapse {
            position: relative;
            z-index: 1031;
        }

        /* Guest link in topbar (Prihlásiť) */
        .topbar-link {
            color: rgba(255,255,255,.9);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            line-height: 1;
            padding: .45rem .8rem;
            border-radius: 8px;
            border: 1px solid transparent;
        }

        .topbar-link:hover,
        .topbar-link:focus {
            color: #fff;
            text-decoration: none;
            border-color: rgba(255,255,255,.85);
            background: rgba(255,255,255,.08);
        }

        .topbar-btn-orange {
            display: inline-flex;
            align-items: center;
            background: var(--brand-orange);
            color: #fff;
            border-radius: 8px;
            padding: .45rem .9rem;
            line-height: 1;
            border: 1px solid var(--brand-orange);
        }

        .topbar-btn-orange:hover,
        .topbar-btn-orange:focus {
            background: #ea6a0f;
            border-color: #ea6a0f;
            color: #fff;
        }

        /* Credits badge (regular user) */
        .topbar-credits {
            background: var(--brand-orange) !important;
            color: #fff !important;
            border: 1px solid rgba(255,255,255,.18);
            font-weight: 700;
        }

        .app-topbar-inner {
            padding: .45rem 0;
        }

        @media (min-width: 768px) {
            .app-topbar-inner {
                padding: .6rem 0;
            }
        }

        .app-logo {
            height: 58px;
            width: auto;
            max-width: 100%;
            display: block;
            /* reduce the shadow so the logo doesn't visually poke outside the topbar */
            filter: drop-shadow(0 6px 10px rgba(0,0,0,.25));
        }

        @media (min-width: 768px) {
            .app-logo {
                height: 70px;
            }
        }

        /* Mobile burger menu: center items when expanded (no flicker) */
        @media (max-width: 991.98px) {
            /* Keep the collapsed area text-centered at all times to avoid left->center flicker.
               Do NOT touch display/visibility here; Bootstrap controls that. */
            .app-topbar .navbar-collapse {
                text-align: center;
            }

            /* Bootstrap uses .collapsing during the open/close animation.
               Apply the same layout rules there to avoid the text jumping left/right. */
            .app-topbar .navbar-collapse.show,
            .app-topbar .navbar-collapse.collapsing {
                padding-top: .75rem;
            }

            /* Stack and center both nav groups when expanded OR animating */
            .app-topbar .navbar-collapse.show .navbar-nav,
            .app-topbar .navbar-collapse.collapsing .navbar-nav {
                width: 100%;
                align-items: center;
                justify-content: center;
                text-align: center;
            }

            .app-topbar .navbar-collapse.show .navbar-nav.me-auto,
            .app-topbar .navbar-collapse.show .navbar-nav.ms-auto,
            .app-topbar .navbar-collapse.collapsing .navbar-nav.me-auto,
            .app-topbar .navbar-collapse.collapsing .navbar-nav.ms-auto {
                flex-direction: column;
                margin-left: 0 !important;
                margin-right: 0 !important;
            }

            /* Optional separator before account/actions */
            .app-topbar .navbar-collapse.show .navbar-nav.ms-auto,
            .app-topbar .navbar-collapse.collapsing .navbar-nav.ms-auto {
                margin-top: .5rem;
                padding-top: .5rem;
                border-top: 1px solid rgba(255,255,255,.15);
            }

            /* One item per line, centered */
            .app-topbar .navbar-collapse.show .navbar-nav .nav-item,
            .app-topbar .navbar-collapse.collapsing .navbar-nav .nav-item {
                width: 100%;
                display: flex;
                justify-content: center;
            }

            .app-topbar .navbar-collapse.show .navbar-nav .nav-link,
            .app-topbar .navbar-collapse.show .navbar-nav .btn,
            .app-topbar .navbar-collapse.collapsing .navbar-nav .nav-link,
            .app-topbar .navbar-collapse.collapsing .navbar-nav .btn {
                width: 100%;
                justify-content: center;
                text-align: center;
                white-space: nowrap;
            }

            /* Center the user info row (name/credits) */
            .app-topbar .navbar-collapse.show .navbar-nav .nav-item.d-flex,
            .app-topbar .navbar-collapse.collapsing .navbar-nav .nav-item.d-flex {
                flex-direction: column;
                align-items: center;
                gap: .35rem;
            }

            /* Remove desktop spacing helpers on mobile expanded menu */
            .app-topbar .navbar-collapse.show .navbar-nav .nav-item.ms-2,
            .app-topbar .navbar-collapse.show .navbar-nav .nav-item.me-2,
            .app-topbar .navbar-collapse.collapsing .navbar-nav .nav-item.ms-2,
            .app-topbar .navbar-collapse.collapsing .navbar-nav .nav-item.me-2 {
                margin-left: 0 !important;
                margin-right: 0 !important;
            }

            .app-topbar .navbar-collapse.show .navbar-nav .badge,
            .app-topbar .navbar-collapse.collapsing .navbar-nav .badge {
                margin-left: 0 !important;
                margin-right: 0 !important;
            }

            /* Mobile: nicer sub-menu card styling */
            .app-topbar .mobile-submenu {
                width: 100%;
                max-width: none;
                padding: .25rem 0;
            }

            .app-topbar .mobile-submenu .nav-link {
                width: 100%;
                border-radius: 10px;
                padding: .55rem .75rem;
            }

            /* Keep the same centering as other links in the expanded burger menu */
            .app-topbar .mobile-submenu .nav-link {
                justify-content: center;
                text-align: center;
            }

            .app-topbar .mobile-submenu .nav-link.active {
                background: rgba(249,115,22,.22);
                border: 1px solid rgba(249,115,22,.42);
            }

            /* Mobile: keep the Oznamy toggle centered like other nav links */
            .app-topbar .mobile-collapse-toggle {
                position: relative;
                width: 100%;
                justify-content: center !important;
                text-align: center;
            }

            .app-topbar .mobile-collapse-toggle .caret {
                position: static;
                transform: none;
                opacity: .9;
                transition: transform .15s ease;
            }

            /* Rotate caret when expanded */
            .app-topbar .mobile-collapse-toggle[aria-expanded="true"] .caret {
                transform: rotate(180deg);
            }

            /* Oznamy (mobile): stack toggle + submenu vertically within the same nav item */
            .app-topbar .nav-item.d-lg-none {
                flex-direction: column;
                align-items: stretch;
            }

            /* Ensure the collapse container takes full width so submenu never flows next to the toggle */
            .app-topbar #announcementsMobile {
                width: 100%;
            }

            /* Make submenu a real vertical block under the Oznamy toggle */
            .app-topbar #announcementsMobile .mobile-submenu {
                display: flex;
                flex-direction: column;
                align-items: center;
                width: 100%;
            }

            .app-topbar #announcementsMobile .mobile-submenu .nav-link {
                width: 100%;
                justify-content: center;
                text-align: center;
            }

            /* Remove inline margin helpers inside the submenu (mt-1 would otherwise shift layout oddly) */
            .app-topbar #announcementsMobile .mobile-submenu .nav-link.mt-1 {
                margin-top: .25rem !important;
            }

            /* When Oznamy is a <button>, reset default button styles so it's centered like links */
            .app-topbar button.mobile-collapse-toggle {
                background: transparent;
                border: 0;
                padding: .5rem 0.5rem;
                color: inherit;
                font: inherit;
                line-height: inherit;
                cursor: pointer;
                appearance: none;
                -webkit-appearance: none;
            }

            .app-topbar button.mobile-collapse-toggle:focus {
                outline: none;
                box-shadow: none;
            }

            /* Make sure the button text is centered (some browsers treat buttons differently) */
            .app-topbar button.mobile-collapse-toggle {
                text-align: center;
            }

            /* Fix: button-based Oznamy toggle was rendering as default (black) in some browsers */
            /* Use concrete fallbacks for navbar colors to avoid unresolved custom-property errors */
            .app-topbar button.mobile-collapse-toggle {
                color: rgba(255,255,255,0.92) !important;
            }

            .app-topbar button.mobile-collapse-toggle:hover,
            .app-topbar button.mobile-collapse-toggle:focus {
                color: rgba(255,255,255,1) !important;
            }

            .app-topbar button.mobile-collapse-toggle.active {
                color: rgba(255,255,255,1) !important;
            }

            /* When the menu is open on mobile turn the collapse into a fixed overlay
               so menu items are centered, scrollable and visually consistent. */
            .app-topbar.menu-open .navbar-collapse {
                position: fixed;
                top: var(--topbar-height);
                left: 0;
                right: 0;
                width: 100%;
                height: calc(100vh - var(--topbar-height));
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
                background: transparent; /* visual backdrop handled by pseudo-element */
                padding: 1rem 1rem 2rem;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: flex-start;
                z-index: 1033; /* above pseudo-element (1030) and interactive content (1031) */
            }

            /* Slight spacing for nav items in overlay mode */
            .app-topbar.menu-open .navbar-collapse .navbar-nav {
                gap: .5rem;
            }

        }

        /* Mobile-only: keep credits badge fixed in the topbar near the burger icon (never inside the collapse layout) */
        @media (max-width: 991.98px) {
            .app-topbar #userCreditsBadgeMobile {
                position: fixed;
                top: calc(var(--topbar-height) / 2 + 20px);
                transform: translateY(-50%);
                right: 76px; /* sits just left of burger icon */
                z-index: 1032;
                pointer-events: none; /* never block tapping the burger icon */
            }
        }

        /* Brand orange text color (sýta oranžová) */
        .text-brand-orange {
            color: var(--brand-orange) !important;
        }

        /* Homepage-only: ensure topbar dropdowns/submenus appear above everything */
        body.homepage .app-topbar .dropdown-menu,
        body.homepage .app-topbar .mobile-submenu,
        body.homepage .dropdown-menu {
            z-index: 99999999 !important;
        }

        /* Workaround helper: disable backdrop-filter briefly to clear rendering artifacts on some mobile browsers */
        /* When JS adds .no-backdrop we only disable the pseudo-element's filter */
        .app-topbar.no-backdrop::before {
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            background: rgba(0,0,0,0.45) !important; /* slightly darker fallback while disabled */
        }

        /* Stronger temporary hide: remove the pseudo-element entirely (used to avoid lingering blur artifacts) */
        .app-topbar.backdrop-disabled::before {
            opacity: 0 !important;
            pointer-events: none !important;
        }
    </style>
</head>
<body class="{{ url()->current() === url('/') ? 'homepage' : '' }} @if(!empty($hideTopbar)) no-topbar @elseif(!empty($overlayTopbar)) overlay-topbar @endif">

@if(empty($hideTopbar))
<nav class="app-topbar navbar navbar-dark navbar-expand-lg">
    <div class="container app-topbar-inner">

         <a class="navbar-brand d-inline-flex align-items-center" href="{{ url('/') }}">
             <img class="app-logo" src="{{ asset('img/logo1.png') }}" alt="Super Gym logo" loading="eager">
         </a>

        {{-- Mobile-only credits badge (positioned next to burger via CSS) --}}
        @auth
            @php
                /** @var \App\Models\User $user */
                $user = auth()->user();
            @endphp
            @if($user && $user->isRegularUser())
                <span class="badge topbar-credits d-lg-none" id="userCreditsBadgeMobile">Kredity: {{ $user->credits ?? 0 }}</span>
            @endif
        @endauth

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
            <span class="navbar-toggler-icon"></span>
        </button>

         <div class="collapse navbar-collapse" id="navbarSupportedContent">
             <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                @auth
                    @php
                        /** @var \App\Models\User $user */
                        $user = auth()->user();
                    @endphp

                    @if($user->isReception())
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

                        @if($user->isRegularUser())
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('my-trainings.index') }}">Moje tréningy</a>
                            </li>
                        @endif

                        @if($user->isAdmin())
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.users.index') }}">Používatelia</a>
                            </li>

                            @php
                                $trainOpen = request()->routeIs('admin.trainings.*');
                            @endphp

                            {{-- Tréningy: desktop dropdown + mobile collapse (no duplicates). --}}
                            <li class="nav-item dropdown d-none d-lg-block">
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

                            <li class="nav-item d-lg-none">
                                <button class="nav-link d-flex align-items-center mobile-collapse-toggle {{ $trainOpen ? 'active' : '' }}"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#trainingsMobile"
                                        aria-expanded="{{ $trainOpen ? 'true' : 'false' }}"
                                        aria-controls="trainingsMobile">
                                    <span>Tréningy</span>
                                    <span class="caret">▾</span>
                                </button>
                                <div class="collapse {{ $trainOpen ? 'show' : '' }}" id="trainingsMobile">
                                    <div class="mobile-submenu">
                                        <a class="nav-link {{ request()->routeIs('admin.trainings.index') ? 'active' : '' }}" href="{{ route('admin.trainings.index') }}">Správa aktuálnych tréningov</a>
                                        <a class="nav-link mt-1 {{ request()->routeIs('admin.trainings.archive') ? 'active' : '' }}" href="{{ route('admin.trainings.archive') }}">Archív tréningov</a>
                                    </div>
                                </div>
                            </li>

                            @php
                                $annOpen = request()->routeIs('admin.announcements.*') || request()->routeIs('announcements.*');
                            @endphp

                            {{-- Desktop/tablet: dropdown. Mobile: inline submenu. --}}
                            <li class="nav-item dropdown d-none d-lg-block">
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
                                    <li><hr class="dropdown-divider"></li>
                                    <!-- removed Admin settings from here - now a top-level nav item (moved below) -->
                                </ul>
                            </li>

                            <li class="nav-item d-lg-none">
                                <button class="nav-link d-flex align-items-center mobile-collapse-toggle {{ $annOpen ? 'active' : '' }}"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#announcementsMobile"
                                        aria-expanded="{{ $annOpen ? 'true' : 'false' }}"
                                        aria-controls="announcementsMobile">
                                    <span>Oznamy</span>
                                    <span class="caret">▾</span>
                                </button>
                                <div class="collapse {{ $annOpen ? 'show' : '' }}" id="announcementsMobile">
                                    <div class="mobile-submenu">
                                        <a class="nav-link {{ request()->routeIs('admin.announcements.index') ? 'active' : '' }}" href="{{ route('admin.announcements.index') }}">Správa oznamov</a>
                                        <a class="nav-link mt-1 {{ request()->routeIs('admin.announcements.archive') ? 'active' : '' }}" href="{{ route('admin.announcements.archive') }}">Archív oznamov</a>
                                        <a class="nav-link mt-1 {{ request()->routeIs('announcements.index') ? 'active' : '' }}" href="{{ route('announcements.index') }}">Oznamy (zobrazenie)</a>
                                        <!-- removed mobile 'Nastavenia' from here - now appears as a separate menu item (moved below) -->
                                    </div>
                                </div>
                            </li>

                            {{-- Move Settings to be last in the admin menu (after Oznamy) --}}
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.edit') }}">Nastavenia</a>
                            </li>

                        @else
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('announcements.index') }}">Oznamy</a>
                            </li>
                        @endif
                    @endif

                    @if($user->isTrainer())
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('trainer.trainings.create') }}">Vytvorenie tréningu</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('trainer.trainings.index') }}">Vytvorené tréningy</a>
                        </li>
                    @endif
                @endauth
            </ul>

            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                @guest
                    <li class="nav-item">
                        <a class="nav-link topbar-link" href="{{ route('login') }}">Prihlásiť sa</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="nav-link topbar-btn-orange" href="{{ route('register') }}">Registrácia</a>
                    </li>
                @else
                    @php
                        /** @var \App\Models\User $user */
                        $user = auth()->user();
                    @endphp
                    <li class="nav-item d-flex align-items-center me-2">
                        @if($user->isRegularUser())
                            {{-- Desktop-only: on mobile we show credits next to the burger icon, so don't duplicate inside the expanded menu. --}}
                            <span class="badge topbar-credits me-2 d-none d-lg-inline-flex" id="userCreditsBadge">Kredity: {{ $user->credits ?? 0 }}</span>
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
</nav>
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
    // Ensure the burger icon always toggles the menu (open/close) reliably.
    (function () {
        const toggler = document.querySelector('.app-topbar .navbar-toggler');
        const collapseEl = document.getElementById('navbarSupportedContent');
        const topbar = document.querySelector('.app-topbar');
        if (!toggler || !collapseEl || typeof bootstrap === 'undefined') return;

        // Add missing ARIA attributes for better Bootstrap/AT interoperability.
        toggler.setAttribute('aria-controls', 'navbarSupportedContent');
        toggler.setAttribute('aria-expanded', 'false');
        toggler.setAttribute('aria-label', 'Toggle navigation');

        // Disable Bootstrap's data-api toggle to avoid double toggles.
        toggler.removeAttribute('data-bs-toggle');
        toggler.removeAttribute('data-bs-target');

        const collapse = bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false });

        // Explicitly toggle on burger click (1st click opens, 2nd click closes).
        toggler.addEventListener('click', function (e) {
            e.preventDefault();
            if (collapseEl.classList.contains('show')) {
                // hide menu and remove the full-viewport blur immediately
                if (topbar) {
                    topbar.classList.remove('menu-open');
                }
                collapse.hide();
            } else {
                // show menu and enable full-viewport blur immediately so they appear together
                if (topbar) {
                    topbar.classList.remove('backdrop-disabled');
                    topbar.classList.add('menu-open');
                }
                collapse.show();
            }
        });

        // Keep ARIA in sync.
        collapseEl.addEventListener('shown.bs.collapse', function () {
            toggler.setAttribute('aria-expanded', 'true');
            // ensure menu-open is present (in case show() was triggered programmatically)
            if (topbar) {
                topbar.classList.remove('backdrop-disabled');
                topbar.classList.add('menu-open');
            }
        });

        collapseEl.addEventListener('hidden.bs.collapse', function () {
            toggler.setAttribute('aria-expanded', 'false');

            // Remove the menu-open class immediately so the blur disappears together with the menu.
            if (topbar) topbar.classList.remove('menu-open');

            // Immediately hide the pseudo-element to avoid lingering blur artifacts on some mobile browsers.
            // We'll restore it shortly after allowing the browser to repaint.
            try {
                const tb = document.querySelector('.app-topbar');
                if (tb) {
                    tb.classList.add('backdrop-disabled');

                    // Also keep the no-backdrop state for a short timeout to allow
                    // some browsers to complete their repaint and clear artifacts.
                    tb.classList.add('no-backdrop');

                    setTimeout(function () {
                        tb.classList.remove('no-backdrop');
                        tb.classList.remove('backdrop-disabled');
                    }, 120);
                }

                // Fallback: apply a trivial transform to body to promote a repaint.
                document.body.style.webkitTransform = 'translateZ(0)';
                document.body.style.transform = 'translateZ(0)';
                requestAnimationFrame(function () {
                    document.body.style.webkitTransform = '';
                    document.body.style.transform = '';
                });
            } catch (err) {
                // ignore
            }
        });

        // Auto-close the menu when clicking a real navigation link inside the expanded collapse (mobile UX).
        // Do NOT close when user clicks collapse toggles like "Oznamy".
        collapseEl.addEventListener('click', function (e) {
            // Consider clicks on anchors and buttons. Mobile submenu toggles are buttons, so
            // ensure we detect them and avoid auto-closing when users toggle submenus.
            const clickedToggle = e.target.closest('[data-bs-toggle="collapse"], .mobile-collapse-toggle');
            const linkEl = e.target.closest('a, button');

            // If click wasn't on a link/button inside the collapse, ignore.
            if (!linkEl) return;

            // If the clicked element (or its ancestor) is a collapse-toggle (mobile submenu), don't auto-close.
            if (clickedToggle) return;

            // If it's a dummy dropdown toggle (href="#"), also don't close.
            if (linkEl.tagName === 'A' && (linkEl.getAttribute('href') || '') === '#') return;

            if (collapseEl.classList.contains('show')) {
                if (topbar) topbar.classList.remove('menu-open');
                collapse.hide();
            }
        });
    })();

    // Fallback: ensure any .dropdown-toggle in the topbar reliably opens its menu (fixes homepage overlay/blocking cases)
    (function () {
        document.addEventListener('click', function (e) {
            const toggle = e.target.closest('.app-topbar .dropdown-toggle');
            if (!toggle) return;

            const menu = document.getElementById(toggle.getAttribute('aria-controls')) || toggle.nextElementSibling;
            if (!menu) return;

            // Let Bootstrap's native handler run first. If it didn't open the menu, open it ourselves.
            // Use a microtask so we don't interfere with event order.
            Promise.resolve().then(function () {
                if (!menu.classList.contains('show')) {
                    const dd = bootstrap.Dropdown.getOrCreateInstance(toggle);
                    dd.toggle();
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

                // React to programmatic updates from other scripts (e.g. training-calendar.js)
                try {
                    window.addEventListener('credits:updated', function (ev) {
                        try {
                            const v = ev?.detail?.credits;
                            if (typeof v !== 'undefined') setBadgeCredits(v);
                        } catch (e) { /* ignore */ }
                    });
                } catch (e) {
                    // ignore
                }

                async function pollMyCredits() {
                    try {
                        const routeUrl = @json(route('me.credits'));
                        if (!routeUrl) return;
                        console.debug('pollMyCredits: fetching', routeUrl);
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

                // Run once immediately so mobile users see their credits without waiting 5s
                pollMyCredits();
                // Poll every 5 seconds
                setInterval(pollMyCredits, 5000);
            })();
        </script>
    @endif
@endauth
</body>
</html>
