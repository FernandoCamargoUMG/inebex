
<template>
  <div class="login-container">
    <div class="login-card">
      <img :src="logo" alt="Logo INEBEX" class="logo" />
      <h2>INEBEX - Ingreso</h2>
      <el-form :model="form" :rules="rules" ref="loginForm" label-width="80px" @submit.prevent="onLogin">
        <el-form-item label="Correo" prop="email">
          <el-input v-model="form.email" autocomplete="username" />
        </el-form-item>
        <el-form-item label="Contraseña" prop="password">
          <el-input v-model="form.password" type="password" autocomplete="current-password" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" style="width:100%" :loading="loading" @click="onLogin">Ingresar</el-button>
        </el-form-item>
        <el-form-item v-if="errorMsg">
          <el-alert :title="errorMsg" type="error" show-icon />
        </el-form-item>
      </el-form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import logo from '../assets/logo.png'
import { login } from '../api'

const router = useRouter()
const form = ref({ email: '', password: '' })
const rules = {
  email: [ { required: true, message: 'Correo requerido', trigger: 'blur' } ],
  password: [ { required: true, message: 'Contraseña requerida', trigger: 'blur' } ]
}
const loading = ref(false)
const errorMsg = ref('')

// usamos login desde src/api/index.js

async function onLogin() {
  loading.value = true
  errorMsg.value = ''
  try {
    // Autenticar mediante helper centralizado
    const resp = await login(form.value.email.trim(), form.value.password)
    const user = resp.data
    // Guardar usuario en sessionStorage
    sessionStorage.setItem('usuario', JSON.stringify(user))
    router.push('/dashboard')
  } catch (e) {
    if (e.response && e.response.data && e.response.data.message) {
      errorMsg.value = e.response.data.message
    } else {
      errorMsg.value = 'Error de conexión con el servidor'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  background: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
}
.login-card {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 16px rgba(0,0,0,0.08);
  padding: 2.5rem 2rem 2rem 2rem;
  width: 350px;
  display: flex;
  flex-direction: column;
  align-items: center;
  border: 3px solid #3d5c2c; /* Verde musgo */
}
.logo {
  width: 120px;
  margin-bottom: 1.5rem;
  border-radius: 50%;
  border: 2px solid #3d5c2c;
  background: #fff;
}
h2 {
  color: #3d5c2c;
  margin-bottom: 1.5rem;
  font-weight: bold;
}
.el-form-item__label {
  color: #3d5c2c;
}
.el-button--primary {
  background: #3d5c2c;
  border-color: #3d5c2c;
}
</style>
