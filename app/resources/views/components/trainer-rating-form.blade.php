<!-- resources/views/components/trainer-rating-form.blade.php -->
<div class="trainer-rating-form">
    <!-- Ak je hodnotenie už uložené, zobraz zmenšenú verziu -->
    <div id="rating-display-{{ $trainer->id }}-{{ $training->id ?? 0 }}" class="card mb-3 bg-light border-0" style="display: none;">
        <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted d-block mb-1">Tvoje hodnotenie:</small>
                    <div id="user-rating-stars-{{ $trainer->id }}-{{ $training->id ?? 0 }}" style="font-size: 18px; color: #ff9800;">
                    </div>
                </div>
                <button
                    type="button"
                    class="btn btn-sm btn-outline-secondary"
                    onclick="editRating({{ $trainer->id }}, {{ $training->id ?? 0 }})"
                >
                    <i class="bi bi-pencil"></i> Upraviť
                </button>
            </div>
        </div>
    </div>

    <!-- Formulár na hodnotenie -->
    <div id="rating-form-{{ $trainer->id }}-{{ $training->id ?? 0 }}" class="card mb-3">
        <div class="card-body">
            <h6 class="card-title mb-3">⭐ Ohodnoť tohto trénera</h6>

            <form onsubmit="submitRating(event, {{ $trainer->id }}, {{ $training->id ?? 'null' }})">
                @csrf

                <!-- Hodnotenie hviezdičkami -->
                <div class="mb-3">
                    <label class="form-label small">Tvoje hodnotenie:</label>
                    <div class="rating-stars mb-2" id="stars-{{ $trainer->id }}-{{ $training->id ?? 0 }}">
                        @for($i = 1; $i <= 5; $i++)
                            <button
                                type="button"
                                class="star-btn"
                                onclick="setRating({{ $trainer->id }}, {{ $training->id ?? 0 }}, {{ $i }})"
                                onmouseover="hoverRating({{ $trainer->id }}, {{ $training->id ?? 0 }}, {{ $i }})"
                                onmouseout="hoverRating({{ $trainer->id }}, {{ $training->id ?? 0 }}, 0)"
                            >
                                <i class="far fa-star"></i>
                            </button>
                        @endfor
                    </div>
                    <small id="rating-text-{{ $trainer->id }}-{{ $training->id ?? 0 }}" class="text-muted d-block mb-2">
                        Klikni na hviezdu
                    </small>
                </div>

                <!-- Hidden input pre rating -->
                <input type="hidden" id="rating-value-{{ $trainer->id }}-{{ $training->id ?? 0 }}" name="rating" value="0">
                <input type="hidden" name="training_id" value="{{ $training->id ?? '' }}">

                <!-- Komentár -->
                <div class="mb-3">
                    <label for="comment-{{ $trainer->id }}-{{ $training->id ?? 0 }}" class="form-label small">Komentár (voliteľný):</label>
                    <textarea
                        id="comment-{{ $trainer->id }}-{{ $training->id ?? 0 }}"
                        class="form-control form-control-sm"
                        rows="2"
                        placeholder="Napíš svoj komentár o tréneri..."
                        maxlength="500"
                        onkeyup="updateCommentCount({{ $trainer->id }}, {{ $training->id ?? 0 }})"
                    ></textarea>
                    <small class="text-muted"><span id="char-count-{{ $trainer->id }}-{{ $training->id ?? 0 }}">0</span>/500</small>
                </div>

                <!-- Tlačidlá -->
                <div class="d-flex gap-2">
                    <button
                        type="submit"
                        class="btn btn-sm"
                        style="background-color: #ff9800; color: white; border: none;"
                        id="submit-btn-{{ $trainer->id }}-{{ $training->id ?? 0 }}"
                    >
                        Uložiť hodnotenie
                    </button>
                    <button
                        type="reset"
                        class="btn btn-sm btn-outline-secondary"
                        onclick="resetRatingForm({{ $trainer->id }}, {{ $training->id ?? 0 }})"
                    >
                        Zrušiť
                    </button>
                </div>

                <!-- Správy -->
                <div id="success-msg-{{ $trainer->id }}-{{ $training->id ?? 0 }}" class="alert alert-success mt-3 mb-0 py-2" style="display: none;">
                    <small id="success-text-{{ $trainer->id }}-{{ $training->id ?? 0 }}"></small>
                </div>
                <div id="error-msg-{{ $trainer->id }}-{{ $training->id ?? 0 }}" class="alert alert-danger mt-3 mb-0 py-2" style="display: none;">
                    <small id="error-text-{{ $trainer->id }}-{{ $training->id ?? 0 }}"></small>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.rating-stars {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.star-btn {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 2rem;
    color: #ddd;
    padding: 0;
    line-height: 1;
    transition: all 0.2s ease;
}

.star-btn:hover {
    color: #ff9800;
    transform: scale(1.15);
}

.star-btn.active {
    color: #ff9800;
}
</style>
