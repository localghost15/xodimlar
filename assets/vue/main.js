import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { createI18n } from 'vue-i18n'
import App from './App.vue'
import ru from './locales/ru.json'
import uz from './locales/uz.json'
import './index.css'

const i18n = createI18n({
    legacy: false,
    locale: 'ru',
    messages: {
        ru,
        uz
    }
})

const app = createApp(App)
app.use(createPinia())
app.use(i18n)
app.mount('#app')
