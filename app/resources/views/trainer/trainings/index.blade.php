@extends('layouts.app')

@section('title', 'Vytvorené tréningy')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h1 class="h3 mb-0">Vytvorené tréningy</h1>
        <a href="{{ route('trainer.trainings.create') }}" class="btn btn-primary">+ Vytvoriť tréning</a>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if($trainings->isEmpty())
        <div class="alert alert-info">Zatiaľ si nevytvoril žiadne tréningy.</div>
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
@endsection

