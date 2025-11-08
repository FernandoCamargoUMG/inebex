
import { createApp } from 'vue'
import './style.css'
import App from './App.vue'
import router from './router'
import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css'
// Importar idioma español para Element Plus
import locale from 'element-plus/dist/locale/es.mjs'
// Configurar axios globalmente
import axios from 'axios'

// Configurar base URL para axios
axios.defaults.baseURL = 'http://localhost:8000'

// Interceptor para manejar errores globalmente
axios.interceptors.response.use(
  response => response,
  error => {
    console.error('Error en petición axios:', error)
    return Promise.reject(error)
  }
)

const app = createApp(App)
app.use(router)
app.use(ElementPlus, {
  locale: locale,
})
app.config.globalProperties.$http = axios
app.mount('#app')
