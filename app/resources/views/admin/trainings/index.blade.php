@extends('layouts.app')

@section('title', 'Správa aktuálnych tréningov')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-0">Správa aktuálnych tréningov</h1>
            <div class="text-muted small">Zobrazujú sa iba tréningy, ktoré ešte nezačali (podľa aktuálneho dátumu a času).</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.trainings.archive') }}" class="btn btn-outline-secondary">Archív tréningov</a>
            <a href="{{ route('training-calendar.index') }}" class="btn btn-outline-secondary">Späť na kalendár</a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.trainings.index') }}" class="row g-2 align-items-end">
                <div class="col-md-8">
                    <label for="q" class="form-label mb-0">Vyhľadávanie</label>
                    <input id="q" name="q" type="text" value="{{ $q ?? '' }}" class="form-control" placeholder="Názov alebo popis">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Hľadať</button>
                    <a href="{{ route('admin.trainings.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <div id="trainings-container">
                    @foreach ($trainings as $t)
                        <div class="training-row">
                            <div class="fw-semibold">{{ $t->title }}</div>
                            <div>{{ $t->start_at?->format('d.m.Y H:i') }}</div>
                            <!-- Other training details here -->
                        </div>
                    @endforeach
                </div>

                <script>
                    let page = 1;
                    const container = document.getElementById('trainings-container');

                    window.addEventListener('scroll', () => {
                        if (window.innerHeight + window.scrollY >= document.body.offsetHeight) {
                            page++;
                            fetch(`/admin/trainings?page=${page}`)
                                .then(response => response.text())
                                .then(html => {
                                    const tempDiv = document.createElement('div');
                                    tempDiv.innerHTML = html;
                                    const newTrainings = tempDiv.querySelector('#trainings-container').innerHTML;
                                    container.innerHTML += newTrainings;
                                });
                        }
                    });
                </script>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $trainings->links() }}
    </div>
@endsection
