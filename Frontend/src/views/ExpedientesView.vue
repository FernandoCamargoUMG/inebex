<template>
  <div class="expedientes-container">
    <!-- Header con titulo y botón crear -->
    <div class="header-section">
      <el-row :gutter="20" align="middle">
        <el-col :span="16">
          <h1 class="page-title">
            <el-icon class="title-icon"><Document /></el-icon>
            Gestión de Expedientes
          </h1>
        </el-col>
        <el-col :span="8" class="text-right">
          <el-button 
            type="primary" 
            @click="openCreateDialog"
            :icon="Plus"
            size="large"
            class="create-button">
            <strong>Nuevo Expediente</strong>
          </el-button>
        </el-col>
      </el-row>
    </div>

    <!-- Filtros y estadísticas -->
    <div class="filters-section">
      <el-row :gutter="20">
        <el-col :span="22">
          <el-card shadow="never" class="filter-card">
            <el-row :gutter="16" align="middle">
              <el-col :span="6">
                <el-select 
                  v-model="filters.status" 
                  placeholder="Filtrar por estado"
                  clearable
                  @change="loadExpedientes">
                  <el-option label="Todos los estados" value="" />
                  <el-option label="Creado" value="created" />
                  <el-option label="En Revisión" value="under_review" />
                  <el-option label="Aprobado" value="approved" />
                  <el-option label="Rechazado" value="rejected" />
                  <el-option label="Activo" value="active" />
                  <el-option label="Inactivo" value="inactive" />
                  <el-option label="Completado" value="completed" />
                  <el-option label="Cancelado" value="cancelled" />
                </el-select>
              </el-col>
              <el-col :span="6">
                <el-select 
                  v-model="filters.profile_id" 
                  placeholder="Filtrar por perfil"
                  clearable
                  @change="loadExpedientes">
                  <el-option label="Todos los perfiles" value="" />
                  <el-option 
                    v-for="profile in profiles"
                    :key="profile.id"
                    :label="profile.name"
                    :value="profile.id" />
                </el-select>
              </el-col>
              <el-col :span="8">
                <el-input
                  v-model="filters.search"
                  placeholder="Buscar por número o usuario..."
                  :prefix-icon="Search"
                  @input="debounceSearch"
                  clearable />
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
            </el-row>
          </el-card>
        </el-col>
        <!----<el-col :span="6">
          <el-card shadow="never" class="stats-card">
            <div class="stat-item">
              <span class="stat-number">{{ totalExpedientes }}</span>
              <span class="stat-label">Total Expedientes</span>
            </div>
          </el-card>
        </el-col>-->
      </el-row>
    </div>

    <!-- Tabla de expedientes -->
    <div class="table-section">
      <el-card shadow="never">
        <el-table 
          :data="expedientes" 
          v-loading="loading"
          style="width: 100%"
          :default-sort="{ prop: 'created_at', order: 'descending' }"
          :table-layout="'auto'">
          
          <!----<el-table-column prop="id" label="ID" width="60" sortable />-->
          
          <el-table-column prop="record_number" label="N° Expediente" width="160" sortable>
            <template #default="{ row }">
              <el-tag effect="plain" size="small" type="info" class="record-number-tag">
                📄 {{ row.record_number }}
              </el-tag>
            </template>
          </el-table-column>

          <el-table-column prop="user.name" label="Usuario Encargado" width="220" sortable>
            <template #default="{ row }">
              <div class="user-info">
                <el-avatar :size="28" class="user-avatar">
                  {{ getUserInitials(row.user) }}
                </el-avatar>
                <div class="user-details">
                  <div class="user-name" :title="getUserFullName(row.user)">
                    {{ getUserFullName(row.user) }}
                  </div>
                  <div class="user-email" :title="row.user?.email || ''">
                    {{ row.user?.email || '' }}
                  </div>
                </div>
              </div>
            </template>
          </el-table-column>

          <el-table-column prop="profile.name" label="Perfil" width="200" sortable>
            <template #default="{ row }">
              <el-tag type="info" effect="light" size="small" class="profile-tag">
                👤 {{ row.profile?.name || 'N/A' }}
              </el-tag>
            </template>
          </el-table-column>

          <el-table-column prop="status" label="Estado" width="130" sortable>
            <template #default="{ row }">
              <el-tag 
                :type="getStatusType(row.status)" 
                effect="light"
                size="small">
                {{ getStatusLabel(row.status) }}
              </el-tag>
            </template>
          </el-table-column>

          <el-table-column prop="documents_count" label="Docs" width="90" align="center">
            <template #default="{ row }">
              <div class="documents-column">
                <el-badge :value="row.documents?.length || 0" :max="99" class="documents-badge">
                  <el-icon size="20"><Document /></el-icon>
                </el-badge>
              </div>
            </template>
          </el-table-column>

          <el-table-column prop="created_at" label="Fecha Creación" width="160" sortable>
            <template #default="{ row }">
              {{ formatDate(row.created_at) }}
            </template>
          </el-table-column>

          <el-table-column label="Acciones" width="320" fixed="right">
            <template #default="{ row }">
              <div class="action-buttons">
                <!-- Botones básicos siempre visibles -->
                <el-tooltip content="Ver detalles" placement="top">
                  <el-button 
                    type="primary" 
                    :icon="View"
                    @click="viewExpediente(row)"
                    size="small"
                    plain
                    circle />
                </el-tooltip>
                
                <el-tooltip content="Editar datos" placement="top">
                  <el-button 
                    type="warning" 
                    :icon="Edit"
                    @click="openEditDialog(row)"
                    size="small"
                    plain
                    circle />
                </el-tooltip>

                <!-- Botones de cambio de estado según estado actual -->
                <template v-if="row.status === 'created'">
                  <el-tooltip content="Pasar a revisión" placement="top">
                    <el-button 
                      type="info" 
                      @click="changeStatus(row, 'under_review')"
                      size="small">
                      Revisar
                    </el-button>
                  </el-tooltip>
                </template>

                <template v-else-if="row.status === 'under_review'">
                  <el-tooltip content="Aprobar expediente" placement="top">
                    <el-button 
                      type="success" 
                      @click="changeStatus(row, 'approved')"
                      size="small">
                      Aprobar
                    </el-button>
                  </el-tooltip>
                  <el-tooltip content="Rechazar expediente" placement="top">
                    <el-button 
                      type="danger" 
                      @click="changeStatus(row, 'rejected')"
                      size="small">
                      Rechazar
                    </el-button>
                  </el-tooltip>
                </template>

                <template v-else-if="row.status === 'approved'">
                  <el-tooltip content="Marcar como completado" placement="top">
                    <el-button 
                      type="success" 
                      @click="changeStatus(row, 'completed')"
                      size="small">
                      Completar
                    </el-button>
                  </el-tooltip>
                </template>

                <template v-else-if="row.status === 'rejected'">
                  <el-tooltip content="Reabrir para revisión" placement="top">
                    <el-button 
                      type="warning" 
                      @click="changeStatus(row, 'under_review')"
                      size="small">
                      Reabrir
                    </el-button>
                  </el-tooltip>
                </template>

                <!-- Botón eliminar siempre disponible -->
                <el-tooltip content="Eliminar expediente" placement="top">
                  <el-button 
                    type="danger" 
                    :icon="Delete"
                    @click="confirmDelete(row)"
                    size="small"
                    plain
                    circle />
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
            :page-sizes="[5, 10, 20, 100]"
            layout="total, sizes, prev, pager, next, jumper"
            :total="totalExpedientes"
            @size-change="loadExpedientes"
            @current-change="loadExpedientes"
            :prev-text="'Anterior'"
            :next-text="'Siguiente'"
            :page-count="Math.ceil(totalExpedientes / pageSize)" />
        </div>
      </el-card>
    </div>

    <!-- Modal Crear/Editar Expediente -->
    <el-dialog 
      v-model="dialogVisible" 
      :title="isEditing ? '✏️ Editar Expediente' : '📄 Nuevo Expediente'"
      width="650px"
      :close-on-click-modal="false"
      class="expediente-dialog"
      top="5vh">
      
      <el-form 
        ref="expedienteFormRef"
        :model="expedienteForm" 
        :rules="formRules"
        label-width="140px"
        label-position="left">
        
        <el-form-item label="N° Expediente" prop="record_number">
          <el-input 
            v-model="expedienteForm.record_number" 
            placeholder="Ej: REC-2025-001"
            :disabled="isEditing" />
        </el-form-item>

        <el-form-item label="Usuario Encargado" prop="user_id">
          <el-select 
            v-model="expedienteForm.user_id" 
            placeholder="Seleccione un usuario"
            style="width: 100%"
            filterable>
            <el-option 
              v-for="user in users"
              :key="user.id"
              :label="`${getUserFullName(user)} (${user.email})`"
              :value="user.id" />
          </el-select>
        </el-form-item>

        <el-form-item label="Perfil" prop="profile_id">
          <el-select 
            v-model="expedienteForm.profile_id" 
            placeholder="Seleccione un perfil"
            style="width: 100%">
            <el-option 
              v-for="profile in profiles"
              :key="profile.id"
              :label="profile.name"
              :value="profile.id" />
          </el-select>
        </el-form-item>

        <!-- Campo Estado solo visible al editar, al crear siempre es "creado" -->
        <el-form-item label="Estado Actual" v-if="isEditing">
          <el-tag 
            :type="getStatusType(expedienteForm.status)" 
            size="large">
            {{ getStatusLabel(expedienteForm.status) }}
          </el-tag>
          <p class="status-note">Use los botones de acción para cambiar el estado</p>
        </el-form-item>

        <el-form-item label="Observaciones" prop="observations">
          <el-input 
            v-model="expedienteForm.observations"
            type="textarea"
            :rows="3"
            placeholder="Observaciones sobre el expediente..." />
        </el-form-item>

        <el-form-item label="Notas" prop="notes">
          <el-input 
            v-model="expedienteForm.notes"
            type="textarea"
            :rows="2"
            placeholder="Notas adicionales..." />
        </el-form-item>
      </el-form>

      <template #footer>
        <div class="dialog-footer">
          <el-button @click="dialogVisible = false">Cancelar</el-button>
          <el-button 
            type="primary" 
            @click="saveExpediente"
            :loading="saving">
            {{ isEditing ? 'Actualizar' : 'Crear' }}
          </el-button>
        </div>
      </template>
    </el-dialog>

    <!-- Modal Ver Expediente -->
    <el-dialog 
      v-model="viewDialogVisible" 
      title="🔍 Detalles del Expediente"
      width="900px"
      class="view-dialog"
      top="3vh">
      
      <div v-if="selectedExpediente" class="expedient-details">
        <el-row :gutter="20">
          <el-col :span="12">
            <el-descriptions title="Información General" :column="1" border>
              <el-descriptions-item label="N° Expediente">
                <el-tag>{{ selectedExpediente.record_number }}</el-tag>
              </el-descriptions-item>
              <el-descriptions-item label="Usuario">
                {{ getUserFullName(selectedExpediente.user) }}
              </el-descriptions-item>
              <el-descriptions-item label="Email">
                {{ selectedExpediente.user?.email || 'N/A' }}
              </el-descriptions-item>
              <el-descriptions-item label="Perfil">
                {{ selectedExpediente.profile?.name }}
              </el-descriptions-item>
              <el-descriptions-item label="Estado">
                <el-tag :type="getStatusType(selectedExpediente.status)">
                  {{ getStatusLabel(selectedExpediente.status) }}
                </el-tag>
              </el-descriptions-item>
              <el-descriptions-item label="Fecha Creación">
                {{ formatDate(selectedExpediente.created_at) }}
              </el-descriptions-item>
            </el-descriptions>
          </el-col>
          <el-col :span="12">
            <div class="documents-section">
              <h4>Documentos ({{ selectedExpediente.documents?.length || 0 }})</h4>
              <el-empty v-if="!selectedExpediente.documents?.length" 
                description="No hay documentos subidos" />
              <div v-else class="documents-list">
                <div 
                  v-for="doc in selectedExpediente.documents" 
                  :key="doc.id"
                  class="document-item">
                  <el-icon><Document /></el-icon>
                  <span>{{ doc.name || 'Documento sin nombre' }}</span>
                  <el-button size="small" type="primary" link>Descargar</el-button>
                </div>
              </div>
            </div>
          </el-col>
        </el-row>

        <el-divider />

        <div class="notes-section">
          <h4>Observaciones</h4>
          <p>{{ selectedExpediente.observations || 'Sin observaciones' }}</p>
          
          <h4>Notas</h4>
          <p>{{ selectedExpediente.notes || 'Sin notas adicionales' }}</p>
        </div>
      </div>

      <template #footer>
        <div class="dialog-footer">
          <el-button @click="viewDialogVisible = false">Cerrar</el-button>
          <el-button 
            type="primary" 
            :icon="Edit"
            @click="openEditDialog(selectedExpediente)">
            Editar Expediente
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
  Document, 
  Plus, 
  Search, 
  Refresh, 
  View, 
  Edit, 
  Delete 
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
const expedientes = ref([])
const users = ref([])
const profiles = ref([])
const selectedExpediente = ref(null)

