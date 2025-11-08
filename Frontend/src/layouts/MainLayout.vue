<template>
  <div class="layout">
    <aside class="sidebar">
      <div class="sidebar-header">
        <img src="/src/assets/logo.png" class="sidebar-logo" alt="Logo" />
        <span class="sidebar-title">INEBEX</span>
      </div>
      <nav class="sidebar-menu">
        <router-link to="/dashboard" class="sidebar-link" active-class="active">Dashboard</router-link>
        <router-link to="/usuarios" class="sidebar-link" active-class="active">Usuarios</router-link>
        <!--  <router-link to="/citas" class="sidebar-link" active-class="active">Citas</router-link> -->
        <router-link to="/tipos-citas" class="sidebar-link" active-class="active">Tipos Citas</router-link>
        <router-link to="/incidencias" class="sidebar-link" active-class="active">Incidencias</router-link>
        <router-link to="/tipos-documentos" class="sidebar-link" active-class="active">Tipos Documentos</router-link>
        <router-link to="/expedientes" class="sidebar-link" active-class="active">Expedientes</router-link>
        <!-- <router-link to="/documentos" class="sidebar-link" active-class="active">Documentos</router-link>-->
        <router-link to="/perfiles" class="sidebar-link" active-class="active">Perfiles</router-link>
        <!-- <router-link to="/notificaciones" class="sidebar-link" active-class="active">Notificaciones</router-link> -->
      </nav>
      
      <!-- Botón de cerrar sesión -->
      <div class="sidebar-footer">
        <button @click="logout" class="logout-button">
          <el-icon class="logout-icon"><SwitchButton /></el-icon>
          Cerrar Sesión
        </button>
      </div>
    </aside>
    <main class="main-content">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { ElMessage, ElMessageBox, ElIcon } from 'element-plus'
import { SwitchButton } from '@element-plus/icons-vue'

const router = useRouter()

const logout = () => {
  ElMessageBox.confirm(
    '¿Está seguro de que desea cerrar sesión?',
    '🚪 Cerrar Sesión',
    {
      confirmButtonText: 'Sí, Cerrar Sesión',
      cancelButtonText: 'Cancelar',
      type: 'warning'
    }
  ).then(() => {
    // Limpiar localStorage/sessionStorage si es necesario
    localStorage.removeItem('user')
    localStorage.removeItem('token')
    sessionStorage.clear()
    
    // Mostrar mensaje de despedida
    ElMessage.success('Sesión cerrada correctamente')
    
    // Redirigir al login después de un breve delay
    setTimeout(() => {
      router.push('/login')
    }, 1000)
  }).catch(() => {
    // Usuario canceló
  })
}
</script>

<style scoped>
.layout {
  display: flex;
  min-height: 100vh;
  background: #f8faf8;
}

.sidebar {
  width: 240px;
  background: #3d5c2c;
  color: #fff;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 2rem 0 1rem 0;
  box-shadow: 2px 0 12px rgba(61, 92, 44, 0.08);
}

.sidebar-header {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 2rem;
}

.sidebar-logo {
  width: 80px;
  border-radius: 50%;
  border: 2px solid #fff;
  margin-bottom: 0.5rem;
  background: #fff;
}

.sidebar-title {
  font-size: 1.3rem;
  font-weight: bold;
  letter-spacing: 1px;
  color: #fff;
}

.sidebar-menu {
  display: flex;
  flex-direction: column;
  gap: 1.2rem;
  width: 100%;
  align-items: center;
}

.sidebar-link {
  color: #fff;
  text-decoration: none;
  font-size: 1.08rem;
  padding: 0.6rem 1.2rem;
  border-radius: 8px;
  transition: background 0.2s, color 0.2s;
  width: 80%;
  text-align: center;
}

.sidebar-link.active,
.sidebar-link:hover {
  background: #fff;
  color: #3d5c2c;
  font-weight: bold;
}

.sidebar-footer {
  margin-top: auto;
  padding: 1rem 0;
  width: 100%;
  display: flex;
  justify-content: center;
}

.logout-button {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  border: none;
  color: white;
  padding: 0.8rem 1.5rem;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  width: 80%;
  justify-content: center;
  box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
}

.logout-button:hover {
  background: linear-gradient(135deg, #dc2626, #b91c1c);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
}

.logout-icon {
  font-size: 1.1rem;
  transition: transform 0.2s ease;
}

.logout-button:hover .logout-icon {
  transform: rotate(-10deg);
}

.main-content {
  flex: 1;
  background: #fff;
  padding: 2.5rem 2rem 2rem 2rem;
  min-height: 100vh;
}
</style>
