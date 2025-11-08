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
        <router-link to="/citas" class="sidebar-link" active-class="active">Citas</router-link>
        <router-link to="/expedientes" class="sidebar-link" active-class="active">Expedientes</router-link>
        <router-link to="/documentos" class="sidebar-link" active-class="active">Documentos</router-link>
        <router-link to="/notificaciones" class="sidebar-link" active-class="active">Notificaciones</router-link>
      </nav>
    </aside>
    <main class="main-content">
      <router-view />
    </main>
  </div>
</template>

<script setup>
// No logic needed, just layout
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
  box-shadow: 2px 0 12px rgba(61,92,44,0.08);
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
.sidebar-link.active, .sidebar-link:hover {
  background: #fff;
  color: #3d5c2c;
  font-weight: bold;
}
.main-content {
  flex: 1;
  background: #fff;
  padding: 2.5rem 2rem 2rem 2rem;
  min-height: 100vh;
}
</style>
