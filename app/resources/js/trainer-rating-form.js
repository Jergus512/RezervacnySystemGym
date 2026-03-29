// Global state for trainer rating forms
const ratings = {};
const hovers = {};

const ratingTexts = {
    1: '😞 Veľmi slabé',
    2: '😐 Slabé',
    3: '😊 OK',
    4: '😄 Dobré',
    5: '🤩 Vynikajúce!'
};

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

// Load existing ratings when page loads or when new forms are added
function loadExistingRatings() {
    const forms = document.querySelectorAll('[id^="rating-form-"]');
    forms.forEach(form => {
        // Extract trainer ID and training ID from form ID
        const match = form.id.match(/rating-form-(\d+)-(\d+)/);
        if (match) {
            const trainerId = parseInt(match[1]);
            const trainingId = parseInt(match[2]);

            // Only load if we have a training ID
            if (trainingId > 0) {
                loadRating(trainerId, trainingId);
            }
        }
    });
}

async function loadRating(trainerId, trainingId) {
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
}

// Load ratings on page load
document.addEventListener('DOMContentLoaded', loadExistingRatings);

// Also load ratings when dynamic content is added (for filters, etc.)
const observer = new MutationObserver(() => {
    loadExistingRatings();
});

observer.observe(document.body, {
    childList: true,
    subtree: true
});
