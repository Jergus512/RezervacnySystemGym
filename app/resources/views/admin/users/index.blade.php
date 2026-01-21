@extends('layouts.app')

@section('title', 'Správa používateľov')

@section('content')
<div class="pt-3 d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
    <h4 class="mb-0">Používatelia</h4>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm ms-sm-2">+ Nový používateľ</a>
</div>

@if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

<form method="GET" action="{{ route('admin.users.index') }}" class="mb-3" autocomplete="off">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-lg-6 position-relative">
            <label for="users-search" class="form-label mb-1">Vyhľadávanie (meno / email)</label>
            <input
                id="users-search"
                name="q"
                type="text"
                class="form-control"
                value="{{ $q ?? '' }}"
                placeholder="Začni písať…"
                aria-autocomplete="list"
                aria-controls="users-autocomplete"
            />

            <div id="users-autocomplete" class="list-group position-absolute w-100 shadow-sm" style="top: 100%; z-index: 1050; display: none;"></div>
        </div>

        <div class="col-12 col-lg-4">
            <label for="users-role" class="form-label mb-1">Filter podľa typu</label>
            <select id="users-role" name="role" class="form-select">
                <option value="all" @selected(($role ?? 'all') === 'all')>Všetci</option>
                <option value="regular" @selected(($role ?? 'all') === 'regular')>Bežní používatelia</option>
                <option value="trainer" @selected(($role ?? 'all') === 'trainer')>Tréneri</option>
                <option value="reception" @selected(($role ?? 'all') === 'reception')>Recepcia</option>
                <option value="admin" @selected(($role ?? 'all') === 'admin')>Admini</option>
            </select>
        </div>

        <div class="col-12 col-lg-2 d-flex gap-2">
            <button type="submit" class="btn btn-outline-primary w-100">Filtrovať</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
        </div>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead>
        <tr>
            <th>ID</th>
            <th>Meno</th>
            <th>Email</th>
            <th>Typ</th>
            <th class="text-end">Akcie</th>
        </tr>
        </thead>
        <tbody>
        @forelse($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    @if($user->is_admin)
                        <span class="badge bg-success">Admin</span>
                    @elseif($user->is_trainer)
                        <span class="badge bg-info text-dark">Tréner</span>
                    @elseif($user->is_reception)
                        <span class="badge bg-warning text-dark">Recepcia</span>
                    @else
                        <span class="badge bg-secondary">Bežný</span>
                    @endif
                </td>
                <td class="text-end">
                    <div class="d-flex flex-column flex-sm-row gap-2 align-items-stretch justify-content-end">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary w-100 w-sm-auto">Upraviť</a>

                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="m-0 w-100 w-sm-auto"
                              onsubmit="return confirm('Naozaj chceš zmazať tohto používateľa?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger w-100 w-sm-auto">Zmazať</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center">Žiadni používatelia.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3">
    {{ $users->links('partials.pagination-no-arrows') }}
</div>

<script>
(function () {
    const input = document.getElementById('users-search');
    const roleSelect = document.getElementById('users-role');
    const box = document.getElementById('users-autocomplete');

    if (!input || !roleSelect || !box) return;

    let abortController = null;
    let lastQuery = '';

    function hideBox() {
        box.style.display = 'none';
        box.innerHTML = '';
    }

    function showBox() {
        box.style.display = 'block';
    }

    function escapeHtml(str) {
        return String(str)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    async function fetchSuggestions(q) {
        if (abortController) abortController.abort();
        abortController = new AbortController();

        const url = new URL('{{ route('admin.users.autocomplete') }}', window.location.origin);
        url.searchParams.set('q', q);
        url.searchParams.set('role', roleSelect.value || 'all');

        const res = await fetch(url.toString(), {
            headers: { 'Accept': 'application/json' },
            signal: abortController.signal,
        });

        if (!res.ok) return [];
        return await res.json();
    }

    function render(items) {
        box.innerHTML = '';

        if (!items || items.length === 0) {
            hideBox();
            return;
        }

        items.forEach((u) => {
            const a = document.createElement('a');
            a.href = '{{ url('/admin/users') }}/' + encodeURIComponent(u.id) + '/edit';
            a.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
            a.innerHTML =
                '<div>' +
                    '<div class="fw-semibold">' + escapeHtml(u.name) + '</div>' +
                    '<div class="small text-muted">' + escapeHtml(u.email) + '</div>' +
                '</div>' +
                '<span class="badge bg-light text-dark border">' + escapeHtml(u.type) + '</span>';
            box.appendChild(a);
        });

        showBox();
    }

    let timer = null;

    input.addEventListener('input', function () {
        const q = input.value.trim();

        if (q.length < 2) {
            hideBox();
            return;
        }

        if (q === lastQuery) return;
        lastQuery = q;

        clearTimeout(timer);
        timer = setTimeout(async () => {
            try {
                const items = await fetchSuggestions(q);
                render(items);
            } catch (e) {
                // AbortError is fine (typing fast)
            }
        }, 200);
    });

    roleSelect.addEventListener('change', function () {
        // refresh suggestions for the current query (if any)
        const q = input.value.trim();
        if (q.length < 2) return;
        lastQuery = '';
        input.dispatchEvent(new Event('input'));
    });

    document.addEventListener('click', function (e) {
        if (!box.contains(e.target) && e.target !== input) {
            hideBox();
        }
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            hideBox();
        }
    });
})();
</script>
@endsection
