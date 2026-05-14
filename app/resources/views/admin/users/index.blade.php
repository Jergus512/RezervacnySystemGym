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
    <table class="table table-striped" id="users-table">
        <thead>
            <tr>
                <th>Meno</th>
                <th>Email</th>
                <th>Typ</th>
                <th>Akcie</th>
            </tr>
        </thead>
        <tbody id="users-tbody">
            @foreach ($users as $user)
                <tr>
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
                    <td>
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-primary">Upraviť</a>
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Naozaj chcete odstrániť tohto používateľa?')">Odstrániť</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Indikátor načítavania -->
<div id="loading-indicator" style="display: none; text-align: center; padding: 20px;">
    <div class="spinner-border spinner-border-sm" role="status">
        <span class="visually-hidden">Načítavanie...</span>
    </div>
</div>

<!-- Tlačidlo na načítanie ďalších -->
<div id="load-more-btn-container" style="text-align: center; padding: 20px;">
    <button id="load-more-btn" class="btn btn-outline-secondary" style="display: none;">Načítať ďalších</button>
</div>

<!-- Správa ak nema viac položiek -->
<div id="no-more-items" style="display: none; text-align: center; padding: 20px; color: #6c757d;">
    Všetci používatelia sú už načítaní
</div>

<script>
(function () {
    let currentPage = {{ $users->currentPage() }};
    const lastPage = {{ $users->lastPage() }};
    const searchQuery = '{{ $q ?? '' }}';
    const roleFilter = '{{ $role ?? 'all' }}';

    let isLoading = false;
    let hasMorePages = currentPage < lastPage;

    const tbody = document.getElementById('users-tbody');
    const loadingIndicator = document.getElementById('loading-indicator');
    const noMoreItems = document.getElementById('no-more-items');
    const loadMoreBtn = document.getElementById('load-more-btn');
    const searchInput = document.getElementById('users-search');
    const roleSelect = document.getElementById('users-role');

    console.log('Initial - Page:', currentPage, 'LastPage:', lastPage, 'HasMore:', hasMorePages, 'Rows:', tbody.children.length);

    // Ak sa zmení vyhľadávanie alebo filter
    searchInput.addEventListener('input', function() {
        searchInput.form.submit();
    });

    roleSelect.addEventListener('change', function() {
        roleSelect.form.submit();
    });

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function createUserRow(user) {
        let typeHtml = '';
        if (user.is_admin) {
            typeHtml = '<span class="badge bg-success">Admin</span>';
        } else if (user.is_trainer) {
            typeHtml = '<span class="badge bg-info text-dark">Tréner</span>';
        } else if (user.is_reception) {
            typeHtml = '<span class="badge bg-warning text-dark">Recepcia</span>';
        } else {
            typeHtml = '<span class="badge bg-secondary">Bežný</span>';
        }

        const editUrl = '/admin/users/' + user.id + '/edit';
        const deleteUrl = '/admin/users/' + user.id;

        return `<tr>
            <td>${escapeHtml(user.name)}</td>
            <td>${escapeHtml(user.email)}</td>
            <td>${typeHtml}</td>
            <td>
                <a href="${editUrl}" class="btn btn-sm btn-primary">Upraviť</a>
                <form method="POST" action="${deleteUrl}" class="d-inline" onsubmit="return confirm('Naozaj chcete odstrániť tohto používateľa?')">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-sm btn-danger">Odstrániť</button>
                </form>
            </td>
        </tr>`;
    }

    function updateLoadMoreButton() {
        if (hasMorePages) {
            loadMoreBtn.style.display = 'inline-block';
            noMoreItems.style.display = 'none';
        } else {
            loadMoreBtn.style.display = 'none';
            noMoreItems.style.display = 'block';
        }
    }

    async function loadMoreUsers() {
        if (isLoading || !hasMorePages) {
            console.log('Skip - isLoading:', isLoading, 'hasMore:', hasMorePages);
            return;
        }

        isLoading = true;
        loadingIndicator.style.display = 'block';
        loadMoreBtn.style.display = 'none';

        try {
            const nextPage = currentPage + 1;
            const url = new URL(window.location.href);
            url.searchParams.set('page', nextPage);
            url.searchParams.set('q', searchQuery);
            url.searchParams.set('role', roleFilter);

            console.log('Loading page', nextPage);

            const response = await fetch(url.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }

            const data = await response.json();
            console.log('Got data:', data);

            if (!data.data || data.data.length === 0) {
                hasMorePages = false;
                console.log('No more data');
            } else {
                // Pridaj riadky
                data.data.forEach(user => {
                    const row = document.createElement('tr');
                    row.innerHTML = createUserRow(user).replace('<tr>', '').replace('</tr>', '');
                    tbody.appendChild(row);
                });

                currentPage = data.current_page;
                console.log('Added rows, now at page', currentPage, 'of', data.last_page);

                if (currentPage >= data.last_page) {
                    hasMorePages = false;
                }
            }

        } catch (error) {
            console.error('Error loading:', error);
        } finally {
            isLoading = false;
            loadingIndicator.style.display = 'none';
            updateLoadMoreButton();
        }
    }

    loadMoreBtn.addEventListener('click', function() {
        loadMoreUsers();
    });

    // Infinite scroll
    let scrollTimeout;
    window.addEventListener('scroll', function() {
        if (!hasMorePages || isLoading) return;

        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(function() {
            if ((window.innerHeight + window.scrollY) >= (document.body.offsetHeight - 300)) {
                console.log('Scroll trigger');
                loadMoreUsers();
            }
        }, 200);
    });

    updateLoadMoreButton();
})();
</script>

<!-- Autocomplete script -->
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
                // AbortError je OK (rýchle písanie)
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
