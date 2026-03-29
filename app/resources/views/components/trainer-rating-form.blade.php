<!-- resources/views/components/trainer-rating-form.blade.php -->
<div class="trainer-rating-wrapper mb-3">
    @php
        // Skontroluj či už existuje hodnotenie pre tohto trénera a tréning
        $existingRating = null;
        if(isset($trainer) && isset($training) && $training) {
            $existingRating = \App\Models\TrainerRating::where('trainer_id', $trainer->id)
                ->where('training_id', $training->id)
                ->where('user_id', auth()->id())
                ->first();
        }
    @endphp

    @if($existingRating)
        <!-- Zmenšená verzia - keď je už ohodnotenie uložené -->
        <div class="card border-0 bg-light">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted d-block mb-1">Tvoje hodnotenie:</small>
                        <div style="font-size: 18px; color: #ff9800;">
                            @for($i = 1; $i <= 5; $i++)
                                {{ $i <= $existingRating->rating ? '★' : '☆' }}
                            @endfor
                            <span style="margin-left: 8px; font-weight: bold;">{{ $existingRating->rating }}/5</span>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-secondary"
                            onclick="toggleEditRating(this)"
                        >
                            <i class="bi bi-pencil"></i> Upraviť
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Skrytý formulár na editáciu -->
        <div class="edit-rating-form" style="display: none; margin-top: 12px;">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title mb-3">⭐ Uprav hodnotenie</h6>

                    <form method="POST" action="{{ route('trainer-ratings.store', $trainer) }}" class="rating-form">
                        @csrf

                        <input type="hidden" name="training_id" value="{{ $training->id }}">

                        <!-- Hviezdičky na kliknutie -->
                        <div class="mb-3">
                            <label class="form-label small">Tvoje hodnotenie:</label>
                            <div class="d-flex gap-2 mb-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <label style="cursor: pointer; font-size: 2rem;">
                                        <input
                                            type="radio"
                                            name="rating"
                                            value="{{ $i }}"
                                            style="display: none;"
                                            class="rating-input"
                                            {{ $existingRating->rating == $i ? 'checked' : '' }}
                                        >
                                        <span class="rating-star" data-rating="{{ $i }}" style="color: {{ $existingRating->rating >= $i ? '#ff9800' : '#ddd' }}; transition: all 0.2s; cursor: pointer;">★</span>
                                    </label>
                                @endfor
                            </div>
                        </div>

                        <!-- Tlačidlá -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-sm" style="background-color: #ff9800; color: white; border: none;">
                                Uložiť zmeny
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleEditRating(this)">
                                Zrušiť
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @else
        <!-- Plný formulár - keď ešte nie je ohodnotenie -->
        <div class="card">
            <div class="card-body">
                <h6 class="card-title mb-3">⭐ Ohodnoť tohto trénera</h6>

                <form method="POST" action="{{ route('trainer-ratings.store', $trainer) }}" class="rating-form">
                    @csrf

                    <!-- Skryté pole pre training_id -->
                    @if(isset($training) && $training)
                        <input type="hidden" name="training_id" value="{{ $training->id }}">
                    @endif

                    <!-- Hviezdičky na kliknutie -->
                    <div class="mb-3">
                        <label class="form-label small">Tvoje hodnotenie:</label>
                        <div class="d-flex gap-2 mb-2">
                            @for($i = 1; $i <= 5; $i++)
                                <label style="cursor: pointer; font-size: 2rem;">
                                    <input
                                        type="radio"
                                        name="rating"
                                        value="{{ $i }}"
                                        style="display: none;"
                                        class="rating-input"
                                    >
                                    <span class="rating-star" data-rating="{{ $i }}" style="color: #ddd; transition: all 0.2s; cursor: pointer;">★</span>
                                </label>
                            @endfor
                        </div>
                    </div>

                    <!-- Tlačidlá -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-sm" style="background-color: #ff9800; color: white; border: none;">
                            Uložiť hodnotenie
                        </button>
                        <button type="reset" class="btn btn-sm btn-outline-secondary">
                            Zrušiť
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

<style>
.rating-star {
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
}

.rating-star:hover {
    color: #ff9800 !important;
}
</style>

<script>
function toggleEditRating(button) {
    const wrapper = button.closest('.trainer-rating-wrapper');
    const editForm = wrapper.querySelector('.edit-rating-form');

    if (editForm) {
        if (editForm.style.display === 'none') {
            editForm.style.display = 'block';
            button.innerHTML = '<i class="bi bi-x-circle"></i> Zatvoriť';
        } else {
            editForm.style.display = 'none';
            button.innerHTML = '<i class="bi bi-pencil"></i> Upraviť';
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Inicializuj všetky rating formy
    initializeAllRatingForms();
});

// Re-inicializuj keď sa DOM zmení
const observer = new MutationObserver(function(mutations) {
    initializeAllRatingForms();
});

observer.observe(document.body, {
    childList: true,
    subtree: true
});

function initializeAllRatingForms() {
    // Najdi všetky rating formy
    document.querySelectorAll('.rating-form').forEach(form => {
        // Ak je už inicializovaná, preskoč
        if (form.dataset.initialized === 'true') {
            return;
        }
        form.dataset.initialized = 'true';

        const wrapper = form.closest('.trainer-rating-wrapper');
        const stars = form.querySelectorAll('.rating-star');
        const inputs = form.querySelectorAll('.rating-input');

        // Pridaj event listenery na hviezdy
        stars.forEach(star => {
            star.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const ratingValue = parseInt(this.getAttribute('data-rating'));

                // Nastav radio input
                inputs.forEach(input => {
                    input.checked = parseInt(input.value) === ratingValue;
                });

                // Update vizuálu
                updateStarsDisplay(wrapper);
            });

            star.addEventListener('mouseover', function() {
                const hoverValue = parseInt(this.getAttribute('data-rating'));
                stars.forEach(s => {
                    const val = parseInt(s.getAttribute('data-rating'));
                    s.style.color = val <= hoverValue ? '#ff9800' : '#ddd';
                });
            });
        });

        wrapper.addEventListener('mouseleave', function() {
            updateStarsDisplay(wrapper);
        });

        // Submit handler
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const checkedInput = form.querySelector('.rating-input:checked');
            if (!checkedInput) {
                alert('Prosím vyber hodnotenie (1-5 hviezd)');
                return false;
            }

            // Vytvor FormData a odošli
            const formData = new FormData(form);
            const action = form.getAttribute('action');
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Ukladám...';

            fetch(action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                alert('Hodnotenie bolo úspešne uložené! 🎉');
                location.reload();
            })
            .catch(error => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                console.error('Error:', error);
                alert('Chyba pri ukladaní: ' + error.message);
            });
        });
    });
}

function updateStarsDisplay(wrapper) {
    const inputs = wrapper.querySelectorAll('.rating-input');
    const stars = wrapper.querySelectorAll('.rating-star');

    let checkedValue = 0;
    inputs.forEach(input => {
        if (input.checked) {
            checkedValue = parseInt(input.value);
        }
    });

    stars.forEach(star => {
        const val = parseInt(star.getAttribute('data-rating'));
        star.style.color = val <= checkedValue ? '#ff9800' : '#ddd';
    });
}
</script>
