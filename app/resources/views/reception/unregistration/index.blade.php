@extends('layouts.app')

@section('title', 'Odhlásenie používateľa z tréningu')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-10">
        <div class="card shadow-sm">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">Odhlásenie používateľa z tréningu</h5>
            </div>
            <div class="card-body">
                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                <div class="alert alert-success d-none" id="ajaxStatus"></div>
                <div class="alert alert-danger d-none" id="ajaxError"></div>

                <div class="alert alert-info">
                    Začni písať email používateľa, vyber ho zo zoznamu a potom si môžeš vybrať tréningy, z ktorých ho chceš odhlásiť.
                </div>

                <div class="mb-4 position-relative">
                    <label for="userEmailSearch" class="form-label">Používateľ (email)</label>
                    <input type="text" id="userEmailSearch" class="form-control" placeholder="napr. jana@example.com" autocomplete="off">

                    <div class="list-group position-absolute w-100 d-none" id="userSearchResults" style="z-index: 1050;"></div>
                    <div class="form-text" id="selectedUserHint">Nie je vybraný žiadny používateľ.</div>
                </div>

                <div id="trainingsContainer" class="d-none">
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                Prihlásené tréningy:
                                <span id="userNameDisplay"></span>
                                (aktuálne kredity: <span id="userCreditsDisplay">-</span>)
                            </h6>
                        </div>
                        <div class="card-body">
                            <div id="trainingsListContainer"></div>
                            <p id="noTrainingsMsg" class="mb-0 text-muted">Používateľ nie je prihlásený na žiadne tréningy.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    const input = document.getElementById('userEmailSearch');
    const results = document.getElementById('userSearchResults');
    const selectedHint = document.getElementById('selectedUserHint');
    const trainingsContainer = document.getElementById('trainingsContainer');
    const trainingsListContainer = document.getElementById('trainingsListContainer');
    const noTrainingsMsg = document.getElementById('noTrainingsMsg');
    const userNameDisplay = document.getElementById('userNameDisplay');
    const userCreditsDisplay = document.getElementById('userCreditsDisplay');
    const ajaxStatus = document.getElementById('ajaxStatus');
    const ajaxError = document.getElementById('ajaxError');

    let debounceT = null;
    let selectedUser = null;
    let statusHideT = null;

    function hideStatusSoon() {
        if (statusHideT) clearTimeout(statusHideT);
        statusHideT = setTimeout(() => {
            ajaxStatus.classList.add('d-none');
            ajaxStatus.textContent = '';
        }, 5000);
    }

    function showStatus(msg) {
        ajaxError.classList.add('d-none');
        ajaxError.textContent = '';
        ajaxStatus.textContent = msg;
        ajaxStatus.classList.remove('d-none');
        hideStatusSoon();
    }

    function showError(msg) {
        if (statusHideT) clearTimeout(statusHideT);
        ajaxStatus.classList.add('d-none');
        ajaxStatus.textContent = '';
        ajaxError.textContent = msg;
        ajaxError.classList.remove('d-none');
    }

    function clearResults() {
        results.innerHTML = '';
        results.classList.add('d-none');
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
            const creditsLabel = (u.credits === null || typeof u.credits === 'undefined') ? '-' : u.credits;
            a.textContent = `${u.email} • ${u.name} • kredity: ${creditsLabel}`;
            a.addEventListener('click', () => {
                setSelected(u);
                clearResults();
            });
            results.appendChild(a);
        });
        results.classList.remove('d-none');
    }

    async function fetchUsers(q) {
        const url = new URL(@json(route('reception.unregistration.search')), window.location.origin);
        url.searchParams.set('q', q);
        const res = await fetch(url.toString(), {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        });
        if (!res.ok) return [];
        return await res.json();
    }

    async function fetchUserTrainings(userId) {
        const url = new URL(@json(route('reception.unregistration.trainings', ['user' => '__ID__'])).replace('__ID__', userId), window.location.origin);
        const res = await fetch(url.toString(), {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        });
        if (!res.ok) return null;
        return await res.json();
    }

    async function setSelected(user) {
        selectedUser = user || null;
        if (!user) {
            input.value = '';
            selectedHint.textContent = 'Nie je vybraný žiadny používateľ.';
            trainingsContainer.classList.add('d-none');
            return;
        }

        input.value = user.email;
        selectedHint.textContent = `Vybraný: ${user.name} (${user.email})`;
        userNameDisplay.textContent = user.name;
        userCreditsDisplay.textContent = user.credits ?? '-';

        // Fetch trainings for this user
        const data = await fetchUserTrainings(user.id);
        if (!data) {
            showError('Nepodarilo sa načítať tréningy.');
            return;
        }

        // Display trainings
        trainingsListContainer.innerHTML = '';
        const trainings = data.trainings || [];

        if (trainings.length === 0) {
            noTrainingsMsg.classList.remove('d-none');
            trainingsContainer.classList.remove('d-none');
        } else {
            noTrainingsMsg.classList.add('d-none');
            trainings.forEach(t => {
                const trainingDiv = document.createElement('div');
                trainingDiv.className = 'card mb-3 border-warning';
                trainingDiv.innerHTML = `
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="card-title mb-2">${t.title}</h6>
                                <p class="mb-1 text-muted">
                                    <small>
                                        <strong>Začiatok:</strong> ${t.start_at_formatted}<br>
                                        <strong>Cena:</strong> ${t.price} kreditov
                                    </small>
                                </p>
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-danger btn-sm unregister-btn" data-user-id="${data.user.id}" data-training-id="${t.id}">
                                    Odhlásiť
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                trainingsListContainer.appendChild(trainingDiv);
            });

            // Add event listeners to unregister buttons
            document.querySelectorAll('.unregister-btn').forEach(btn => {
                btn.addEventListener('click', unregisterFromTraining);
            });

            trainingsContainer.classList.remove('d-none');
        }
    }

    async function unregisterFromTraining(e) {
        const btn = e.target;
        const userId = btn.getAttribute('data-user-id');
        const trainingId = btn.getAttribute('data-training-id');

        if (!confirm('Naozaj chceš odhlásiť tohto používateľa z tréningu?')) {
            return;
        }

        btn.disabled = true;
        const originalText = btn.textContent;
        btn.textContent = 'Spracovávam...';

        try {
            const res = await fetch(@json(route('reception.unregistration.unregister')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    user_id: userId,
                    training_id: trainingId
                })
            });

            const data = await res.json().catch(() => null);

            if (!res.ok) {
                const msg = data?.message || 'Nepodarilo sa odhlásiť používateľa.';
                showError(msg);
                btn.disabled = false;
                btn.textContent = originalText;
                return;
            }

            showStatus(data?.message || 'Používateľ bol odhlásený z tréningu.');

            // Refresh trainings list
            if (selectedUser) {
                const updatedData = await fetchUserTrainings(selectedUser.id);
                if (updatedData) {
                    selectedUser = updatedData.user;
                    userCreditsDisplay.textContent = updatedData.user.credits ?? '-';

                    trainingsListContainer.innerHTML = '';
                    const trainings = updatedData.trainings || [];

                    if (trainings.length === 0) {
                        noTrainingsMsg.classList.remove('d-none');
                    } else {
                        noTrainingsMsg.classList.add('d-none');
                        trainings.forEach(t => {
                            const trainingDiv = document.createElement('div');
                            trainingDiv.className = 'card mb-3 border-warning';
                            trainingDiv.innerHTML = `
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h6 class="card-title mb-2">${t.title}</h6>
                                            <p class="mb-1 text-muted">
                                                <small>
                                                    <strong>Začiatok:</strong> ${t.start_at_formatted}<br>
                                                    <strong>Cena:</strong> ${t.price} kreditov
                                                </small>
                                            </p>
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-danger btn-sm unregister-btn" data-user-id="${updatedData.user.id}" data-training-id="${t.id}">
                                                Odhlásiť
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            `;
                            trainingsListContainer.appendChild(trainingDiv);
                        });

                        document.querySelectorAll('.unregister-btn').forEach(btn => {
                            btn.addEventListener('click', unregisterFromTraining);
                        });
                    }
                }
            }
        } catch (err) {
            showError('Nepodarilo sa odhlásiť používateľa: ' + (err?.message || err));
            btn.disabled = false;
            btn.textContent = originalText;
        }
    }

    function onInput() {
        const q = input.value.trim();
        if (selectedUser && selectedUser.email !== q) {
            setSelected(null);
        }
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

    input.addEventListener('input', onInput);
})();
</script>
@endsection
