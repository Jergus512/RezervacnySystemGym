@extends('layouts.app')

@section('title', 'Správa oznamov')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0">Aktuálne oznamy</h1>
            <div class="text-muted small">Zobrazujú sa iba oznamy, ktoré sú aktuálne aktívne k dnešnému dátumu.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.announcements.archive') }}" class="btn btn-outline-secondary">Archív oznamov</a>
            <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary">Vytvoriť oznam</a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form class="row g-2 mb-3" method="GET" action="{{ route('admin.announcements.index') }}">
        <div class="col-md-9">
            <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Hľadať (title/content)">
        </div>
        <div class="col-md-3 d-grid">
            <button type="submit" class="btn btn-outline-secondary">Hľadať</button>
        </div>
    </form>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Názov</th>
                        <th>Aktívny</th>
                        <th>Od</th>
                        <th>Do</th>
                        <th>Autor</th>
                        <th class="text-end">Akcie</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($announcements as $a)
                        <tr>
                            <td>{{ $a->id }}</td>
                            <td>
                                <div class="fw-semibold">{{ $a->title ?: '—' }}</div>
                                <div class="text-muted small">{{ \Illuminate\Support\Str::limit(strip_tags($a->content), 80) }}</div>
                            </td>
                            <td>
                                @if($a->is_active)
                                    <span class="badge bg-success">Áno</span>
                                @else
                                    <span class="badge bg-secondary">Nie</span>
                                @endif
                            </td>
                            <td>{{ optional($a->active_from)->format('d.m.Y H:i') ?: '—' }}</td>
                            <td>{{ optional($a->active_to)->format('d.m.Y H:i') ?: '—' }}</td>
                            <td>{{ $a->creator?->name ?: '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.announcements.edit', $a) }}" class="btn btn-sm btn-outline-primary">Upraviť</a>
                                <form method="POST" action="{{ route('admin.announcements.destroy', $a) }}" class="d-inline"
                                      onsubmit="return confirm('Naozaj zmazať oznam?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Zmazať</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Žiadne oznamy.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $announcements->links() }}
    </div>

    {{-- Upcoming announcements section --}}
    <div class="mt-5 d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="h4 mb-0">Nadchádzajúce oznamy</h2>
            <div class="text-muted small">Zobrazujú sa naplánované oznamy, ktoré majú nastavený začiatok v budúcnosti.</div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Názov</th>
                        <th>Aktívny</th>
                        <th>Od</th>
                        <th>Do</th>
                        <th>Autor</th>
                        <th class="text-end">Akcie</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($upcoming as $a)
                        <tr>
                            <td>{{ $a->id }}</td>
                            <td>
                                <div class="fw-semibold">{{ $a->title ?: '—' }}</div>
                                <div class="text-muted small">{{ \Illuminate\Support\Str::limit(strip_tags($a->content), 80) }}</div>
                            </td>
                            <td>
                                @if($a->is_active)
                                    <span class="badge bg-success">Áno</span>
                                @else
                                    <span class="badge bg-secondary">Nie</span>
                                @endif
                            </td>
                            <td>{{ optional($a->active_from)->format('d.m.Y H:i') ?: '—' }}</td>
                            <td>{{ optional($a->active_to)->format('d.m.Y H:i') ?: '—' }}</td>
                            <td>{{ $a->creator?->name ?: '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.announcements.edit', $a) }}" class="btn btn-sm btn-outline-primary">Upraviť</a>
                                <form method="POST" action="{{ route('admin.announcements.destroy', $a) }}" class="d-inline"
                                      onsubmit="return confirm('Naozaj zmazať oznam?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Zmazať</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Žiadne nadchádzajúce oznamy.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $upcoming->links() }}
    </div>
@endsection
