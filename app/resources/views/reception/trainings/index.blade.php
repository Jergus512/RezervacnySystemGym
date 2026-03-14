@extends('layouts.app')

@section('title', 'Zrušenie tréningov')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-0">Zrušenie tréningov (Recepcia)</h1>
            <div class="text-muted small">Tu môže recepcia vyhľadať tréningy a zmeniť ich stav na neaktívny (zrušený) alebo znovu aktivovať.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('training-calendar.index') }}" class="btn btn-outline-secondary">Späť na kalendár</a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('reception.trainings.index') }}" class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label for="q" class="form-label mb-0">Vyhľadávanie</label>
                    <input id="q" name="q" type="text" value="{{ $q ?? '' }}" class="form-control" placeholder="Názov alebo popis">
                </div>
                <div class="col-md-4">
                    <label for="show" class="form-label mb-0">Zobraziť</label>
                    <select id="show" name="show" class="form-select">
                        <option value="upcoming" {{ ($show ?? '') === 'upcoming' ? 'selected' : '' }}>Nadchádzajúce</option>
                        <option value="all" {{ ($show ?? '') === 'all' ? 'selected' : '' }}>Všetky</option>
                        <option value="cancelled" {{ ($show ?? '') === 'cancelled' ? 'selected' : '' }}>Zrušené</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Hľadať</button>
                    <a href="{{ route('reception.trainings.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0 align-middle">
                    <thead>
                    <tr>
                        <th>Názov</th>
                        <th>Začiatok</th>
                        <th>Koniec</th>
                        <th>Tréner</th>
                        <th>Kapacita</th>
                        <th>Aktívny</th>
                        <th class="text-end">Akcie</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($trainings as $t)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $t->title }}</div>
                                @if($t->description)
                                    <div class="text-muted small">{{ \Illuminate\Support\Str::limit($t->description, 80) }}</div>
                                @endif
                            </td>
                            <td>{{ $t->start_at?->format('d.m.Y H:i') }}</td>
                            <td>{{ $t->end_at?->format('d.m.Y H:i') }}</td>
                            <td>{{ $t->creator?->name ?? '—' }}</td>
                            <td>{{ $t->capacity }}</td>
                            <td>
                                @if($t->is_active)
                                    <span class="badge text-bg-success">áno</span>
                                @else
                                    <span class="badge text-bg-secondary">nie</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    @if($t->is_active)
                                        <form method="POST" action="{{ route('reception.trainings.toggle', $t) }}" class="m-0"
                                              onsubmit="return confirm('Naozaj chceš zrušiť (deaktivovať) tento tréning?');">
                                            @csrf
                                            <input type="hidden" name="action" value="deactivate">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Zrušiť tréning</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('reception.trainings.toggle', $t) }}" class="m-0">
                                            @csrf
                                            <input type="hidden" name="action" value="activate">
                                            <button type="submit" class="btn btn-sm btn-outline-success">Aktivovať</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Žiadne tréningy.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $trainings->links() }}
    </div>
@endsection

