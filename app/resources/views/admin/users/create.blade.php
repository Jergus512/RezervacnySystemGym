@extends('layouts.app')

@section('title', 'Nový používateľ')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Vytvoriť nového používateľa</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.users.store') }}" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Meno</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required
                               class="form-control @error('name') is-invalid @enderror">
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required
                               class="form-control @error('email') is-invalid @enderror">
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Heslo</label>
                        <input id="password" type="password" name="password" required
                               class="form-control @error('password') is-invalid @enderror">
                        @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Potvrdenie hesla</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required
                               class="form-control">
                    </div>

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" value="1" id="is_admin" name="is_admin" @checked(old('is_admin'))>
                        <label class="form-check-label" for="is_admin">
                            Admin používateľ
                        </label>
                    </div>

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" value="1" id="is_trainer" name="is_trainer" @checked(old('is_trainer'))>
                        <label class="form-check-label" for="is_trainer">
                            Tréner
                        </label>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" value="1" id="is_reception" name="is_reception" @checked(old('is_reception'))>
                        <label class="form-check-label" for="is_reception">
                            Recepcia
                        </label>
                        <div class="form-text">Admin, tréner a recepcia sa nedajú kombinovať.</div>
                    </div>

                    <div class="mb-3" id="creditsFieldWrapper">
                        <label for="credits" class="form-label">Kredity</label>
                        <input id="credits" type="number" min="0" name="credits" value="{{ old('credits', 0) }}"
                               class="form-control @error('credits') is-invalid @enderror">
                        @error('credits')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Zobrazí sa iba pre bežného používateľa (nie admin/tréner).</div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Späť</a>
                        <button type="submit" class="btn btn-primary">Uložiť</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const adminCb = document.getElementById('is_admin');
        const trainerCb = document.getElementById('is_trainer');
        const receptionCb = document.getElementById('is_reception');
        const creditsWrapper = document.getElementById('creditsFieldWrapper');
        const creditsInput = document.getElementById('credits');

        function syncCreditsVisibility() {
            if (!creditsWrapper) return;

            const isRole = (adminCb?.checked || trainerCb?.checked || receptionCb?.checked);
            creditsWrapper.classList.toggle('d-none', isRole);

            if (isRole && creditsInput) {
                creditsInput.value = 0;
            }
        }

        function enforceExclusive(changed) {
            // allow turning role off; but when turning one ON, turn the other two OFF
            if (!changed?.checked) {
                syncCreditsVisibility();
                return;
            }

            if (changed === adminCb) {
                if (trainerCb) trainerCb.checked = false;
                if (receptionCb) receptionCb.checked = false;
            }

            if (changed === trainerCb) {
                if (adminCb) adminCb.checked = false;
                if (receptionCb) receptionCb.checked = false;
            }

            if (changed === receptionCb) {
                if (adminCb) adminCb.checked = false;
                if (trainerCb) trainerCb.checked = false;
            }

            syncCreditsVisibility();
        }

        if (adminCb) adminCb.addEventListener('change', () => enforceExclusive(adminCb));
        if (trainerCb) trainerCb.addEventListener('change', () => enforceExclusive(trainerCb));
        if (receptionCb) receptionCb.addEventListener('change', () => enforceExclusive(receptionCb));

        // initial state
        syncCreditsVisibility();
    })();
</script>
@endsection
