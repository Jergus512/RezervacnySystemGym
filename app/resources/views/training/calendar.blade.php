@extends('layouts.app')

@section('title', 'Kalendár tréningov')

@section('content')
    {{-- FullCalendar CSS (lokálne, aby sme neboli závislí od CDN) --}}
    <link rel="stylesheet" href="{{ asset('fullcalendar/fullcalendar.min.css') }}">

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
