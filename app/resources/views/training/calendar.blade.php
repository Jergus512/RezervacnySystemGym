@extends('layouts.app')

@section('title', 'Kalendár tréningov')

@section('content')
    {{-- FullCalendar CSS (lokálne, aby sme neboli závislí od CDN) --}}
    <link rel="stylesheet" href="{{ asset('fullcalendar/fullcalendar.min.css') }}">

    <style>
        /* FullCalendar: current time indicator ("now" line) in brand orange */
        .fc {
            --fc-now-indicator-color: #f97316;
            --fc-today-bg-color: rgba(249,115,22,.12);
        }

        .fc .fc-timegrid-now-indicator-line {
            border-top-color: #f97316 !important;
            border-color: #f97316 !important;
        }

        .fc .fc-timegrid-now-indicator-arrow {
            border-color: #f97316 !important;
        }

        /* Month view: always show events as full bars (not dot/list-item)
           FullCalendar uses `eventDisplay: 'auto'` by default and may render list-items with a dot.
           These overrides make the bar visible and let per-event backgroundColor fill the block. */
        .fc .fc-daygrid-event {
            display: block;
            border-radius: 4px;
        }

        .fc .fc-daygrid-event-dot {
            display: none !important;
        }

        .fc .fc-daygrid-event .fc-event-time,
        .fc .fc-daygrid-event .fc-event-title {
            padding-left: 0.25rem;
            padding-right: 0.25rem;
        }

        /* Ensure inner container doesn't stay transparent */
        .fc .fc-daygrid-event .fc-event-main {
            background: transparent;
        }

        /* Our semantic coloring. Needs high specificity to override FullCalendar defaults in dayGridMonth. */
        .fc .gym-event-not-current,
        .fc .gym-event-not-current .fc-event-main,
        .fc .gym-event-not-current .fc-event-main-frame {
            background-color: #e9ecef !important;
            border-color: #ced4da !important;
            color: #6c757d !important;
        }

        .fc .gym-event-not-current .fc-daygrid-event-dot {
            border-color: #ced4da !important;
            background-color: #ced4da !important;
        }

        .fc .gym-event-registered,
        .fc .gym-event-registered .fc-event-main,
        .fc .gym-event-registered .fc-event-main-frame {
            background-color: #198754 !important;
            border-color: #198754 !important;
            color: #ffffff !important;
        }

        .fc .gym-event-registered .fc-daygrid-event-dot {
            border-color: #198754 !important;
            background-color: #198754 !important;
        }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Kalendár tréningov</h1>
    </div>

    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    <div id="trainingCalendarLoadError" class="alert alert-danger d-none"></div>

    <div class="card mb-3">
        <div class="card-body">
            <div id="training-calendar"></div>
        </div>
    </div>

    <!-- Modal pre detail tréningu -->
    <div class="modal fade" id="trainingEventModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="trainingEventTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="trainingEventDescription" class="mb-2"></p>
                    <p class="mb-1"><strong>Tréner:</strong> <span id="trainingEventCreator"></span></p>
                    <p class="mb-1"><strong>Začiatok:</strong> <span id="trainingEventStart"></span></p>
                    <p class="mb-1"><strong>Koniec:</strong> <span id="trainingEventEnd"></span></p>
                    <p class="mb-1"><strong>Kapacita:</strong> <span id="trainingEventCapacity"></span></p>
                    <p class="mb-1"><strong>Cena:</strong> <span id="trainingEventPrice"></span> kreditov</p>
                    <p class="mb-1"><strong>Prihlásených:</strong> <span id="trainingEventRegistered"></span></p>

                    <hr>
                    <p class="mb-1"><strong>Prihlásení:</strong></p>
                    <ul class="mb-0" id="trainingEventAttendees"></ul>

                    <div class="alert alert-danger d-none mt-3" id="trainingEventError"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zavrieť</button>

                    <a href="#" class="btn btn-outline-primary d-none" id="trainingEventAdminEditLink">Upraviť (admin)</a>

                    <form method="POST" action="#" id="trainingUnregisterForm" class="m-0 d-none">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger" id="trainingEventUnregisterButton" disabled>
                            Odhlásiť sa
                        </button>
                    </form>

                    <form method="POST" action="#" id="trainingRegisterForm" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-primary" id="trainingEventRegisterButton" disabled>
                            Prihlásiť sa
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- FullCalendar JS (CDN; lokálne súbory v public/fullcalendar sú placeholdery) --}}
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales-all.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/interaction@6.1.15/index.global.min.js"></script>

    <script>
        window.trainingRegisterRouteTemplate = @json(route('trainings.register', ['training' => '__ID__']));
        window.trainingUnregisterRouteTemplate = @json(route('trainings.unregister', ['training' => '__ID__']));
        window.trainingEventsUrl = @json(route('training-calendar.events'));
        window.canRegisterForTrainings = @json(auth()->check() && auth()->user()->isRegularUser());
        window.isAdminUser = @json(auth()->check() && auth()->user()->isAdmin());
        window.adminTrainingEditRouteTemplate = @json(route('admin.trainings.edit', ['training' => '__ID__']));
    </script>

    <script src="{{ asset('js/training-calendar.js') }}"></script>
@endsection
