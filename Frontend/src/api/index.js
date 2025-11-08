import axios from 'axios'

export const API_BASE = 'http://localhost:8000/api'

// Endpoints list centralizada
export const endpoints = {
	login: `${API_BASE}/login`,
	usuarios: `${API_BASE}/usuarios`,
	citas: `${API_BASE}/citas`,
	expedientes: `${API_BASE}/expedientes`,
	documentos: `${API_BASE}/documentos`,
	notificaciones: `${API_BASE}/notificaciones`
}

// Helpers
export function login(email, password) {
	return axios.post(endpoints.login, { email, password })
}

export * from './usuarios'
