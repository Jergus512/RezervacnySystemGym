<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>Rezervačný systém 1.0</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CDN -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
        crossorigin="anonymous"
    >
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">Rezervačný systém 1.0</a>
    </div>
</nav>

<main class="py-5">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-12 col-lg-6">
                <h1 class="display-5 fw-bold mb-3">Rezervačný systém 1.0</h1>
                <p class="lead mb-4">
                    Jednoduchý a prehľadný systém na správu rezervácií. Vytváraj, spravuj a kontroluj rezervácie
                    z akéhokoľvek zariadenia.
                </p>

                @guest
                    <div class="d-flex flex-column flex-sm-row gap-2 mb-3">
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg px-4">
                            Prihlásiť sa
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-outline-primary btn-lg px-4">
                            Registrácia
                        </a>
                    </div>
                    <p class="text-muted small mb-0">
                        Po registrácii budeš mať prístup k vlastnému dashboardu, kde neskôr pribudne správa rezervácií,
                        kalendár a štatistiky.
                    </p>
                @else
                    <div class="d-flex flex-column flex-sm-row gap-2 mb-3">
                        <a href="{{ route('dashboard') }}" class="btn btn-success btn-lg px-4">
                            Prejsť na dashboard
                        </a>
                    </div>
                    <p class="text-muted small mb-0">
                        Si prihlásený ako <strong>{{ auth()->user()->name }}</strong>. V dashboarde neskôr pribudne kompletná
                        správa rezervácií.
                    </p>
                @endguest
            </div>

            <div class="col-12 col-lg-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-3">Prehľad rezervácií (ukážka)</h5>
                        <ul class="list-group list-group-flush mb-3">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Rezervácia miestnosti
                                <span class="badge bg-primary rounded-pill">Pripravuje sa</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Konzultácia so zákazníkom
                                <span class="badge bg-secondary rounded-pill">Čoskoro</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Interné stretnutie
                                <span class="badge bg-light text-muted rounded-pill">Ukážka</span>
                            </li>
                        </ul>

                        <button class="btn btn-outline-secondary w-100" disabled>
                            Pridať novú rezerváciu (čoskoro dostupné)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
    crossorigin="anonymous"
></script>
</body>
</html>
