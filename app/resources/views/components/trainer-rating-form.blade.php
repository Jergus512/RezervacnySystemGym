<!-- resources/views/components/trainer-rating-form.blade.php -->
<div class="trainer-rating-wrapper mb-3">
    <div class="card">
        <div class="card-body">
            <h6 class="card-title mb-3">⭐ Ohodnoť tohto trénera</h6>

            <form method="POST" action="{{ route('trainer-ratings.store', $trainer) }}">
                @csrf

                <!-- Skryté pole pre training_id -->
                @if($training ?? false)
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

                <!-- Komentár -->
                <div class="mb-3">
                    <label for="comment_{{ $trainer->id }}_{{ $training->id ?? 0 }}" class="form-label small">Komentár (voliteľný):</label>
                    <textarea
                        id="comment_{{ $trainer->id }}_{{ $training->id ?? 0 }}"
                        name="comment"
                        class="form-control form-control-sm"
                        rows="2"
                        placeholder="Napíš svoj komentár o tréneri..."
                        maxlength="500"
                    ></textarea>
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
                        // Zisti hviezdu a aktivuj ju
                        wrapper.querySelectorAll('.rating-star').forEach((s, i) => {
                            if (i < rating) {
                                s.classList.add('active');
                                s.style.color = '#ff9800';
                            } else {
                                s.classList.remove('active');
                                s.style.color = '#ddd';
                            }
                        });
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
            inputs.forEach((input, index) => {
                if (input.checked) {
                    wrapper.querySelectorAll('.rating-star').forEach((s, i) => {
                        if (i < index + 1) {
                            s.style.color = '#ff9800';
                        } else {
                            s.style.color = '#ddd';
                        }
                    });
                } else {
                    wrapper.querySelectorAll('.rating-star').forEach(s => {
                        s.style.color = '#ddd';
                    });
                }
            });
        });
    });
});
</script>
