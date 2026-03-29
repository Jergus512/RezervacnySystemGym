<template>
    <div class="trainer-rating-component">
        <!-- Formulár na hodnotenie -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">⭐ Ohodnoť tohto trénera</h5>

                <!-- Ak je používateľ prihlásený -->
                <form @submit.prevent="submitRating" v-if="isAuthenticated">
                    <!-- Hodnotenie hviezdičkami -->
                    <div class="mb-3">
                        <label class="form-label">Tvoje hodnotenie:</label>
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
                        <small class="text-muted">
                            {{ hoverRating > 0 ? getRatingText(hoverRating) : (rating > 0 ? getRatingText(rating) : 'Klikni na hviezdu') }}
                        </small>
                    </div>

                    <!-- Komentár -->
                    <div class="mb-3">
                        <label for="comment" class="form-label">Komentár (voliteľný):</label>
                        <textarea
                            id="comment"
                            v-model="comment"
                            class="form-control"
                            rows="3"
                            placeholder="Napíš svoj komentár o tréneri..."
                            maxlength="500"
                        ></textarea>
                        <small class="text-muted">{{ comment.length }}/500</small>
                    </div>

                    <!-- Tlačidlá -->
                    <div class="d-flex gap-2">
                        <button
                            type="submit"
                            class="btn btn-primary"
                            :disabled="rating === 0 || isSubmitting"
                        >
                            <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2"></span>
                            {{ isSubmitting ? 'Ukladám...' : 'Uložiť hodnotenie' }}
                        </button>
                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            @click="resetForm"
                        >
                            Zrušiť
                        </button>
                    </div>

                    <!-- Správy -->
                    <div v-if="successMessage" class="alert alert-success mt-3 mb-0">
                        {{ successMessage }}
                    </div>
                    <div v-if="errorMessage" class="alert alert-danger mt-3 mb-0">
                        {{ errorMessage }}
                    </div>
                </form>

                <!-- Ak používateľ nie je prihlásený -->
                <div v-else class="alert alert-info">
                    <p class="mb-0">
                        <a href="/login" class="alert-link">Prihlásiť sa</a>
                        aby si mohol ohodnotiť tohto trénera.
                    </p>
                </div>
            </div>
        </div>

        <!-- Zobrazenie všetkých hodnotení -->
        <div class="card" v-if="showRatings">
            <div class="card-body">
                <h5 class="card-title mb-4">📊 Hodnotenia trénerov</h5>

                <!-- Štatistika -->
                <div class="row mb-4" v-if="trainerStats">
                    <div class="col-md-6">
                        <div class="card bg-light border-0">
                            <div class="card-body text-center">
                                <h3 class="mb-0">
                                    <span style="color: #ffc107; font-size: 28px;">★</span>
                                    {{ trainerStats.avg_rating }}
                                </h3>
                                <small class="text-muted">
                                    na základe {{ trainerStats.total_ratings }} hodnotení
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!-- Rozdelenie hviezd -->
                        <div v-for="(stat, stars) in trainerStats.distribution" :key="stars" class="mb-2">
                            <div class="d-flex align-items-center">
                                <small class="me-2" style="min-width: 30px;">
                                    <span v-for="i in Number(stars)" :key="i" style="color: #ffc107;">★</span>
                                </small>
                                <div class="progress flex-grow-1" style="height: 20px;">
                                    <div
                                        class="progress-bar bg-warning"
                                        :style="{ width: stat.percentage + '%' }"
                                    >
                                        {{ stat.percentage }}%
                                    </div>
                                </div>
                                <small class="ms-2">{{ stat.count }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Zoznam hodnotení -->
                <div v-if="ratings.length > 0">
                    <h6 class="mb-3">Najnovšie hodnotenia:</h6>
                    <div
                        v-for="(ratingItem, index) in ratings.slice(0, 5)"
                        :key="index"
                        class="card mb-3 border-0 bg-light"
                    >
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1">{{ ratingItem.user.name }}</h6>
                                    <div style="font-size: 16px; color: #ffc107;">
                                        <span v-for="i in 5" :key="i">
                                            {{ i <= ratingItem.rating ? '★' : '☆' }}
                                        </span>
                                    </div>
                                </div>
                                <small class="text-muted">{{ formatDate(ratingItem.created_at) }}</small>
                            </div>
                            <p v-if="ratingItem.comment" class="mb-0 text-muted">
                                {{ ratingItem.comment }}
                            </p>
                        </div>
                    </div>
                </div>
                <div v-else class="alert alert-info mb-0">
                    Tento tréner nemá zatiaľ žiadne hodnotenia.
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'

const props = defineProps({
    trainerId: {
        type: Number,
        required: true
    },
    trainingId: {
        type: Number,
        default: null
    },
    showRatings: {
        type: Boolean,
        default: true
    }
})

const rating = ref(0)
const hoverRating = ref(0)
const comment = ref('')
const isSubmitting = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const ratings = ref([])
const trainerStats = ref(null)
const isAuthenticated = ref(false)

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
                training_id: props.trainingId
            })
        })

        const data = await response.json()

        if (response.ok) {
            successMessage.value = 'Tvoje hodnotenie bolo úspešne uložené!'
            resetForm()
            // Obnov štatistiky
            await loadRatings()
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

const loadRatings = async () => {
    try {
        const response = await fetch(`/trainers/${props.trainerId}/ratings`)
        const data = await response.json()

        if (response.ok) {
            ratings.value = data.ratings || []
            trainerStats.value = {
                avg_rating: data.avg_rating,
                total_ratings: data.total_ratings,
                distribution: data.distribution
            }
        }
    } catch (error) {
        console.error('Error loading ratings:', error)
    }
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('sk-SK', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

const checkAuthentication = () => {
    // Skontroluj či je používateľ prihlásený (check meta tag alebo CSRF token)
    isAuthenticated.value = !!document.querySelector('meta[name="csrf-token"]')?.content
}

onMounted(() => {
    checkAuthentication()
    if (props.showRatings) {
        loadRatings()
    }
})
</script>

<style scoped>
.rating-stars {
    display: flex;
    gap: 0.5rem;
}

.star-btn {
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
    font-size: 28px;
    color: #ddd;
    transition: color 0.2s ease;
}

.star-btn:hover,
.star-btn.active {
    color: #ffc107;
}

.star-btn i {
    display: inline-block;
}

.trainer-rating-component {
    margin: 1rem 0;
}
</style>
