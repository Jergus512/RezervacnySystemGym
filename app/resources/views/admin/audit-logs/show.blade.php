@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">
                <i class="bi bi-journal-text"></i> Detaily Audit Logu
            </h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Späť na zoznam
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <span class="badge {{ $audit->action === 'create' ? 'bg-success' : ($audit->action === 'delete' ? 'bg-danger' : ($audit->action === 'cancel' ? 'bg-warning' : 'bg-info')) }}">
                            {{ ucfirst($audit->action) }}
                        </span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Tréning</small>
                            <strong>
                                @if($audit->training)
                                    <a href="{{ route('admin.trainings.edit', $audit->training) }}">
                                        {{ $audit->training->title }}
                                    </a>
                                @else
                                    <span class="text-muted">Zmazaný tréning</span>
                                @endif
                            </strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Čas akcie</small>
                            <strong>{{ $audit->created_at->format('d.m.Y H:i:s') }}</strong>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Vykonané používateľom</small>
                            <strong>
                                @if($audit->performer)
                                    {{ $audit->performer->name }}
                                    <br><small class="text-muted">{{ $audit->performer->email }}</small>
                                @else
                                    <span class="text-muted">Systém</span>
                                @endif
                            </strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Akcia</small>
                            <strong>{{ ucfirst(str_replace('_', ' ', $audit->action)) }}</strong>
                        </div>
                    </div>

                    @if($audit->training)
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted d-block">Začiatok tréningu</small>
                                <strong>{{ $audit->training->start_at?->format('d.m.Y H:i') ?? '-' }}</strong>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Koniec tréningu</small>
                                <strong>{{ $audit->training->end_at?->format('d.m.Y H:i') ?? '-' }}</strong>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <small class="text-muted d-block">Kapacita</small>
                                <strong>{{ $audit->training->capacity }}</strong>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Cena (kredity)</small>
                                <strong>{{ $audit->training->price }}</strong>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if($audit->meta && count($audit->meta) > 0)
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">Detaily zmeny</h5>
                    </div>
                    <div class="card-body">
                        <pre class="bg-light p-3 rounded"><code>{{ json_encode($audit->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle"></i> Informácie
                    </h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-5">ID záznamu:</dt>
                        <dd class="col-sm-7"><code>{{ $audit->id }}</code></dd>

                        <dt class="col-sm-5">ID tréningu:</dt>
                        <dd class="col-sm-7"><code>{{ $audit->training_id }}</code></dd>

                        <dt class="col-sm-5">Vykonané:</dt>
                        <dd class="col-sm-7">
                            @if($audit->performer)
                                <code>{{ $audit->performed_by_user_id }}</code>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </dd>

                        <dt class="col-sm-5">Vytvorené:</dt>
                        <dd class="col-sm-7">
                            <small>{{ $audit->created_at->diffForHumans() }}</small>
                        </dd>
                    </dl>
                </div>
            </div>

            @if($audit->training)
                <div class="card mt-3">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-clock-history"></i> História tréningu
                        </h5>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('admin.audit-logs.training', $audit->training) }}" class="btn btn-sm btn-outline-primary w-100">
                            Zobraziť všetky zmeny
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
