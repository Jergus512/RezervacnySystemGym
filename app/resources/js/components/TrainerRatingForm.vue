<template>
    <div class="trainer-rating-form">
        <!-- Ak je hodnotenie už uložené, zobraz zmenšenú verziu -->
        <div v-if="userRating && !isEditing" class="card mb-3 bg-light border-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted d-block mb-1">Tvoje hodnotenie:</small>
                        <div style="font-size: 18px; color: #ff9800;">
                            <span v-for="i in 5" :key="i">
                                {{ i <= userRating ? '★' : '☆' }}
                            </span>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary"
                        @click="isEditing = true"
                    >
                        <i class="bi bi-pencil"></i> Upraviť
                    </button>
                </div>
            </div>
        </div>

        <!-- Formulár na hodnotenie -->
        <div v-else class="card mb-3">
            <div class="card-body">
                <h6 class="card-title mb-3">⭐ Ohodnoť tohto trénera</h6>

                <form @submit.prevent="submitRating">
                    <!-- Hodnotenie hviezdičkami -->
                    <div class="mb-3">
                        <label class="form-label small">Tvoje hodnotenie:</label>
                        <div class="rating-stars mb-2">
                            <button
                                v-for="star in 5"
                                :key="star"
                                type="button"
                                class="star-btn"
                                :class="{ 'active': rating >= star }"
                                @click="setRating(star)"
                                @mouseover="hoverRating = star"
                                @mouseleave="hoverRating = 0"
                            >
                                <i :class="getStarClass(star, hoverRating)"></i>
                            </button>
                        </div>
                        <small class="text-muted d-block mb-2">
                            {{ hoverRating > 0 ? getRatingText(hoverRating) : (rating > 0 ? getRatingText(rating) : 'Klikni na hviezdu') }}
                        </small>
                    </div>

                    <!-- Komentár -->
                    <div class="mb-3">
                        <label for="comment" class="form-label small">Komentár (voliteľný):</label>
                        <textarea
                            id="comment"
                            v-model="comment"
                            class="form-control form-control-sm"
                            rows="2"
                            placeholder="Napíš svoj komentár o tréneri..."
                            maxlength="500"
                        ></textarea>
                        <small class="text-muted">{{ comment.length }}/500</small>
                    </div>

                    <!-- Tlačidlá -->
                    <div class="d-flex gap-2">
                        <button
                            type="submit"
                            class="btn btn-sm"
                            style="background-color: #ff9800; color: white; border: none;"
                            :disabled="rating === 0 || isSubmitting"
                        >
                            <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2"></span>
                            {{ isSubmitting ? 'Ukladám...' : 'Uložiť hodnotenie' }}
                        </button>
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-secondary"
                            @click="resetForm"
                            :disabled="isSubmitting"
                        >
                            Zrušiť
                        </button>
                    </div>

                    <!-- Správy -->
                    <div v-if="successMessage" class="alert alert-success mt-3 mb-0 py-2">
                        <small>{{ successMessage }}</small>
                    </div>
                    <div v-if="errorMessage" class="alert alert-danger mt-3 mb-0 py-2">
                        <small>{{ errorMessage }}</small>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const props = defineProps({
    trainerId: {
        type: Number,
        required: true
    },
    trainingId: {
        type: [Number, String],
        default: null
    }
})

const rating = ref(0)
const hoverRating = ref(0)
const comment = ref('')
const isSubmitting = ref(false)
const isEditing = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const userRating = ref(null)

const ratingTexts = {
    1: '😞 Veľmi slabé',
    2: '😐 Slabé',
    3: '😊 OK',
    4: '😄 Dobré',
    5: '🤩 Vynikajúce!'
}

const getRatingText = (stars) => {
    return ratingTexts[stars] || ''
}

const getStarClass = (star, hover) => {
    const displayRating = hover > 0 ? hover : rating.value
    return displayRating >= star ? 'fas fa-star' : 'far fa-star'
}

const setRating = (stars) => {
    rating.value = stars
}

const resetForm = () => {
    rating.value = 0
    comment.value = ''
    hoverRating.value = 0
    successMessage.value = ''
    errorMessage.value = ''
    isEditing.value = false
}

const submitRating = async () => {
    if (rating.value === 0) {
        errorMessage.value = 'Musíš vybrať hodnotenie!'
        return
    }

    isSubmitting.value = true
    successMessage.value = ''
    errorMessage.value = ''

    try {
        const response = await fetch(`/trainers/${props.trainerId}/ratings`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                rating: rating.value,
                comment: comment.value || null,
                training_id: props.trainingId && props.trainingId !== 'null' ? props.trainingId : null
            })
        })

        const data = await response.json()

        if (response.ok) {
            successMessage.value = '✓ Tvoje hodnotenie bolo úspešne uložené!'
            userRating.value = rating.value
            // Obnov formulár na upravenú verziu
            setTimeout(() => {
                isEditing.value = false
            }, 1500)
        } else {
            errorMessage.value = data.message || 'Chyba pri ukladaní hodnotenia.'
        }
    } catch (error) {
        console.error('Error submitting rating:', error)
        errorMessage.value = 'Chyba pri komunikácii so serverom.'
    } finally {
        isSubmitting.value = false
    }
}

const loadUserRating = async () => {
    try {
        const response = await fetch(
            `/trainers/${props.trainerId}/ratings/check?training_id=${props.trainingId}`,
            {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            }
        )

        const data = await response.json()
        if (data.rating) {
            userRating.value = data.rating
        }
    } catch (error) {
        console.error('Error loading user rating:', error)
    }
}

onMounted(() => {
    loadUserRating()
})
</script>

<style scoped>
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

.trainer-rating-form {
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
