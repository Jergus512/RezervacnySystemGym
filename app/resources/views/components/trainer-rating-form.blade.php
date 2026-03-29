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

                    <form method="POST" action="{{ route('trainer-ratings.store', $trainer) }}">
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
                                        <span class="rating-star" data-rating="{{ $i }}" style="color: {{ $existingRating->rating >= $i ? '#ff9800' : '#ddd' }}; transition: all 0.2s;">★</span>
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

                <form method="POST" action="{{ route('trainer-ratings.store', $trainer) }}">
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
                                    <span class="rating-star" data-rating="{{ $i }}" style="color: #ddd; transition: all 0.2s;">★</span>
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
    cursor: pointer;
    font-size: 2rem;
    color: #ddd;
    transition: all 0.2s ease;
}

.rating-star:hover {
    color: #ff9800;
}

input[type="radio"]:checked ~ .rating-star,
input[type="radio"]:checked ~ .rating-star::after {
    color: #ff9800;
}
</style>

<script>
function toggleEditRating(button) {
    const wrapper = button.closest('.trainer-rating-wrapper');
    const editForm = wrapper.querySelector('.edit-rating-form');

    if (editForm.style.display === 'none') {
        editForm.style.display = 'block';
        button.innerHTML = '<i class="bi bi-x-circle"></i> Zatvoriť';
    } else {
        editForm.style.display = 'none';
        button.innerHTML = '<i class="bi bi-pencil"></i> Upraviť';
    }
}

// Inicializácia rating foriem - toto sa spúšťa vždy keď sa DOM zmení
function initializeRatingForms() {
    const wrappers = document.querySelectorAll('.trainer-rating-wrapper');

    wrappers.forEach(wrapper => {
        // Preskočiť ak sú event listenery už inicializované
        if (wrapper.dataset.initialized) {
            return;
        }
        wrapper.dataset.initialized = 'true';

        const form = wrapper.querySelector('form');
        const stars = wrapper.querySelectorAll('.rating-star');
        const inputs = wrapper.querySelectorAll('.rating-input');

        // Klik na hviezdu - nastav rating
        stars.forEach((star) => {
            star.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const ratingValue = parseInt(this.getAttribute('data-rating'));

                // Nastav správny input ako checked
                inputs.forEach((input, inputIndex) => {
                    input.checked = (inputIndex + 1) === ratingValue;
                });

                // Uprav vizuál hviezd
                wrapper.querySelectorAll('.rating-star').forEach((s, i) => {
                    if ((i + 1) <= ratingValue) {
                        s.classList.add('active');
                        s.style.color = '#ff9800';
                    } else {
                        s.classList.remove('active');
                        s.style.color = '#ddd';
                    }
                });
            });

            // Hover efekt
            star.addEventListener('mouseover', function() {
                const ratingValue = parseInt(this.getAttribute('data-rating'));
                wrapper.querySelectorAll('.rating-star').forEach((s, i) => {
                    if ((i + 1) <= ratingValue) {
                        s.style.color = '#ff9800';
                    } else {
                        s.style.color = '#ddd';
                    }
                });
            });
        });

        // Mouse leave - vráť na aktuálne vybrané hodnotenie
        wrapper.addEventListener('mouseleave', function() {
            let checkedIndex = -1;

            inputs.forEach((input, index) => {
                if (input.checked) {
                    checkedIndex = index;
                }
            });

            wrapper.querySelectorAll('.rating-star').forEach((s, i) => {
                if (checkedIndex >= 0 && i <= checkedIndex) {
                    s.style.color = '#ff9800';
                } else {
                    s.style.color = '#ddd';
                }
            });
        });

        // Odoslanie formulára - preventDefault a ajax
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // Skontroluj či je vybrané hodnotenie
                const checkedInput = wrapper.querySelector('.rating-input:checked');
                if (!checkedInput) {
                    alert('Prosím vyber hodnotenie (1-5 hviezd)');
                    return false;
                }

                // Odošli formulár cez AJAX
                const formData = new FormData(form);
                const url = form.getAttribute('action');

                fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    return response.json().then(data => ({
                        status: response.status,
                        ok: response.ok,
                        data: data
                    }));
                })
                .then(result => {
                    console.log('Response:', result);
                    if (!result.ok) {
                        throw new Error(result.data.message || 'Chyba pri ukladaní');
                    }
                    alert('Hodnotenie bolo úspešne uložené! 🎉');
                    // Reload stránky aby sa zobrazilo uložené hodnotenie
                    setTimeout(() => {
                        location.reload();
                    }, 500);
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Chyba pri ukladaní hodnotenia: ' + error.message);
                });
            });
        }
    });
}

// Spusti inicializáciu keď sa stránka načíta
document.addEventListener('DOMContentLoaded', initializeRatingForms);

// Spusti inicializáciu keď sa DOM zmení (filter mesiacov atď)
const observer = new MutationObserver(initializeRatingForms);
observer.observe(document.body, {
    childList: true,
    subtree: true
});
</script>
