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

.rating-star:hover,
.rating-star.active {
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

document.addEventListener('DOMContentLoaded', function() {
    // Nájdi všetky rating wrappery
    const wrappers = document.querySelectorAll('.trainer-rating-wrapper');

    wrappers.forEach(wrapper => {
        const stars = wrapper.querySelectorAll('.rating-star');
        const inputs = wrapper.querySelectorAll('.rating-input');

        stars.forEach(star => {
            star.addEventListener('click', function() {
                const rating = this.getAttribute('data-rating');

                // Nájdi zodpovedajúci input
                inputs.forEach((input, index) => {
                    if (index + 1 <= rating) {
                        input.checked = true;
                    }
                });

                // Uprav farby hviezdičiek
                wrapper.querySelectorAll('.rating-star').forEach((s, i) => {
                    if (i < rating) {
                        s.classList.add('active');
                        s.style.color = '#ff9800';
                    } else {
                        s.classList.remove('active');
                        s.style.color = '#ddd';
                    }
                });
            });

            star.addEventListener('mouseover', function() {
                const rating = this.getAttribute('data-rating');
                wrapper.querySelectorAll('.rating-star').forEach((s, i) => {
                    if (i < rating) {
                        s.style.color = '#ff9800';
                    } else {
                        s.style.color = '#ddd';
                    }
                });
            });
        });

        wrapper.addEventListener('mouseleave', function() {
            // Vráť späť na pôvodný stav
            let hasChecked = false;
            let checkedIndex = 0;

            inputs.forEach((input, index) => {
                if (input.checked) {
                    hasChecked = true;
                    checkedIndex = index;
                }
            });

            wrapper.querySelectorAll('.rating-star').forEach((s, i) => {
                if (hasChecked && i < checkedIndex + 1) {
                    s.style.color = '#ff9800';
                } else {
                    s.style.color = '#ddd';
                }
            });
        });
    });
});
</script>
