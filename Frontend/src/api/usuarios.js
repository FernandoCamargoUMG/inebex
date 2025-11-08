import axios from 'axios'

const API_URL = 'http://localhost:8000/api/usuarios'

export function getUsuarios() {
  return axios.get(API_URL)
}

export function createUsuario(data) {
  return axios.post(API_URL, data)
}

export function updateUsuario(id, data) {
  return axios.put(`${API_URL}/${id}`, data)
}

export function deleteUsuario(id) {
  return axios.delete(`${API_URL}/${id}`)
}
