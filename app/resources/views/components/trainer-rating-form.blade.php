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

<script>
const ratingTexts = {
    1: '😞 Veľmi slabé',
    2: '😐 Slabé',
    3: '😊 OK',
    4: '😄 Dobré',
    5: '🤩 Vynikajúce!'
};

const ratings = {};
const hovers = {};

function setRating(trainerId, trainingId, value) {
    const key = `${trainerId}-${trainingId}`;
    ratings[key] = value;
    document.getElementById(`rating-value-${trainerId}-${trainingId}`).value = value;
    updateStarDisplay(trainerId, trainingId, value);
    updateRatingText(trainerId, trainingId, value);
}

function hoverRating(trainerId, trainingId, value) {
    const key = `${trainerId}-${trainingId}`;
    hovers[key] = value;
    const displayValue = value > 0 ? value : (ratings[key] || 0);
    updateStarDisplay(trainerId, trainingId, displayValue);
    if (value > 0) {
        updateRatingText(trainerId, trainingId, value);
    }
}

function updateStarDisplay(trainerId, trainingId, value) {
    const stars = document.querySelectorAll(`#stars-${trainerId}-${trainingId} .star-btn`);
    stars.forEach((star, index) => {
        if (index + 1 <= value) {
            star.innerHTML = '<i class="fas fa-star"></i>';
            star.classList.add('active');
        } else {
            star.innerHTML = '<i class="far fa-star"></i>';
            star.classList.remove('active');
        }
    });
}

function updateRatingText(trainerId, trainingId, value) {
    const textEl = document.getElementById(`rating-text-${trainerId}-${trainingId}`);
    textEl.textContent = ratingTexts[value] || 'Klikni na hviezdu';
}

function updateCommentCount(trainerId, trainingId) {
    const textarea = document.getElementById(`comment-${trainerId}-${trainingId}`);
    const counter = document.getElementById(`char-count-${trainerId}-${trainingId}`);
    counter.textContent = textarea.value.length;
}

function resetRatingForm(trainerId, trainingId) {
    const key = `${trainerId}-${trainingId}`;
    ratings[key] = 0;
    hovers[key] = 0;
    document.getElementById(`rating-value-${trainerId}-${trainingId}`).value = 0;
    document.getElementById(`comment-${trainerId}-${trainingId}`).value = '';
    updateStarDisplay(trainerId, trainingId, 0);
    updateRatingText(trainerId, trainingId, 0);
    updateCommentCount(trainerId, trainingId);
    document.getElementById(`success-msg-${trainerId}-${trainingId}`).style.display = 'none';
    document.getElementById(`error-msg-${trainerId}-${trainingId}`).style.display = 'none';
}

function editRating(trainerId, trainingId) {
    document.getElementById(`rating-form-${trainerId}-${trainingId}`).style.display = 'block';
    document.getElementById(`rating-display-${trainerId}-${trainingId}`).style.display = 'none';
}

async function submitRating(event, trainerId, trainingId) {
    event.preventDefault();

    const key = `${trainerId}-${trainingId}`;
    const rating = ratings[key] || 0;

    if (rating === 0) {
        const errorEl = document.getElementById(`error-msg-${trainerId}-${trainingId}`);
        document.getElementById(`error-text-${trainerId}-${trainingId}`).textContent = 'Musíš vybrať hodnotenie!';
        errorEl.style.display = 'block';
        return;
    }

    const submitBtn = document.getElementById(`submit-btn-${trainerId}-${trainingId}`);
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Ukladám...';

    try {
        const comment = document.getElementById(`comment-${trainerId}-${trainingId}`).value;
        const response = await fetch(`/trainers/${trainerId}/ratings`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                rating: rating,
                comment: comment || null,
                training_id: trainingId && trainingId !== 'null' ? trainingId : null
            })
        });

        const data = await response.json();

        if (response.ok) {
            document.getElementById(`success-msg-${trainerId}-${trainingId}`).style.display = 'block';
            document.getElementById(`success-text-${trainerId}-${trainingId}`).textContent = '✓ Tvoje hodnotenie bolo úspešne uložené!';

            // Prikaž zmenšenú verziu po chvíli
            setTimeout(() => {
                showRatingDisplay(trainerId, trainingId, rating);
            }, 1500);
        } else {
            document.getElementById(`error-msg-${trainerId}-${trainingId}`).style.display = 'block';
            document.getElementById(`error-text-${trainerId}-${trainingId}`).textContent = data.message || 'Chyba pri ukladaní hodnotenia.';
        }
    } catch (error) {
        console.error('Error submitting rating:', error);
        document.getElementById(`error-msg-${trainerId}-${trainingId}`).style.display = 'block';
        document.getElementById(`error-text-${trainerId}-${trainingId}`).textContent = 'Chyba pri komunikácii so serverom.';
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Uložiť hodnotenie';
    }
}

function showRatingDisplay(trainerId, trainingId, rating) {
    document.getElementById(`rating-form-${trainerId}-${trainingId}`).style.display = 'none';
    document.getElementById(`rating-display-${trainerId}-${trainingId}`).style.display = 'block';

    // Zobraz hviezdičky
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        stars += i <= rating ? '★' : '☆';
    }
    document.getElementById(`user-rating-stars-${trainerId}-${trainingId}`).textContent = stars;
}

// Load existing rating na load stránky
document.addEventListener('DOMContentLoaded', async () => {
    @if($training ?? false)
        const trainerId = {{ $trainer->id }};
        const trainingId = {{ $training->id }};

        try {
            const response = await fetch(`/trainers/${trainerId}/ratings/check?training_id=${trainingId}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const data = await response.json();
            if (data.rating) {
                showRatingDisplay(trainerId, trainingId, data.rating);
            }
        } catch (error) {
            console.error('Error loading rating:', error);
        }
    @endif
});
</script>
