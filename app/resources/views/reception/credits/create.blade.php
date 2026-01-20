@extends('layouts.app')

@section('title', 'Pridanie kreditov')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Pridanie kreditov</h5>
            </div>
            <div class="card-body">
                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                <div class="alert alert-info">
                    Začni písať email, vyber používateľa zo zoznamu a zadaj počet kreditov, ktoré sa pripočítajú.
                </div>

                <form method="POST" action="{{ route('reception.credits.store') }}" novalidate>
                    @csrf

                    <input type="hidden" name="user_id" id="selectedUserId" value="{{ old('user_id') }}">

                    <div class="mb-3 position-relative">
                        <label for="userEmailSearch" class="form-label">Používateľ (email)</label>
                        <input type="text" id="userEmailSearch" class="form-control @error('user_id') is-invalid @enderror" placeholder="napr. jana@example.com" autocomplete="off">
                        @error('user_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <div class="list-group position-absolute w-100 d-none" id="userSearchResults" style="z-index: 1050;"></div>
                        <div class="form-text" id="selectedUserHint">Nie je vybraný žiadny používateľ.</div>
                    </div>

                    <div class="mb-3">
                        <label for="creditsToAdd" class="form-label">Koľko kreditov pridať</label>
                        <input type="number" min="1" id="creditsToAdd" name="credits_to_add" value="{{ old('credits_to_add') }}"
                               class="form-control @error('credits_to_add') is-invalid @enderror">
                        @error('credits_to_add')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('reception.calendar') }}" class="btn btn-outline-secondary">Späť</a>
                        <button type="submit" class="btn btn-success" id="submitCreditsBtn" disabled>Pripísať kredity</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    const input = document.getElementById('userEmailSearch');
    const results = document.getElementById('userSearchResults');
    const selectedIdInput = document.getElementById('selectedUserId');
    const selectedHint = document.getElementById('selectedUserHint');
    const submitBtn = document.getElementById('submitCreditsBtn');

    let debounceT = null;

    function setSelected(user) {
        if (!user) {
            selectedIdInput.value = '';
            selectedHint.textContent = 'Nie je vybraný žiadny používateľ.';
            submitBtn.disabled = true;
            return;
        }
        selectedIdInput.value = user.id;
        input.value = user.email;
        selectedHint.textContent = `Vybraný: ${user.name} (${user.email}) | aktuálne kredity: ${user.credits ?? 0}`;
        submitBtn.disabled = false;
    }

    function clearResults() {
        results.innerHTML = '';
        results.classList.add('d-none');
    }

    function roleLabel(u) {
        if (u.is_admin) return 'admin';
        if (u.is_trainer) return 'tréner';
        if (u.is_reception) return 'recepcia';
        return 'používateľ';
    }

    function showResults(items) {
        results.innerHTML = '';
        if (!items.length) {
            clearResults();
            return;
        }
        items.forEach(u => {
            const a = document.createElement('button');
            a.type = 'button';
            a.className = 'list-group-item list-group-item-action';
            a.textContent = `${u.email} • ${u.name} • kredity: ${u.credits ?? 0} • ${roleLabel(u)}`;
            a.addEventListener('click', () => {
                setSelected(u);
                clearResults();
            });
            results.appendChild(a);
        });
        results.classList.remove('d-none');
    }

    async function fetchUsers(q) {
        const url = new URL(@json(route('reception.credits.search')), window.location.origin);
        url.searchParams.set('q', q);
        const res = await fetch(url.toString(), {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        });
        if (!res.ok) return [];
        return await res.json();
    }

    function onInput() {
        const q = input.value.trim();
        setSelected(null);
        if (q.length < 2) {
            clearResults();
            return;
        }
        clearTimeout(debounceT);
        debounceT = setTimeout(async () => {
            const items = await fetchUsers(q);
            showResults(items);
        }, 180);
    }

    document.addEventListener('click', (e) => {
        if (!results.contains(e.target) && e.target !== input) {
            clearResults();
        }
    });

    input.addEventListener('input', onInput);
})();
</script>
@endsection