// Paginación
const currentPage = ref(1)
const pageSize = ref(20)
const totalExpedientes = computed(() => expedientes.value.length)

// Textos de paginación en español
const paginationTexts = {
  total: `Total {total}`,
  goto: 'Ir a',
  pageClassifier: '',
  page: 'página',
  prev: 'Anterior',
  next: 'Siguiente',
  currentPage: 'página {pager}',
  prevPages: 'Páginas anteriores {pager}',
  nextPages: 'Siguientes {pager} páginas'
}

// Filtros
const filters = reactive({
  status: '',
  profile_id: '',
  search: ''
})

// Formulario
const expedienteForm = reactive({
  record_number: '',
  user_id: '',
  profile_id: '',
  status: 'created',
  observations: '',
  notes: ''
})

// Reglas de validación
const formRules = {
  record_number: [
    { required: true, message: 'El número de expediente es obligatorio', trigger: 'blur' },
    { min: 3, max: 50, message: 'Debe tener entre 3 y 50 caracteres', trigger: 'blur' }
  ],
  user_id: [
    { required: true, message: 'Debe seleccionar un usuario', trigger: 'change' }
  ],
  profile_id: [
    { required: true, message: 'Debe seleccionar un perfil', trigger: 'change' }
  ]
}

const expedienteFormRef = ref()

