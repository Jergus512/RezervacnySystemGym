@extends('layouts.app')

@section('title', 'Editácia tréningov')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h1 class="h3 mb-0">Editácia tréningov</h1>
        <a href="{{ route('training-calendar.index') }}" class="btn btn-outline-secondary">Späť na kalendár</a>
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
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.trainings.edit', $t) }}">Upraviť</a>

                                    <form method="POST" action="{{ route('admin.trainings.destroy', $t) }}" class="m-0"
                                          onsubmit="return confirm('Naozaj chceš odstrániť tento tréning?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Zmazať</button>
                                    </form>
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

