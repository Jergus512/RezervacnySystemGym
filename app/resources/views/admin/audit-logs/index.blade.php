@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">
                <i class="bi bi-journal-text"></i> Audit Log - Zmeny Tréningu
            </h1>
        </div>
    </div>

    <!-- Filtry -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Akcia</label>
                    <select name="action" class="form-select form-select-sm">
                        <option value="">-- Všetky --</option>
                        @foreach($actions as $act)
                            <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>
                                {{ ucfirst($act) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Od dátumu</label>
                    <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Do dátumu</label>
                    <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class="bi bi-search"></i> Filtrovať
                    </button>
                    <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-sm btn-outline-secondary w-100 ms-2">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabuľka audit logov -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Čas</th>
                        <th>Akcia</th>
                        <th>Tréning</th>
                        <th>Vykonané</th>
                        <th>Detaily</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($audits as $audit)
                        <tr>
                            <td>
                                <small class="text-muted">
                                    {{ $audit->created_at->format('d.m.Y H:i:s') }}
                                </small>
                            </td>
                            <td>
                                <span class="badge {{ $audit->action === 'create' ? 'bg-success' : ($audit->action === 'delete' ? 'bg-danger' : ($audit->action === 'cancel' ? 'bg-warning' : 'bg-info')) }}">
                                    {{ ucfirst($audit->action) }}
                                </span>
                            </td>
                            <td>
                                @if($audit->training)
                                    <a href="{{ route('admin.trainings.edit', $audit->training) }}">
                                        {{ $audit->training->title }}
                                    </a>
                                    <br>
                                    <small class="text-muted">
                                        {{ $audit->training->start_at?->format('d.m.Y H:i') }}
                                    </small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($audit->performer)
                                    <strong>{{ $audit->performer->name }}</strong>
                                @else
                                    <span class="text-muted">Systém</span>
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
                                Žiadne záznamy nenájdené
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
