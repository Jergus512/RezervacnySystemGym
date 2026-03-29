import './bootstrap';
import { createApp } from 'vue';
import TrainerRatingForm from '@/components/TrainerRatingForm.vue';
import TrainerRatingComponent from '@/components/TrainerRatingComponent.vue';

const app = createApp({});

// Registuj Vue komponenty ako custom elements
app.component('trainer-rating-form', TrainerRatingForm);
app.component('trainer-rating-component', TrainerRatingComponent);

// Mount app na všetky Vue elementy
const vueElements = document.querySelectorAll('[data-vue]');
vueElements.forEach(el => {
    const rootDiv = document.createElement('div');
    el.parentNode.insertBefore(rootDiv, el);
    el.remove();
    app.mount(rootDiv);
});

// Ak nie sú žiadne markery, aplikuj na body
if (vueElements.length === 0) {
    app.mount('#app');
}