// Funciones utilitarias
const getUserInitials = (user) => {
  if (!user || (!user.name && !user.first_name)) return 'N/A'
  
  // Si tiene campo 'name' lo usa, sino construye desde first_name y last_name
  const fullName = user.name || `${user.first_name || ''} ${user.last_name || ''}`.trim()
  
  if (!fullName) return 'N/A'
  return fullName.split(' ').map(word => word.charAt(0)).join('').toUpperCase()
}

const getUserFullName = (user) => {
  if (!user) return 'N/A'
  
  // Si tiene campo 'name' lo usa, sino construye desde first_name y last_name
  return user.name || `${user.first_name || ''} ${user.last_name || ''}`.trim() || 'N/A'
}

const getStatusType = (status) => {
  const types = {
    active: 'success',
    inactive: 'info',
    completed: 'warning',
    cancelled: 'danger',
    created: 'info',
    under_review: 'warning',
    approved: 'success',
    rejected: 'danger'
  }
  return types[status] || 'info'
}

const getStatusLabel = (status) => {
  const labels = {
    active: 'Activo',
    inactive: 'Inactivo',
    completed: 'Completado',
    cancelled: 'Cancelado',
    created: 'Creado',
    under_review: 'En Revisión',
    approved: 'Aprobado',
    rejected: 'Rechazado'
  }
  return labels[status] || status
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return dayjs(dateString).format('DD/MM/YYYY HH:mm')
}

// Búsqueda con debounce
let searchTimeout
const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    loadExpedientes()
  }, 500)
}

// Funciones principales
const loadExpedientes = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/expedientes')
    
    if (response.data.success) {
      let data = response.data.data
      
      // Debug: verificar estructura de datos
      console.log('Expedientes data:', data)
      if (data.length > 0) {
        console.log('Primer usuario:', data[0].user)
      }
      
      // Aplicar filtros
      if (filters.status) {
        data = data.filter(exp => exp.status === filters.status)
      }
      
      if (filters.profile_id) {
        data = data.filter(exp => exp.profile_id == filters.profile_id)
      }
      
      if (filters.search) {
        const search = filters.search.toLowerCase()
        data = data.filter(exp => {
          const fullName = getUserFullName(exp.user).toLowerCase()
          return exp.record_number.toLowerCase().includes(search) ||
                 fullName.includes(search) ||
                 exp.user?.email?.toLowerCase().includes(search)
        })
      }
      
      expedientes.value = data
    }
  } catch (error) {
    console.error('Error loading expedientes:', error)
    ElMessage.error('Error al cargar expedientes')
  } finally {
    loading.value = false
  }
}

const loadUsers = async () => {
  try {
    const response = await axios.get('/api/usuarios')
    console.log('Users response:', response.data)
    
    // Manejar tanto formato con success como directo
    if (response.data.success) {
      users.value = response.data.data
    } else if (Array.isArray(response.data)) {
      users.value = response.data
    }
    
    console.log('Users loaded:', users.value.length)
  } catch (error) {
    console.error('Error loading users:', error)
  }
}

const loadProfiles = async () => {
  try {
    const response = await axios.get('/api/perfiles')
    if (response.data.success) {
      profiles.value = response.data.data
    }
  } catch (error) {
    console.error('Error loading profiles:', error)
  }
}

// CRUD Operations
const openCreateDialog = () => {
  isEditing.value = false
  resetForm()
  dialogVisible.value = true
}

const openEditDialog = (expediente) => {
  isEditing.value = true
  Object.assign(expedienteForm, {
    id: expediente.id,
    record_number: expediente.record_number,
    user_id: expediente.user_id,
    profile_id: expediente.profile_id,
    status: expediente.status,
    observations: expediente.observations || '',
    notes: expediente.notes || ''
  })
  viewDialogVisible.value = false
  dialogVisible.value = true
}

const saveExpediente = async () => {
  if (!expedienteFormRef.value) return
  
  const valid = await expedienteFormRef.value.validate().catch(() => false)
  if (!valid) return

  saving.value = true
  try {
    let response
    
    if (isEditing.value) {
      response = await axios.put(`/api/expedientes/${expedienteForm.id}`, expedienteForm)
    } else {
      response = await axios.post('/api/expedientes', expedienteForm)
    }

    if (response.data.success) {
      ElMessage.success(
        isEditing.value ? 'Expediente actualizado correctamente' : 'Expediente creado correctamente'
      )
      dialogVisible.value = false
      loadExpedientes()
    }
  } catch (error) {
    console.error('Error saving expediente:', error)
    ElMessage.error('Error al guardar expediente')
  } finally {
    saving.value = false
  }
}

const viewExpediente = async (expediente) => {
  try {
    const response = await axios.get(`/api/expedientes/${expediente.id}`)
    if (response.data.success) {
      selectedExpediente.value = response.data.data
      viewDialogVisible.value = true
    }
  } catch (error) {
    console.error('Error loading expediente details:', error)
    ElMessage.error('Error al cargar detalles del expediente')
  }
}

// Función para cambiar el estado de un expediente
const changeStatus = (expediente, newStatus) => {
  const statusLabels = {
    'under_review': 'En Revisión',
    'approved': 'Aprobado',
    'rejected': 'Rechazado',
    'completed': 'Completado'
  }
  
  ElMessageBox.confirm(
    `¿Confirma cambiar el estado del expediente ${expediente.record_number} a "${statusLabels[newStatus]}"?`,
    '🔄 Cambiar Estado',
    {
      confirmButtonText: 'Sí, Cambiar',
      cancelButtonText: 'Cancelar',
      type: 'info'
    }
  ).then(async () => {
    try {
      const response = await axios.put(`/api/expedientes/${expediente.id}`, {
        ...expediente,
        status: newStatus
      })
      
      if (response.data.success) {
        ElMessage.success(`Estado cambiado a ${statusLabels[newStatus]}`)
        loadExpedientes() // Recargar la lista
      } else {
        ElMessage.error('Error al cambiar el estado')
      }
    } catch (error) {
      console.error('Error changing status:', error)
      ElMessage.error('Error al cambiar el estado')
    }
  }).catch(() => {
    // Usuario canceló el cambio
  })
}

const confirmDelete = (expediente) => {
  ElMessageBox.confirm(
    `¿Está seguro de eliminar el expediente ${expediente.record_number}? Esta acción no se puede deshacer.`,
    '⚠️ Confirmar Eliminación',
    {
      confirmButtonText: 'Sí, Eliminar',
      cancelButtonText: 'Cancelar',
      type: 'warning',
      dangerouslyUseHTMLString: false
    }
  ).then(() => {
    deleteExpediente(expediente.id)
  }).catch(() => {
    // Usuario canceló la eliminación
  })
}

const deleteExpediente = async (id) => {
  try {
    const response = await axios.delete(`/api/expedientes/${id}`)
    if (response.data.success) {
      ElMessage.success('Expediente eliminado correctamente')
      loadExpedientes()
    }
  } catch (error) {
    console.error('Error deleting expediente:', error)
    ElMessage.error('Error al eliminar expediente')
  }
}

const resetForm = () => {
  Object.assign(expedienteForm, {
    record_number: '',
    user_id: '',
    profile_id: '',
    status: 'created',
    observations: '',
    notes: ''
  })
}

const clearFilters = () => {
  Object.assign(filters, {
    status: '',
    profile_id: '',
    search: ''
  })
  loadExpedientes()
}

// Montaje del componente
onMounted(() => {
  loadExpedientes()
  loadUsers()
  loadProfiles()
})
</script>

<style scoped>
.expedientes-container {
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
  color: #3b82f6;
  filter: drop-shadow(0 2px 4px rgba(59,130,246,0.3));
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

.stats-card {
  border: none;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
  border-radius: 12px;
  overflow: hidden;
}

.stats-card :deep(.el-card__body) {
  padding: 24px;
}

.stat-item {
  text-align: center;
}

.stat-number {
  display: block;
  font-size: 28px;
  font-weight: 800;
  margin-bottom: 6px;
  text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.stat-label {
  font-size: 13px;
  opacity: 0.95;
  font-weight: 500;
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

.user-info {
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 0;
}

.user-details {
  flex: 1;
  min-width: 0;
  overflow: hidden;
}

.user-name {
  font-weight: 600;
  color: #1f2937;
  font-size: 13px;
  margin-bottom: 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 1.2;
}

.user-email {
  font-size: 11px;
  color: #6b7280;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 1.2;
}

.user-avatar {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: white;
  font-weight: 700;
  font-size: 12px;
  box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
}

.documents-column {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 40px;
  padding: 4px;
}

.documents-badge {
  cursor: pointer;
  transition: transform 0.2s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.documents-badge:hover {
  transform: scale(1.1);
}

.documents-badge :deep(.el-badge__content) {
  background: linear-gradient(135deg, #10b981, #059669);
  border: none;
  box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);
  font-size: 11px;
  font-weight: 600;
}

.documents-badge :deep(.el-icon) {
  display: flex;
  align-items: center;
  justify-content: center;
  color: #4b5563;
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

.expedient-details {
  max-height: 65vh;
  overflow-y: auto;
  padding-right: 8px;
}

.expedient-details::-webkit-scrollbar {
  width: 6px;
}

.expedient-details::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.expedient-details::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.expedient-details::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

.documents-section h4,
.notes-section h4 {
  color: #1f2937;
  margin-bottom: 16px;
  font-weight: 700;
  font-size: 16px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.documents-section h4::before {
  content: "📄";
  font-size: 18px;
}

.notes-section h4::before {
  content: "📝";
  font-size: 18px;
}

.documents-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.document-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: #fafbfc;
  transition: all 0.2s ease;
}

.document-item:hover {
  background: #f0f9ff;
  border-color: #3b82f6;
  transform: translateX(4px);
}

.document-item span {
  flex: 1;
  font-size: 14px;
  font-weight: 500;
  color: #374151;
}

.notes-section p {
  background: #f8fafc;
  padding: 16px;
  border-radius: 8px;
  margin: 12px 0;
  color: #4b5563;
  line-height: 1.6;
  border-left: 4px solid #3b82f6;
  font-size: 14px;
}

/* Mejorar tags de estado */
.table-section :deep(.el-tag) {
  font-weight: 600;
  border: none;
  padding: 4px 12px;
  border-radius: 20px;
}

.table-section :deep(.el-tag.el-tag--success) {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
}

.table-section :deep(.el-tag.el-tag--warning) {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: white;
}

.table-section :deep(.el-tag.el-tag--danger) {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
}

.table-section :deep(.el-tag.el-tag--info) {
  background: linear-gradient(135deg, #6b7280, #4b5563);
  color: white;
}

/* Mejorar botones */
.table-section :deep(.el-button-group .el-button) {
  margin: 0;
  border-radius: 6px;
  font-size: 12px;
  padding: 6px 12px;
  font-weight: 500;
  transition: all 0.2s ease;
}

.table-section :deep(.el-button-group .el-button:first-child) {
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
}

.table-section :deep(.el-button-group .el-button:last-child) {
  border-top-left-radius: 0;
  border-bottom-left-radius: 0;
}

.table-section :deep(.el-button.is-plain:hover) {
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Mejorar botón crear */
.create-button {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8) !important;
  border: none !important;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4) !important;
  border-radius: 10px !important;
  padding: 12px 24px !important;
  font-size: 15px !important;
  transition: all 0.3s ease !important;
}

.create-button:hover {
  transform: translateY(-2px) !important;
  box-shadow: 0 6px 16px rgba(59, 130, 246, 0.5) !important;
}

.clear-filters-btn {
  border-radius: 8px !important;
  font-weight: 500 !important;
  transition: all 0.2s ease !important;
}

.clear-filters-btn:hover {
  background: #f3f4f6 !important;
  border-color: #9ca3af !important;
}

/* Mejorar select y inputs en filtros */
.filter-card :deep(.el-select),
.filter-card :deep(.el-input) {
  font-size: 14px;
}

.filter-card :deep(.el-select .el-input__inner),
.filter-card :deep(.el-input__inner) {
  border-radius: 8px;
  border: 1px solid #d1d5db;
  font-weight: 500;
  transition: all 0.2s ease;
}

.filter-card :deep(.el-select .el-input__inner:focus),
.filter-card :deep(.el-input__inner:focus) {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.filter-card :deep(.el-select-dropdown) {
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

/* Mejorar diálogos */
.expediente-dialog :deep(.el-dialog),
.view-dialog :deep(.el-dialog) {
  border-radius: 16px;
  box-shadow: 0 25px 50px rgba(0,0,0,0.15);
  background: white;
}

.expediente-dialog :deep(.el-dialog__header),
.view-dialog :deep(.el-dialog__header) {
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
  border-radius: 16px 16px 0 0;
  padding: 20px 24px;
  border-bottom: 1px solid #e5e7eb;
}

.expediente-dialog :deep(.el-dialog__title),
.view-dialog :deep(.el-dialog__title) {
  font-size: 20px;
  font-weight: 700;
  color: #1f2937;
}

.expediente-dialog :deep(.el-dialog__body),
.view-dialog :deep(.el-dialog__body) {
  padding: 24px;
  background: white;
}

.expediente-dialog :deep(.el-form-item__label) {
  font-weight: 600;
  color: #374151;
}

.expediente-dialog :deep(.el-input__inner),
.expediente-dialog :deep(.el-textarea__inner) {
  border-radius: 8px;
  border: 1px solid #d1d5db;
  transition: all 0.2s ease;
}

.expediente-dialog :deep(.el-input__inner:focus),
.expediente-dialog :deep(.el-textarea__inner:focus) {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.expediente-dialog :deep(.el-select .el-input__inner) {
  border-radius: 8px;
}

/* Mejorar descriptions */
.view-dialog :deep(.el-descriptions__title) {
  font-size: 18px;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 16px;
}

.view-dialog :deep(.el-descriptions-item__label) {
  font-weight: 600;
  color: #4b5563;
}

.view-dialog :deep(.el-descriptions-item__content) {
  color: #1f2937;
}

.view-dialog :deep(.el-descriptions) {
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

/* Mejorar botones de acción */
.action-buttons {
  display: flex;
  gap: 4px;
  justify-content: center;
  align-items: center;
  flex-wrap: wrap;
}

.action-buttons .el-button {
  transition: all 0.2s ease !important;
}

/* Botones circulares para ver/editar/eliminar */
.action-buttons .el-button[circle] {
  width: 32px !important;
  height: 32px !important;
  padding: 0 !important;
  border-radius: 50% !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
}

/* Botones rectangulares para cambio de estado */
.action-buttons .el-button:not([circle]) {
  padding: 6px 12px !important;
  font-size: 12px !important;
  border-radius: 6px !important;
  font-weight: 500 !important;
}

/* Nota del estado en formulario */
.status-note {
  font-size: 12px;
  color: #666;
  margin-top: 4px;
  font-style: italic;
}

.action-buttons .el-button:hover {
  transform: translateY(-1px) scale(1.1) !important;
  box-shadow: 0 4px 8px rgba(0,0,0,0.15) !important;
}

/* Mejorar tags específicos */
.record-number-tag {
  background: linear-gradient(135deg, #f0f9ff, #e0f2fe) !important;
  border: 1px solid #0ea5e9 !important;
  color: #0c4a6e !important;
  font-weight: 600 !important;
  font-family: 'Courier New', monospace !important;
  font-size: 11px !important;
  white-space: nowrap !important;
}

.profile-tag {
  background: linear-gradient(135deg, #fef3c7, #fde68a) !important;
  border: 1px solid #f59e0b !important;
  color: #92400e !important;
  font-weight: 500 !important;
  white-space: nowrap !important;
  max-width: 100% !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
}

/* Responsive */
@media (max-width: 1024px) {
  .expedientes-container {
    padding: 16px;
  }
  
  .page-title {
    font-size: 28px;
  }
  
  .header-section .el-col:first-child {
    margin-bottom: 16px;
  }
  
  .filters-section .el-col {
    margin-bottom: 12px;
  }
}

@media (max-width: 768px) {
  .expedientes-container {
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
  
  .user-info {
    flex-direction: column;
    text-align: center;
    gap: 8px;
  }
}
</style>
