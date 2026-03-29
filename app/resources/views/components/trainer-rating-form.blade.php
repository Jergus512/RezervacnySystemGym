<!-- resources/views/components/trainer-rating-form.blade.php -->
<div class="trainer-rating-form">
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">⭐ Ohodnoť tohto trénera</h5>

            @auth
                <form action="{{ route('trainer-ratings.store', $trainer) }}" method="POST">
                    @csrf

                    <!-- Hodnotenie hviezdičkami -->
                    <div class="mb-3">
                        <label class="form-label">Tvoje hodnotenie:</label>
                        <div class="rating-stars mb-2" id="ratingStars">
                            @for($i = 1; $i <= 5; $i++)
                                <input
                                    type="radio"
                                    name="rating"
                                    id="star{{ $i }}"
                                    value="{{ $i }}"
                                    class="d-none"
                                    required
                                >
                                <label for="star{{ $i }}" class="star-label">
                                    <i class="fas fa-star"></i>
                                </label>
                            @endfor
                        </div>
                        <small class="text-muted">Vyberte počet hviezd</small>
                        @error('rating')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Komentár -->
                    <div class="mb-3">
                        <label for="comment" class="form-label">Komentár (voliteľný):</label>
                        <textarea
                            id="comment"
                            name="comment"
                            class="form-control @error('comment') is-invalid @enderror"
                            rows="3"
                            placeholder="Napíš svoj komentár o tréneri..."
                            maxlength="500"
                        >{{ old('comment') }}</textarea>
                        <small class="text-muted">
                            <span id="charCount">0</span>/500
                        </small>
                        @error('comment')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Training ID (ak je dostupný) -->
                    @if($training ?? false)
                        <input type="hidden" name="training_id" value="{{ $training->id }}">
                    @endif

                    <!-- Tlačidlá -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            Uložiť hodnotenie
                        </button>
                        <button type="reset" class="btn btn-outline-secondary">
                            Zrušiť
                        </button>
                    </div>

                    <!-- Správy -->
                    @if($errors->any())
                        <div class="alert alert-danger mt-3 mb-0">
                            {{ $errors->first() }}
                        </div>
                    @endif
                </form>

                @if(session('status'))
                    <div class="alert alert-success mt-3 mb-0">
                        {{ session('status') }}
                    </div>
                @endif
            @else
                <div class="alert alert-info">
                    <p class="mb-0">
                        <a href="{{ route('login') }}" class="alert-link">Prihlásiť sa</a>
                        aby si mohol ohodnotiť tohto trénera.
                    </p>
                </div>
            @endauth
        </div>
    </div>
</div>

<style scoped>
.rating-stars {
    display: flex;
    gap: 0.5rem;
    flex-direction: row-reverse;
    justify-content: flex-end;
    width: fit-content;
}

.star-label {
    font-size: 28px;
    color: #ddd;
    cursor: pointer;
    transition: color 0.2s ease;
    padding: 0.25rem;
}

.star-label:hover,
.star-label:hover ~ label,
#star1:checked ~ label,
#star2:checked ~ label,
#star3:checked ~ label,
#star4:checked ~ label,
#star5:checked ~ label {
    color: #ffc107;
}

.star-label i {
    display: inline-block;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Počítanie znakov v komentári
    const commentInput = document.getElementById('comment');
    const charCount = document.getElementById('charCount');

    if (commentInput && charCount) {
        commentInput.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    }

    // Inicializácia počítadla
    if (commentInput && charCount) {
        charCount.textContent = commentInput.value.length;
    }
});
</script>
