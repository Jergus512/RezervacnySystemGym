@extends('layouts.app')

@section('title', 'Vytvorené tréningy')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h1 class="h3 mb-0">Vytvorené tréningy</h1>
        <a href="{{ route('trainer.trainings.create') }}" class="btn btn-primary">+ Vytvoriť tréning</a>
    </div>

    {{-- Search & filter form --}}
    <form method="GET" action="{{ route('trainer.trainings.index') }}" class="card card-body mb-3">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-5">
                <label for="search" class="form-label">Vyhľadávanie podľa názvu</label>
                <input type="text" id="search" name="search" class="form-control"
                       placeholder="Zadaj názov tréningu…" value="{{ $search ?? '' }}">
            </div>
            <div class="col-12 col-md-5">
                <label class="form-label d-block">Stav tréningu</label>
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="active" id="activeAll" value="all"
                           {{ ($showActive ?? '1') === 'all' ? 'checked' : '' }}>
                    <label class="btn btn-outline-secondary" for="activeAll">Všetky</label>

                    <input type="radio" class="btn-check" name="active" id="activeYes" value="1"
                           {{ ($showActive ?? '1') === '1' ? 'checked' : '' }}>
                    <label class="btn btn-outline-success" for="activeYes">Aktívne</label>

                    <input type="radio" class="btn-check" name="active" id="activeNo" value="0"
                           {{ ($showActive ?? '1') === '0' ? 'checked' : '' }}>
                    <label class="btn btn-outline-secondary" for="activeNo">Neaktívne</label>
                </div>
            </div>
            <div class="col-12 col-md-2">
                <button type="submit" class="btn btn-primary w-100">Hľadať</button>
            </div>
        </div>
    </form>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if($trainings->isEmpty())
        <div class="alert alert-info">Žiadne tréningy nezodpovedajú filtru.</div>
    @else
        <div class="card">
            <div class="table-responsive">
                <table class="table table-striped mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Názov</th>
                            <th>Začiatok</th>
                            <th>Koniec</th>
                            <th>Aktívny</th>
                            <th class="text-end">Akcie</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trainings as $t)
                            <tr>
                                <td class="fw-semibold">{{ $t->title }}</td>
                                <td>{{ $t->start_at?->format('d.m.Y H:i') }}</td>
                                <td>{{ $t->end_at?->format('d.m.Y H:i') }}</td>
                                <td>
                                    @if($t->is_active)
                                        <span class="badge bg-success">Áno</span>
                                    @else
                                        <span class="badge bg-secondary">Nie</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
                                        <a href="{{ route('trainer.trainings.edit', $t) }}" class="btn btn-sm btn-outline-primary">Upraviť</a>
                                        <form method="POST" action="{{ route('trainer.trainings.destroy', $t) }}" class="m-0"
                                              onsubmit="return confirm('Naozaj chceš odstrániť tento tréning?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Odstrániť</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $trainings->links() }}
        </div>
    @endif

    <div id="trainings-container">
        @foreach ($trainings as $training)
            {{-- Render training details here --}}
            <div class="training-item">
                <h5>{{ $training->title }}</h5>
                <p>{{ $training->description }}</p>
                <p><strong>Začiatok:</strong> {{ $training->start_at }}</p>
                <p><strong>Koniec:</strong> {{ $training->end_at }}</p>
            </div>
        @endforeach
    </div>

    <script>
        let page = 1;
        const container = document.getElementById('trainings-container');

        window.addEventListener('scroll', () => {
            if (window.innerHeight + window.scrollY >= document.body.offsetHeight) {
                page++;
                fetch(`/trainer/trainings?page=${page}`)
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
@endsection

