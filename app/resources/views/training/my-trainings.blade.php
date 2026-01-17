@extends('layouts.app')

@section('title', 'Moje tréningy')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Moje tréningy</h1>
    </div>

    <p class="text-muted">Zoznam nadchádzajúcich tréningov, na ktoré si prihlásený (zakúpené).</p>

    @if($trainings->isEmpty())
        <div class="alert alert-info">
            Zatiaľ nemáš žiadne nadchádzajúce zakúpené tréningy.
        </div>
    @else
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Názov</th>
                                <th>Začiatok</th>
                                <th>Koniec</th>
                                <th>Cena</th>
                                <th>Popis</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trainings as $t)
                                <tr>
                                    <td class="fw-semibold">{{ $t->title }}</td>
                                    <td>{{ $t->start_at?->format('d.m.Y H:i') }}</td>
                                    <td>{{ $t->end_at?->format('d.m.Y H:i') }}</td>
                                    <td>{{ (int) ($t->price ?? 0) }} kreditov</td>
                                    <td class="text-muted">{{ $t->description }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection

