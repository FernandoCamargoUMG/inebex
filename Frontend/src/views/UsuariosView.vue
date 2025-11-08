<template>
  <div class="usuarios-container">
    <!-- Header con titulo y botón crear -->
    <div class="header-section">
      <el-row :gutter="20" align="middle">
        <el-col :span="16">
          <h1 class="page-title">
            <el-icon class="title-icon"><UserFilled /></el-icon>
            Gestión de Usuarios
          </h1>
        </el-col>
        <el-col :span="8" class="text-right">
          <el-button 
            type="primary" 
            @click="openCreateDialog"
            :icon="Plus"
            size="large"
            class="create-button">
            <strong>Nuevo Usuario</strong>
          </el-button>
        </el-col>
      </el-row>
    </div>

    <!-- Filtros -->
    <div class="filters-section">
      <el-row :gutter="20">
        <el-col :span="24">
          <el-card shadow="never" class="filter-card">
            <el-row :gutter="16" align="middle">
              <el-col :span="6">
                <el-input
                  v-model="filters.search"
                  placeholder="Buscar por nombre, email o teléfono..."
                  :prefix-icon="Search"
                  @input="debounceSearch"
                  clearable />
              </el-col>
              <el-col :span="4">
                <el-select 
                  v-model="filters.status" 
                  placeholder="Estado"
                  @change="applyFilters"
                  clearable>
                  <el-option label="Todos" value="" />
                  <el-option label="Activo" value="active" />
                  <el-option label="Inactivo" value="inactive" />
                </el-select>
              </el-col>
              <el-col :span="4">
                <el-select 
                  v-model="filters.role_id" 
                  placeholder="Rol"
                  @change="applyFilters"
                  clearable>
                  <el-option label="Todos" value="" />
                  <el-option 
                    v-for="role in roles" 
                    :key="role.id"
                    :label="role.name"
                    :value="role.id" />
                </el-select>
              </el-col>
              <el-col :span="4">
                <el-button 
                  @click="clearFilters" 
                  :icon="Refresh"
                  type="default"
                  class="clear-filters-btn">
                  Limpiar Filtros
                </el-button>
              </el-col>
              <el-col :span="6">
                <div class="stats-display">
                  <el-tag type="success" size="large">
                    Total: {{ totalUsuarios }}
                  </el-tag>
                  <el-tag type="primary" size="large" style="margin-left: 8px;">
                    Activos: {{ usuariosActivos }}
                  </el-tag>
                </div>
              </el-col>
            </el-row>
          </el-card>
        </el-col>
      </el-row>
    </div>

    <!-- Tabla de usuarios -->
    <div class="table-section">
      <el-card shadow="never">
        <!-- Estado vacío cuando no hay usuarios -->
        <div v-if="!loading && usuarios.length === 0" class="empty-state">
          <el-empty description="No hay usuarios registrados">
            <div class="empty-actions">
              <el-button 
                type="primary" 
                size="large"
                :icon="Plus"
                @click="openCreateDialog"
                class="empty-create-btn">
                Crear Primer Usuario
              </el-button>
              <el-button 
                type="success" 
                size="large"
                :icon="Setting"
                @click="$router.push('/roles')"
                class="empty-roles-btn">
                Gestionar Roles
              </el-button>
            </div>
            <div class="empty-help">
              <p>Los usuarios son las personas que pueden acceder al sistema.</p>
              <p>Cada usuario debe tener un rol asignado para definir sus permisos.</p>
            </div>
          </el-empty>
        </div>

        <!-- Tabla cuando hay datos -->
        <el-table 
          v-else
          :data="usuarios" 
          v-loading="loading"
          style="width: 100%"
          :default-sort="{ prop: 'first_name', order: 'ascending' }"
          :table-layout="'auto'">

          <el-table-column prop="first_name" label="Nombre" min-width="200" sortable>
            <template #default="{ row }">
              <div class="user-name">
                <el-avatar 
                  :size="32" 
                  :src="`https://ui-avatars.com/api/?name=${row.first_name}+${row.last_name}&background=random`"
                  class="user-avatar" />
                <div class="name-info">
                  <strong>{{ row.first_name }} {{ row.last_name }}</strong>
                  <div class="user-email">{{ row.email }}</div>
                </div>
              </div>
            </template>
          </el-table-column>

          <el-table-column prop="phone" label="Teléfono" width="140">
            <template #default="{ row }">
              <div class="phone-info">
                <el-icon class="phone-icon"><Phone /></el-icon>
                <span>{{ row.phone || 'N/A' }}</span>
              </div>
            </template>
          </el-table-column>

          <el-table-column prop="address" label="Dirección" min-width="180">
            <template #default="{ row }">
              <div class="address-info">
                <el-icon class="address-icon"><Location /></el-icon>
                <span>{{ row.address || 'N/A' }}</span>
              </div>
            </template>
          </el-table-column>

          <el-table-column prop="role_id" label="Rol" width="120" sortable align="center">
            <template #default="{ row }">
              <el-tag 
                :type="getRoleTagType(row.role_id)"
                size="small"
                effect="light">
                {{ getRoleName(row.role_id) }}
              </el-tag>
            </template>
          </el-table-column>

          <el-table-column prop="status" label="Estado" width="100" sortable align="center">
            <template #default="{ row }">
              <el-switch
                v-model="row.status"
                active-value="active"
                inactive-value="inactive"
                active-text="Activo"
                inactive-text="Inactivo"
                @change="toggleUserStatus(row)"
                :loading="row.updating" />
            </template>
          </el-table-column>

          <el-table-column prop="created_at" label="Creación" width="100" sortable>
            <template #default="{ row }">
              {{ formatDate(row.created_at) }}
            </template>
          </el-table-column>

          <el-table-column label="Acciones" width="140" fixed="right">
            <template #default="{ row }">
              <div class="action-buttons">
                <el-tooltip content="Ver detalles" placement="top">
                  <el-button 
                    type="info" 
                    :icon="View"
                    @click="viewUser(row)"
                    size="small"
                    plain />
                </el-tooltip>
                
                <el-tooltip content="Editar" placement="top">
                  <el-button 
                    type="warning" 
                    :icon="Edit"
                    @click="openEditDialog(row)"
                    size="small"
                    plain />
                </el-tooltip>

                <el-tooltip content="Eliminar" placement="top">
                  <el-button 
                    type="danger" 
                    :icon="Delete"
                    @click="confirmDelete(row)"
                    size="small"
                    plain />
                </el-tooltip>
              </div>
            </template>
          </el-table-column>
        </el-table>

        <!-- Paginación -->
        <div class="pagination-section">
          <el-pagination
            v-model:current-page="currentPage"
            v-model:page-size="pageSize"
            :page-sizes="[5, 10, 50, 100]"
            layout="total, sizes, prev, pager, next, jumper"
            :total="totalUsuarios"
            @size-change="loadUsuarios"
            @current-change="loadUsuarios"
            :prev-text="'Anterior'"
            :next-text="'Siguiente'" />
        </div>
      </el-card>
    </div>

    <!-- Modal Crear/Editar Usuario -->
    <el-dialog 
      v-model="dialogVisible" 
      :title="isEditing ? '✏️ Editar Usuario' : '👤 Nuevo Usuario'"
      width="900px"
      :close-on-click-modal="false"
      class="user-dialog"
      top="3vh">
      
      <el-form 
        ref="userFormRef"
        :model="userForm" 
        :rules="formRules"
        label-width="120px"
        label-position="left">
        
        <el-row :gutter="16">
          <el-col :span="12">
            <el-form-item label="Nombre" prop="first_name">
              <el-input 
                v-model="userForm.first_name" 
                placeholder="Nombre del usuario..." />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="Apellido" prop="last_name">
              <el-input 
                v-model="userForm.last_name" 
                placeholder="Apellido del usuario..." />
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="Email" prop="email">
          <el-input 
            v-model="userForm.email"
            type="email"
            placeholder="correo@ejemplo.com" />
        </el-form-item>

        <el-row :gutter="16">
          <el-col :span="12">
            <el-form-item label="Teléfono" prop="phone">
              <el-input 
                v-model="userForm.phone" 
                placeholder="+502 1234-5678" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="Estado" prop="status">
              <el-select v-model="userForm.status" placeholder="Selecciona estado" style="width: 100%">
                <el-option label="Activo" value="active" />
                <el-option label="Inactivo" value="inactive" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="Dirección" prop="address">
          <el-input 
            v-model="userForm.address"
            type="textarea"
            :rows="2"
            placeholder="Dirección completa del usuario..." />
        </el-form-item>

        <el-form-item label="Rol" prop="role_id">
          <el-select 
            v-model="userForm.role_id" 
            placeholder="Selecciona rol"
            style="width: 100%"
            filterable>
            <el-option 
              v-for="role in roles" 
              :key="role.id"
              :label="role.name"
              :value="role.id">
              <span style="float: left">{{ role.name }}</span>
              <span style="float: right; color: #8492a6; font-size: 13px">ID: {{ role.id }}</span>
            </el-option>
          </el-select>
        </el-form-item>

        <el-form-item 
          v-if="!isEditing"
          label="Contraseña" 
          prop="password">
          <el-input 
            v-model="userForm.password"
            type="password"
            placeholder="Contraseña del usuario..."
            show-password />
        </el-form-item>

        <el-form-item 
          v-if="!isEditing"
          label="Confirmar" 
          prop="password_confirmation">
          <el-input 
            v-model="userForm.password_confirmation"
            type="password"
            placeholder="Confirmar contraseña..."
            show-password />
        </el-form-item>
      </el-form>

      <template #footer>
        <div class="dialog-footer">
          <el-button @click="dialogVisible = false">Cancelar</el-button>
          <el-button 
            type="primary" 
            @click="saveUser"
            :loading="saving">
            {{ isEditing ? 'Actualizar' : 'Crear' }}
          </el-button>
        </div>
      </template>
    </el-dialog>

    <!-- Modal Ver Detalles de Usuario -->
    <el-dialog 
      v-model="viewDialogVisible" 
      title="👤 Detalles del Usuario"
      width="800px"
      class="view-dialog">
      
      <div v-if="selectedUser" class="user-details">
        <div class="user-header">
          <el-avatar 
            :size="80" 
            :src="`https://ui-avatars.com/api/?name=${selectedUser.first_name}+${selectedUser.last_name}&background=random&size=80`"
            class="profile-avatar" />
          <div class="user-info">
            <h2>{{ selectedUser.first_name }} {{ selectedUser.last_name }}</h2>
            <p class="user-email">{{ selectedUser.email }}</p>
            <el-tag 
              :type="selectedUser.status === 'active' ? 'success' : 'danger'"
              size="large">
              {{ selectedUser.status === 'active' ? 'Activo' : 'Inactivo' }}
            </el-tag>
          </div>
        </div>

        <el-divider />

        <el-descriptions title="Información Personal" :column="2" border>
          <el-descriptions-item label="Nombre Completo">
            <strong>{{ selectedUser.first_name }} {{ selectedUser.last_name }}</strong>
          </el-descriptions-item>
          <el-descriptions-item label="Email">
            {{ selectedUser.email }}
          </el-descriptions-item>
          <el-descriptions-item label="Teléfono">
            {{ selectedUser.phone || 'No especificado' }}
          </el-descriptions-item>
          <el-descriptions-item label="Rol">
            <el-tag 
              :type="getRoleTagType(selectedUser.role_id)"
              size="small">
              {{ getRoleName(selectedUser.role_id) }}
            </el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="Estado">
            <el-tag 
              :type="selectedUser.status === 'active' ? 'success' : 'danger'"
              size="small">
              {{ selectedUser.status === 'active' ? 'Activo' : 'Inactivo' }}
            </el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="Fecha de Registro">
            {{ formatDate(selectedUser.created_at) }}
          </el-descriptions-item>
        </el-descriptions>

        <div class="address-section">
          <el-divider />
          <h4>📍 Dirección</h4>
          <div class="address-content">
            {{ selectedUser.address || 'No especificada' }}
          </div>
        </div>
      </div>

      <template #footer>
        <div class="dialog-footer">
          <el-button @click="viewDialogVisible = false">Cerrar</el-button>
          <el-button 
            type="primary" 
            :icon="Edit"
            @click="openEditDialog(selectedUser)">
            Editar Usuario
          </el-button>
        </div>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { 
  UserFilled,
  Plus, 
  Search, 
  Refresh, 
  View,
  Edit, 
  Delete,
  Phone,
  Location,
  Setting
} from '@element-plus/icons-vue'
import axios from 'axios'
import dayjs from 'dayjs'

// Estados reactivos
const loading = ref(false)
const saving = ref(false)
const dialogVisible = ref(false)
const viewDialogVisible = ref(false)
const isEditing = ref(false)

// Datos
const usuarios = ref([])
const allUsuarios = ref([])
const roles = ref([])
const selectedUser = ref(null)

// Paginación
const currentPage = ref(1)
const pageSize = ref(20)
const totalUsuarios = computed(() => usuarios.value.length)
const usuariosActivos = computed(() => usuarios.value.filter(user => user.status === 'active').length)

// Filtros
const filters = reactive({
  search: '',
  status: '',
  role_id: ''
})

// Formulario
const userForm = reactive({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  address: '',
  status: 'active',
  role_id: null,
  password: '',
  password_confirmation: ''
})

// Reglas de validación
const formRules = {
  first_name: [
    { required: true, message: 'El nombre es obligatorio', trigger: 'blur' },
    { min: 2, max: 50, message: 'Debe tener entre 2 y 50 caracteres', trigger: 'blur' }
  ],
  last_name: [
    { required: true, message: 'El apellido es obligatorio', trigger: 'blur' },
    { min: 2, max: 50, message: 'Debe tener entre 2 y 50 caracteres', trigger: 'blur' }
  ],
  email: [
    { required: true, message: 'El email es obligatorio', trigger: 'blur' },
    { type: 'email', message: 'Formato de email inválido', trigger: 'blur' }
  ],
  phone: [
    { min: 8, max: 20, message: 'Debe tener entre 8 y 20 caracteres', trigger: 'blur' }
  ],
  status: [
    { required: true, message: 'El estado es obligatorio', trigger: 'change' }
  ],
  role_id: [
    { required: true, message: 'Debe seleccionar un rol', trigger: 'change' }
  ],
  password: [
    { required: true, message: 'La contraseña es obligatoria', trigger: 'blur' },
    { min: 6, message: 'Mínimo 6 caracteres', trigger: 'blur' }
  ],
  password_confirmation: [
    { required: true, message: 'Debe confirmar la contraseña', trigger: 'blur' },
    {
      validator: (rule, value, callback) => {
        if (value !== userForm.password) {
          callback(new Error('Las contraseñas no coinciden'))
        } else {
          callback()
        }
      },
      trigger: 'blur'
    }
  ]
}

const userFormRef = ref()

// Funciones utilitarias
const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return dayjs(dateString).format('DD/MM/YYYY')
}

const getRoleName = (roleId) => {
  const role = roles.value.find(r => r.id === roleId)
  return role ? role.name : 'N/A'
}

const getRoleTagType = (roleId) => {
  const roleTypes = {
    1: 'danger',  // Super Admin
    2: 'warning', // Admin
    3: 'primary', // Manager
    4: 'success', // Employee
    5: 'info'     // Client
  }
  return roleTypes[roleId] || 'info'
}

// Búsqueda con debounce
let searchTimeout
const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 500)
}

// Funciones principales
const loadUsuarios = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/usuarios')
    
    if (response.data && Array.isArray(response.data)) {
      allUsuarios.value = response.data
      applyFilters()
    }
  } catch (error) {
    console.error('Error loading usuarios:', error)
    ElMessage.error('Error al cargar usuarios')
  } finally {
    loading.value = false
  }
}

const loadRoles = async () => {
  try {
    const response = await axios.get('/api/roles')
    
    if (response.data && Array.isArray(response.data)) {
      roles.value = response.data
    }
  } catch (error) {
    console.error('Error loading roles:', error)
  }
}

const applyFilters = () => {
  let data = [...allUsuarios.value]
  
  // Filtro por búsqueda
  if (filters.search) {
    const search = filters.search.toLowerCase()
    data = data.filter(user => 
      user.first_name.toLowerCase().includes(search) ||
      user.last_name.toLowerCase().includes(search) ||
      user.email.toLowerCase().includes(search) ||
      (user.phone && user.phone.toLowerCase().includes(search))
    )
  }
  
  // Filtro por estado
  if (filters.status) {
    data = data.filter(user => user.status === filters.status)
  }
  
  // Filtro por rol
  if (filters.role_id) {
    data = data.filter(user => user.role_id === filters.role_id)
  }
  
  usuarios.value = data
}

// CRUD Operations
const openCreateDialog = () => {
  isEditing.value = false
  resetForm()
  dialogVisible.value = true
}

const openEditDialog = (user) => {
  isEditing.value = true
  Object.assign(userForm, {
    id: user.id,
    first_name: user.first_name,
    last_name: user.last_name,
    email: user.email,
    phone: user.phone || '',
    address: user.address || '',
    status: user.status,
    role_id: user.role_id,
    password: '',
    password_confirmation: ''
  })
  viewDialogVisible.value = false
  dialogVisible.value = true
}

const saveUser = async () => {
  if (!userFormRef.value) return
  
  const valid = await userFormRef.value.validate().catch(() => false)
  if (!valid) return

  saving.value = true
  try {
    let response
    const userData = { ...userForm }
    
    // Para edición, no enviar contraseña si está vacía
    if (isEditing.value) {
      delete userData.password
      delete userData.password_confirmation
    }
    
    if (isEditing.value) {
      response = await axios.put(`/api/usuarios/${userForm.id}`, userData)
    } else {
      response = await axios.post('/api/usuarios', userData)
    }

    if (response.data) {
      ElMessage.success(
        isEditing.value ? 'Usuario actualizado correctamente' : 'Usuario creado correctamente'
      )
      dialogVisible.value = false
      loadUsuarios()
    }
  } catch (error) {
    console.error('Error saving user:', error)
    if (error.response?.data?.errors) {
      const errorMessages = Object.values(error.response.data.errors).flat()
      ElMessage.error(errorMessages.join(', '))
    } else {
      ElMessage.error('Error al guardar usuario')
    }
  } finally {
    saving.value = false
  }
}

const viewUser = async (user) => {
  try {
    const response = await axios.get(`/api/usuarios/${user.id}`)
    if (response.data) {
      selectedUser.value = response.data
      viewDialogVisible.value = true
    }
  } catch (error) {
    console.error('Error loading user details:', error)
    ElMessage.error('Error al cargar detalles del usuario')
  }
}

const toggleUserStatus = async (user) => {
  user.updating = true
  try {
    const response = await axios.put(`/api/usuarios/${user.id}`, {
      first_name: user.first_name,
      last_name: user.last_name,
      email: user.email,
      phone: user.phone,
      address: user.address,
      status: user.status,
      role_id: user.role_id
    })

    if (response.data) {
      ElMessage.success(`Usuario ${user.status === 'active' ? 'activado' : 'desactivado'} correctamente`)
    }
  } catch (error) {
    console.error('Error updating user status:', error)
    // Revertir el cambio
    user.status = user.status === 'active' ? 'inactive' : 'active'
    ElMessage.error('Error al cambiar estado del usuario')
  } finally {
    user.updating = false
  }
}

const confirmDelete = (user) => {
  ElMessageBox.confirm(
    `¿Está seguro de eliminar al usuario "${user.first_name} ${user.last_name}"? Esta acción no se puede deshacer.`,
    '⚠️ Confirmar Eliminación',
    {
      confirmButtonText: 'Sí, Eliminar',
      cancelButtonText: 'Cancelar',
      type: 'warning'
    }
  ).then(() => {
    deleteUser(user.id)
  }).catch(() => {
    // Usuario canceló
  })
}

const deleteUser = async (id) => {
  try {
    const response = await axios.delete(`/api/usuarios/${id}`)
    if (response.data) {
      ElMessage.success('Usuario eliminado correctamente')
      loadUsuarios()
    }
  } catch (error) {
    console.error('Error deleting user:', error)
    ElMessage.error('Error al eliminar usuario')
  }
}

const resetForm = () => {
  Object.assign(userForm, {
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    address: '',
    status: 'active',
    role_id: null,
    password: '',
    password_confirmation: ''
  })
}

const clearFilters = () => {
  Object.assign(filters, {
    search: '',
    status: '',
    role_id: ''
  })
  // Recargar todos los usuarios sin filtros
  loadUsuarios()
}

// Montaje del componente
onMounted(() => {
  loadUsuarios()
  loadRoles()
})
</script>

<style scoped>
.usuarios-container {
  padding: 24px;
  max-width: 1600px;
  margin: 0 auto;
  background: #f5f7fa;
  min-height: 100vh;
}

.header-section {
  margin-bottom: 24px;
  padding: 0 4px;
}

.page-title {
  font-size: 32px;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 16px;
  text-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.title-icon {
  font-size: 36px;
  color: #6366f1;
  filter: drop-shadow(0 2px 4px rgba(99, 102, 241, 0.3));
}

.text-right {
  text-align: right;
}

.filters-section {
  margin-bottom: 24px;
}

.filter-card {
  border: none;
  background: white;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  border-radius: 12px;
  overflow: hidden;
}

.filter-card :deep(.el-card__body) {
  padding: 20px 24px;
}

.stats-display {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

.table-section {
  margin-bottom: 24px;
}

.table-section :deep(.el-card) {
  border: none;
  background: white;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  border-radius: 12px;
  overflow: hidden;
}

.table-section :deep(.el-card__body) {
  padding: 0;
}

.table-section :deep(.el-table) {
  border-radius: 12px;
}

.table-section :deep(.el-table th) {
  background: #f8fafc;
  color: #374151;
  font-weight: 600;
  border-bottom: 2px solid #e5e7eb;
}

.table-section :deep(.el-table td) {
  border-bottom: 1px solid #f3f4f6;
  padding: 14px 8px;
  vertical-align: middle;
}

.table-section :deep(.el-table tbody tr:hover) {
  background: #f8fafc;
}

.user-name {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-avatar {
  border: 2px solid #e5e7eb;
}

.name-info {
  flex: 1;
}

.user-email {
  font-size: 12px;
  color: #6b7280;
  margin-top: 2px;
}

.phone-info,
.address-info {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
}

.phone-icon,
.address-icon {
  color: #6b7280;
  font-size: 14px;
}

.action-buttons {
  display: flex;
  gap: 4px;
  justify-content: center;
}

.action-buttons .el-button {
  width: 32px !important;
  height: 32px !important;
  padding: 0 !important;
  border-radius: 6px !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  font-size: 14px !important;
  transition: all 0.2s ease !important;
  box-shadow: 0 2px 4px rgba(0,0,0,0.08) !important;
  border: 1px solid rgba(0,0,0,0.08) !important;
}

.action-buttons .el-button:hover {
  transform: translateY(-1px) scale(1.05) !important;
  box-shadow: 0 4px 8px rgba(0,0,0,0.12) !important;
}

.pagination-section {
  margin-top: 24px;
  text-align: center;
  padding: 20px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.dialog-footer {
  text-align: right;
  padding-top: 16px;
  border-top: 1px solid #e5e7eb;
}

/* Estado vacío */
.empty-state {
  padding: 60px 40px;
  text-align: center;
  background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
  border-radius: 16px;
  margin: 20px;
}

.empty-state :deep(.el-empty__image) {
  width: 120px !important;
  margin: 0 auto 24px;
}

.empty-state :deep(.el-empty__description) {
  font-size: 18px;
  font-weight: 600;
  color: #6b7280;
  margin-bottom: 32px;
}

.empty-actions {
  display: flex;
  flex-direction: column;
  gap: 16px;
  align-items: center;
  margin-bottom: 32px;
}

.empty-create-btn {
  background: linear-gradient(135deg, #6366f1, #4f46e5) !important;
  border: none !important;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4) !important;
  border-radius: 12px !important;
  padding: 16px 32px !important;
  font-size: 16px !important;
  font-weight: 600 !important;
  min-width: 200px !important;
  transition: all 0.3s ease !important;
}

.empty-create-btn:hover {
  transform: translateY(-2px) !important;
  box-shadow: 0 6px 16px rgba(99, 102, 241, 0.5) !important;
}

.empty-roles-btn {
  background: linear-gradient(135deg, #10b981, #059669) !important;
  color: white !important;
  border: none !important;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4) !important;
  border-radius: 12px !important;
  padding: 14px 28px !important;
  font-size: 15px !important;
  font-weight: 600 !important;
  min-width: 200px !important;
  transition: all 0.3s ease !important;
}

.empty-roles-btn:hover {
  transform: translateY(-2px) !important;
  box-shadow: 0 6px 16px rgba(16, 185, 129, 0.5) !important;
}

.empty-help {
  background: rgba(255, 255, 255, 0.8);
  border-radius: 12px;
  padding: 24px;
  border: 1px solid rgba(99, 102, 241, 0.2);
  backdrop-filter: blur(10px);
}

.empty-help p {
  margin: 0 0 12px 0;
  color: #6b7280;
  font-size: 14px;
  line-height: 1.6;
}

.empty-help p:last-child {
  margin-bottom: 0;
  font-weight: 500;
  color: #4f46e5;
}

/* Mejorar botón crear */
.create-button {
  background: linear-gradient(135deg, #6366f1, #4f46e5) !important;
  border: none !important;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4) !important;
  border-radius: 10px !important;
  padding: 12px 24px !important;
  font-size: 15px !important;
  transition: all 0.3s ease !important;
}

.create-button:hover {
  transform: translateY(-2px) !important;
  box-shadow: 0 6px 16px rgba(99, 102, 241, 0.5) !important;
}

.clear-filters-btn {
  border-radius: 8px !important;
  font-weight: 500 !important;
  transition: all 0.2s ease !important;
  background: #f8fafc !important;
  border: 1px solid #e2e8f0 !important;
  color: #64748b !important;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
  width: 100% !important;
}

.clear-filters-btn:hover {
  background: #f1f5f9 !important;
  border-color: #cbd5e1 !important;
  color: #475569 !important;
  transform: translateY(-1px) !important;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15) !important;
}

/* Mejorar diálogos */
.user-dialog :deep(.el-dialog),
.view-dialog :deep(.el-dialog) {
  border-radius: 16px;
  box-shadow: 0 25px 50px rgba(0,0,0,0.15);
  background: white;
}

.user-dialog :deep(.el-dialog__header),
.view-dialog :deep(.el-dialog__header) {
  background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
  border-radius: 16px 16px 0 0;
  padding: 20px 24px;
  border-bottom: 1px solid #e5e7eb;
}

.user-dialog :deep(.el-dialog__title),
.view-dialog :deep(.el-dialog__title) {
  font-size: 20px;
  font-weight: 700;
  color: #1f2937;
}

.user-dialog :deep(.el-dialog__body),
.view-dialog :deep(.el-dialog__body) {
  padding: 24px;
  background: white;
}

.user-details {
  max-height: 70vh;
  overflow-y: auto;
}

.user-header {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 20px;
}

.profile-avatar {
  border: 3px solid #e5e7eb;
}

.user-info h2 {
  color: #1f2937;
  margin: 0 0 8px 0;
  font-weight: 700;
  font-size: 24px;
}

.user-info .user-email {
  color: #6b7280;
  margin: 0 0 12px 0;
  font-size: 16px;
}

.address-section {
  margin-top: 20px;
}

.address-section h4 {
  color: #1f2937;
  margin-bottom: 12px;
  font-weight: 700;
  font-size: 16px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.address-content {
  background: #f8fafc;
  padding: 16px;
  border-radius: 8px;
  border-left: 4px solid #6366f1;
  color: #374151;
  line-height: 1.6;
}

/* Responsive */
@media (max-width: 1024px) {
  .usuarios-container {
    padding: 16px;
  }
  
  .page-title {
    font-size: 28px;
  }
  
  .empty-actions {
    flex-direction: column;
  }
  
  .empty-create-btn,
  .empty-roles-btn {
    min-width: 250px !important;
  }
  
  .filters-section .el-row {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
  }
}

@media (max-width: 768px) {
  .usuarios-container {
    padding: 12px;
  }
  
  .page-title {
    font-size: 24px;
    text-align: center;
    justify-content: center;
  }
  
  .header-section .el-row {
    flex-direction: column;
  }
  
  .text-right {
    text-align: center;
  }
  
  .table-section :deep(.el-table) {
    font-size: 12px;
  }
  
  .empty-state {
    padding: 40px 20px;
    margin: 10px;
  }
  
  .empty-actions {
    gap: 12px;
  }
  
  .empty-create-btn,
  .empty-roles-btn {
    min-width: 280px !important;
    padding: 16px 24px !important;
    font-size: 14px !important;
  }
  
  .empty-help {
    padding: 20px;
  }
  
  .empty-help p {
    font-size: 13px;
  }
  
  .filters-section .el-row {
    flex-direction: column;
  }
  
  .filters-section .el-col {
    width: 100% !important;
    margin-bottom: 12px;
  }
  
  .clear-filters-btn {
    width: 100% !important;
    min-width: 100% !important;
    font-size: 14px !important;
    padding: 12px 16px !important;
  }
  
  .stats-display {
    justify-content: center;
    flex-wrap: wrap;
  }
  
  .stats-display .el-tag {
    font-size: 14px !important;
    padding: 8px 16px !important;
  }
}
</style>
