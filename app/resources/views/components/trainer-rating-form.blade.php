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
                        @error('rating')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Training ID (ak je dostupný) -->
                    @if($training ?? false)
                        <input type="hidden" name="training_id" value="{{ $training->id }}">
                    @endif

                    <!-- Tlačidlo -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn" style="background-color: #ff9800; color: white; border: none;">
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
                <div class="alert" style="background-color: #fff3e0; border-color: #ff9800; color: #e65100;">
                    <p class="mb-0">
                        <a href="{{ route('login') }}" style="color: #e65100;">Prihlásiť sa</a>
                        aby si mohol ohodnotiť tohto trénera.
                    </p>
                </div>
            @endauth
        </div>
    </div>
</div>

<style>
.rating-stars {
    display: flex;
    gap: 1rem;
    flex-direction: row-reverse;
    justify-content: flex-end;
    width: fit-content;
}

.star-label {
    cursor: pointer;
    font-size: 2.5rem;
    color: #d0d0d0;
    transition: all 0.2s ease;
    border: 2px solid #ccc;
    border-radius: 8px;
    padding: 8px 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.star-label:hover {
    color: #ff9800;
    border-color: #ff9800;
    background-color: #fff9f0;
    transform: scale(1.1);
}

#ratingStars input:checked + .star-label,
#ratingStars input:checked ~ .star-label:hover {
    color: #ff9800;
    border-color: #ff9800;
    background-color: #fff9f0;
}

/* Interaktivita - pri hoveru sa zvýraznia aj všetky hviezdy vľavo */
#ratingStars label:hover ~ .star-label {
    color: #ff9800;
    border-color: #ff9800;
}

#ratingStars input:checked ~ label {
    color: #ff9800;
    border-color: #ff9800;
    background-color: #fff9f0;
}
</style>
