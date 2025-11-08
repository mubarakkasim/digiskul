import { createI18n } from 'vue-i18n'
import en from '../locales/en.json'
import ha from '../locales/ha.json'
import ar from '../locales/ar.json'
import fr from '../locales/fr.json'

const messages = {
  en,
  ha,
  ar,
  fr
}

const savedLocale = localStorage.getItem('locale') || 'en'

export default createI18n({
  locale: savedLocale,
  fallbackLocale: 'en',
  messages,
  legacy: false
})

