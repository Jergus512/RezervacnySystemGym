<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Rezervačný systém 1.0')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
        crossorigin="anonymous"
    >

    <style>
        :root {
            --topbar-height: 64px;
        }

        @media (min-width: 768px) {
            :root {
                --topbar-height: 72px;
            }
        }

        body {
            padding-top: var(--topbar-height);
        }

        body.no-topbar {
            padding-top: 0;
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
            z-index: 1030; /* bootstrap navbar default */
            background: #000;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }

        .app-topbar-inner {
            padding: .2rem 0;
        }

        @media (min-width: 768px) {
            .app-topbar-inner {
                padding: .25rem 0;
            }
        }

        .app-logo {
            height: 44px;
            width: auto;
            max-width: 100%;
            display: block;
        }

        @media (min-width: 768px) {
            .app-logo {
                height: 52px;
            }
        }
    </style>
</head>
<body class="@if(!empty($hideTopbar)) no-topbar @endif">

@if(empty($hideTopbar))
<nav class="navbar navbar-expand-lg navbar-dark app-topbar">
    <div class="container app-topbar-inner">
        <a class="navbar-brand d-inline-flex align-items-center" href="{{ url('/') }}">
            <img class="app-logo" src="{{ asset('img/logo1.png') }}" alt="Super Gym logo" loading="eager">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                @auth
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('training-calendar.index') }}">Kalendár tréningov</a>
                    </li>

                    @if(auth()->user()->isRegularUser())
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('my-trainings.index') }}">Moje tréningy</a>
                        </li>
                    @endif

                    @if(auth()->user()->is_trainer)
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('trainer.trainings.create') }}">Vytvorenie tréningu</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('trainer.trainings.index') }}">Vytvorené tréningy</a>
                        </li>
                    @endif

                    @if(auth()->user()->is_admin)
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.users.index') }}">Používatelia</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.trainings.index') }}">Editácia tréningov</a>
                        </li>
                    @endif
                @endauth
            </ul>

            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Prihlásiť</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">Registrácia</a>
                    </li>
                @else
                    <li class="nav-item d-flex align-items-center me-2">
                        @if(auth()->user()->isRegularUser())
                            <span class="badge text-bg-warning me-2" id="userCreditsBadge">Kredity: {{ auth()->user()->credits ?? 0 }}</span>
                        @endif
                        <span class="navbar-text small text-white-50">{{ auth()->user()->name }}</span>
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

<main class="py-4">
    <div class="container">
        @yield('content')
    </div>
</main>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
    crossorigin="anonymous"
></script>
</body>
</html>
