@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">
                <i class="bi bi-clock-history"></i> História Zmien - {{ $training->title }}
            </h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.trainings.edit', $training) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-pencil"></i> Upraviť tréning
            </a>
            <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Späť
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <small class="text-muted d-block">Názov tréningu</small>
                    <strong class="h5">{{ $training->title }}</strong>
                </div>
                <div class="col-md-6">
                    <small class="text-muted d-block">Začiatok</small>
                    <strong>{{ $training->start_at->format('d.m.Y H:i') }}</strong>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <small class="text-muted d-block">Kapacita</small>
                    <strong>{{ $training->capacity }}</strong>
                </div>
                <div class="col-md-6">
                    <small class="text-muted d-block">Cena (kredity)</small>
                    <strong>{{ $training->price }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Čas</th>
                        <th>Akcia</th>
                        <th>Vykonané</th>
                        <th style="width: 40%;">Zmena</th>
                        <th>Detaily</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($audits as $audit)
                        <tr>
                            <td>
                                <small class="text-muted">
                                    {{ $audit->created_at->format('d.m.Y H:i:s') }}
                                    <br>
                                    <em class="text-secondary">{{ $audit->created_at->diffForHumans() }}</em>
                                </small>
                            </td>
                            <td>
                                <span class="badge {{ $audit->action === 'create' ? 'bg-success' : ($audit->action === 'delete' ? 'bg-danger' : ($audit->action === 'cancel' ? 'bg-warning' : 'bg-info')) }}">
                                    {{ ucfirst($audit->action) }}
                                </span>
                            </td>
                            <td>
                                @if($audit->performer)
                                    <strong>{{ $audit->performer->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $audit->performer->email }}</small>
                                @else
                                    <span class="text-muted">Systém</span>
                                @endif
                            </td>
                            <td>
                                @if($audit->meta)
                                    <small class="text-muted">
                                        @php
                                            $meta = $audit->meta;
                                            $changes = [];

                                            // Parse changes from meta
                                            foreach ($meta as $key => $value) {
                                                if (is_array($value) && isset($value['old']) && isset($value['new'])) {
                                                    $changes[] = "$key: {$value['old']} → {$value['new']}";
                                                }
                                            }
                                        @endphp

                                        @if(count($changes) > 0)
                                            @foreach($changes as $change)
                                                {{ $change }}<br>
                                            @endforeach
                                        @else
                                            {{ array_key_first($meta) ? implode(', ', array_keys($meta)) : '-' }}
                                        @endif
                                    </small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.audit-logs.show', $audit) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Zobraziť
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                Žiadne zmeny nenájdené
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($audits->hasPages())
            <div class="card-footer">
                {{ $audits->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
</div>
@endsection
