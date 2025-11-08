<template>
  <div class="incidencias-container">
    <!-- Header con titulo y botón crear -->
    <div class="header-section">
      <el-row :gutter="20" align="middle">
        <el-col :span="16">
          <h1 class="page-title">
            <el-icon class="title-icon"><Warning /></el-icon>
            Gestión de Incidencias
          </h1>
        </el-col>
        <el-col :span="8" class="text-right">
          <el-button 
            type="primary" 
            @click="openCreateDialog"
            :icon="Plus"
            size="large"
            class="create-button">
            <strong>Nueva Incidencia</strong>
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
                  placeholder="Buscar por título o descripción..."
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
                  <el-option label="Abierta" value="open" />
                  <el-option label="En Progreso" value="in_progress" />
                  <el-option label="Resuelta" value="resolved" />
                  <el-option label="Cerrada" value="closed" />
                </el-select>
              </el-col>
              <el-col :span="4">
                <el-select 
                  v-model="filters.priority" 
                  placeholder="Prioridad"
                  @change="applyFilters"
                  clearable>
                  <el-option label="Todas" value="" />
                  <el-option label="Baja" value="low" />
                  <el-option label="Media" value="medium" />
                  <el-option label="Alta" value="high" />
                  <el-option label="Crítica" value="critical" />
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
              <el-col :span="3">
                <div class="stats-display">
                  <el-tag type="info" size="large">
                    Total: {{ totalIncidencias }}
                  </el-tag>
                </div>
              </el-col>
              <el-col :span="3">
                <div class="overdue-section">
                  <el-button 
                    type="danger" 
                    :icon="Clock"
                    @click="showOverdue"
                    size="default"
                    class="overdue-btn">
                    Vencidas
                  </el-button>
                </div>
              </el-col>
            </el-row>
          </el-card>
        </el-col>
      </el-row>
    </div>

    <!-- Tabla de incidencias -->
    <div class="table-section">
      <el-card shadow="never">
        <!-- Estado vacío cuando no hay incidencias -->
        <div v-if="!loading && incidencias.length === 0" class="empty-state">
          <el-empty description="No hay incidencias reportadas">
            <div class="empty-actions">
              <el-button 
                type="primary" 
                size="large"
                :icon="Plus"
                @click="openCreateDialog"
                class="empty-create-btn">
                Reportar Primera Incidencia
              </el-button>
              <el-button 
                type="success" 
                size="large"
                :icon="User"
                @click="$router.push('/usuarios')"
                class="empty-usuarios-btn">
                Gestionar Usuarios
              </el-button>
            </div>
            <div class="empty-help">
              <p>Las incidencias permiten reportar y gestionar problemas del sistema.</p>
              <p>Puedes asignar incidencias a usuarios y hacer seguimiento de su resolución.</p>
            </div>
          </el-empty>
        </div>

        <!-- Tabla cuando hay datos -->
        <el-table 
          v-else
          :data="incidencias" 
          v-loading="loading"
          style="width: 100%"
          :default-sort="{ prop: 'created_at', order: 'descending' }"
          :table-layout="'auto'">

          <el-table-column prop="title" label="Título" min-width="200" sortable>
            <template #default="{ row }">
              <div class="incident-title">
                <el-icon class="incident-icon"><Warning /></el-icon>
                <strong>{{ row.title }}</strong>
              </div>
            </template>
          </el-table-column>

          <el-table-column prop="priority" label="Prioridad" width="120" sortable align="center">
            <template #default="{ row }">
              <el-tag 
                :type="getPriorityTagType(row.priority)"
                :effect="row.priority === 'critical' ? 'dark' : 'light'"
                size="small">
                {{ formatPriority(row.priority) }}
              </el-tag>
            </template>
          </el-table-column>

          <el-table-column prop="status" label="Estado" width="120" sortable align="center">
            <template #default="{ row }">
              <el-tag 
                :type="getStatusTagType(row.status)"
                size="small"
                effect="light">
                {{ formatStatus(row.status) }}
              </el-tag>
            </template>
          </el-table-column>

          <el-table-column prop="user" label="Reportado por" width="150">
            <template #default="{ row }">
              <div class="user-info">
                <el-icon class="user-icon"><User /></el-icon>
                <span>{{ row.user ? `${row.user.first_name} ${row.user.last_name}` : 'N/A' }}</span>
              </div>
            </template>
          </el-table-column>

          <el-table-column prop="assigned_user" label="Asignado a" width="150">
            <template #default="{ row }">
              <div class="user-info" v-if="row.assigned_user">
                <el-icon class="user-icon"><UserFilled /></el-icon>
                <span>{{ `${row.assigned_user.first_name} ${row.assigned_user.last_name}` }}</span>
              </div>
              <span v-else class="no-assigned">Sin asignar</span>
            </template>
          </el-table-column>

          <el-table-column prop="due_date" label="Fecha Límite" width="120" sortable>
            <template #default="{ row }">
              <span v-if="row.due_date" :class="getDueDateClass(row.due_date, row.status)">
                {{ formatDate(row.due_date) }}
              </span>
              <span v-else class="no-date">Sin fecha</span>
            </template>
          </el-table-column>

          <el-table-column prop="created_at" label="Creación" width="100" sortable>
            <template #default="{ row }">
              {{ formatDateShort(row.created_at) }}
            </template>
          </el-table-column>

          <el-table-column label="Acciones" width="140" fixed="right">
            <template #default="{ row }">
              <div class="action-buttons">
                <el-tooltip content="Ver detalles" placement="top">
                  <el-button 
                    type="info" 
                    :icon="View"
                    @click="viewIncident(row)"
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
            :page-sizes="[10, 20, 50, 100]"
            layout="total, sizes, prev, pager, next, jumper"
            :total="totalIncidencias"
            @size-change="loadIncidencias"
            @current-change="loadIncidencias"
            :prev-text="'Anterior'"
            :next-text="'Siguiente'" />
        </div>
      </el-card>
    </div>

    <!-- Modal Crear/Editar Incidencia -->
    <el-dialog 
      v-model="dialogVisible" 
      :title="isEditing ? '✏️ Editar Incidencia' : '🚨 Nueva Incidencia'"
      width="800px"
      :close-on-click-modal="false"
      class="incident-dialog"
      top="3vh">
      
      <el-form 
        ref="incidentFormRef"
        :model="incidentForm" 
        :rules="formRules"
        label-width="140px"
        label-position="left">
        
        <el-form-item label="Título" prop="title">
          <el-input 
            v-model="incidentForm.title" 
            placeholder="Título descriptivo de la incidencia..." />
        </el-form-item>

        <el-form-item label="Descripción" prop="description">
          <el-input 
            v-model="incidentForm.description"
            type="textarea"
            :rows="4"
            placeholder="Descripción detallada del problema..." />
        </el-form-item>

        <el-row :gutter="16">
          <el-col :span="12">
            <el-form-item label="Prioridad" prop="priority">
              <el-select v-model="incidentForm.priority" placeholder="Selecciona prioridad" style="width: 100%">
                <el-option label="Baja" value="low" />
                <el-option label="Media" value="medium" />
                <el-option label="Alta" value="high" />
                <el-option label="Crítica" value="critical" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="Estado" prop="status">
              <el-select v-model="incidentForm.status" placeholder="Selecciona estado" style="width: 100%">
                <el-option label="Abierta" value="open" />
                <el-option label="En Progreso" value="in_progress" />
                <el-option label="Resuelta" value="resolved" />
                <el-option label="Cerrada" value="closed" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="16">
          <el-col :span="12">
            <el-form-item label="Usuario Reporta" prop="user_id">
              <el-select 
                v-model="incidentForm.user_id" 
                placeholder="Selecciona usuario"
                style="width: 100%"
                filterable>
                <el-option 
                  v-for="user in usuarios" 
                  :key="user.id"
                  :label="`${user.first_name} ${user.last_name}`"
                  :value="user.id" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="Asignar a" prop="assigned_user_id">
              <el-select 
                v-model="incidentForm.assigned_user_id" 
                placeholder="Asignar a usuario"
                style="width: 100%"
                filterable
                clearable>
                <el-option 
                  v-for="user in usuarios" 
                  :key="user.id"
                  :label="`${user.first_name} ${user.last_name}`"
                  :value="user.id" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="Fecha Límite" prop="due_date">
          <el-date-picker
            v-model="incidentForm.due_date"
            type="datetime"
            placeholder="Selecciona fecha límite"
            style="width: 100%"
            format="DD/MM/YYYY HH:mm"
            value-format="YYYY-MM-DD HH:mm:ss" />
        </el-form-item>

        <el-form-item 
          v-if="isEditing && (incidentForm.status === 'resolved' || incidentForm.status === 'closed')"
          label="Resolución" 
          prop="resolution">
          <el-input 
            v-model="incidentForm.resolution"
            type="textarea"
            :rows="3"
            placeholder="Descripción de la resolución..." />
        </el-form-item>
      </el-form>

      <template #footer>
        <div class="dialog-footer">
          <el-button @click="dialogVisible = false">Cancelar</el-button>
          <el-button 
            type="primary" 
            @click="saveIncident"
            :loading="saving">
            {{ isEditing ? 'Actualizar' : 'Crear' }}
          </el-button>
        </div>
      </template>
    </el-dialog>

    <!-- Modal Ver Detalles de Incidencia -->
    <el-dialog 
      v-model="viewDialogVisible" 
      title="🔍 Detalles de la Incidencia"
      width="900px"
      class="view-dialog">
      
      <div v-if="selectedIncident" class="incident-details">
        <el-descriptions title="Información General" :column="2" border>
          <el-descriptions-item label="Título">
            <strong>{{ selectedIncident.title }}</strong>
          </el-descriptions-item>
          <el-descriptions-item label="Estado">
            <el-tag 
              :type="getStatusTagType(selectedIncident.status)"
              size="small">
              {{ formatStatus(selectedIncident.status) }}
            </el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="Prioridad">
            <el-tag 
              :type="getPriorityTagType(selectedIncident.priority)"
              :effect="selectedIncident.priority === 'critical' ? 'dark' : 'light'"
              size="small">
              {{ formatPriority(selectedIncident.priority) }}
            </el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="Fecha Creación">
            {{ formatDate(selectedIncident.created_at) }}
          </el-descriptions-item>
          <el-descriptions-item label="Reportado por">
            {{ selectedIncident.user ? `${selectedIncident.user.first_name} ${selectedIncident.user.last_name}` : 'N/A' }}
          </el-descriptions-item>
          <el-descriptions-item label="Asignado a">
            {{ selectedIncident.assigned_user ? `${selectedIncident.assigned_user.first_name} ${selectedIncident.assigned_user.last_name}` : 'Sin asignar' }}
          </el-descriptions-item>
          <el-descriptions-item label="Fecha Límite">
            <span v-if="selectedIncident.due_date" :class="getDueDateClass(selectedIncident.due_date, selectedIncident.status)">
              {{ formatDate(selectedIncident.due_date) }}
            </span>
            <span v-else>Sin fecha límite</span>
          </el-descriptions-item>
          <el-descriptions-item label="Fecha Resolución" v-if="selectedIncident.resolved_at">
            {{ formatDate(selectedIncident.resolved_at) }}
          </el-descriptions-item>
        </el-descriptions>

        <el-divider />

        <div class="description-section">
          <h4>📝 Descripción del Problema</h4>
          <div class="description-content">
            {{ selectedIncident.description }}
          </div>
        </div>

        <div v-if="selectedIncident.resolution" class="resolution-section">
          <el-divider />
          <h4>✅ Resolución</h4>
          <div class="resolution-content">
            {{ selectedIncident.resolution }}
          </div>
        </div>
      </div>

      <template #footer>
        <div class="dialog-footer">
          <el-button @click="viewDialogVisible = false">Cerrar</el-button>
          <el-button 
            type="primary" 
            :icon="Edit"
            @click="openEditDialog(selectedIncident)">
            Editar Incidencia
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
  Warning,
  Plus, 
  Search, 
  Refresh, 
  View,
  Edit, 
  Delete,
  User,
  UserFilled,
  Clock
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
const incidencias = ref([])
const allIncidencias = ref([])
const usuarios = ref([])
const selectedIncident = ref(null)

// Paginación
const currentPage = ref(1)
const pageSize = ref(20)
const totalIncidencias = computed(() => incidencias.value.length)

// Filtros
const filters = reactive({
  search: '',
  status: '',
  priority: ''
})

// Formulario
const incidentForm = reactive({
  title: '',
  description: '',
  priority: 'medium',
  status: 'open',
  user_id: null,
  assigned_user_id: null,
  due_date: null,
  resolution: ''
})

// Reglas de validación
const formRules = {
  title: [
    { required: true, message: 'El título es obligatorio', trigger: 'blur' },
    { min: 5, max: 255, message: 'Debe tener entre 5 y 255 caracteres', trigger: 'blur' }
  ],
  description: [
    { required: true, message: 'La descripción es obligatoria', trigger: 'blur' },
    { min: 10, message: 'Mínimo 10 caracteres', trigger: 'blur' }
  ],
  priority: [
    { required: true, message: 'La prioridad es obligatoria', trigger: 'change' }
  ],
  status: [
    { required: true, message: 'El estado es obligatorio', trigger: 'change' }
  ],
  user_id: [
    { required: true, message: 'Debe seleccionar el usuario que reporta', trigger: 'change' }
  ]
}

const incidentFormRef = ref()

// Funciones utilitarias
const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return dayjs(dateString).format('DD/MM/YYYY HH:mm')
}

const formatDateShort = (dateString) => {
  if (!dateString) return 'N/A'
  return dayjs(dateString).format('DD/MM/YY')
}

const formatPriority = (priority) => {
  const priorities = {
    low: 'Baja',
    medium: 'Media',
    high: 'Alta',
    critical: 'Crítica'
  }
  return priorities[priority] || priority
}

const formatStatus = (status) => {
  const statuses = {
    open: 'Abierta',
    in_progress: 'En Progreso',
    resolved: 'Resuelta',
    closed: 'Cerrada'
  }
  return statuses[status] || status
}

const getPriorityTagType = (priority) => {
  const types = {
    low: 'info',
    medium: 'warning',
    high: 'danger',
    critical: 'danger'
  }
  return types[priority] || 'info'
}

const getStatusTagType = (status) => {
  const types = {
    open: 'warning',
    in_progress: 'primary',
    resolved: 'success',
    closed: 'info'
  }
  return types[status] || 'info'
}

const getDueDateClass = (dueDate, status) => {
  if (!dueDate || ['resolved', 'closed'].includes(status)) return ''
  
  const now = dayjs()
  const due = dayjs(dueDate)
  
  if (due.isBefore(now)) {
    return 'overdue-date'
  } else if (due.diff(now, 'hours') <= 24) {
    return 'warning-date'
  }
  return ''
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
const loadIncidencias = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/incidencias')
    
    if (response.data && response.data.success && response.data.data) {
      allIncidencias.value = response.data.data
      applyFilters()
      console.log('Incidencias cargadas:', allIncidencias.value.length)
    } else {
      console.warn('Respuesta inesperada del servidor:', response.data)
      allIncidencias.value = []
    }
  } catch (error) {
    console.error('Error loading incidencias:', error)
    ElMessage.error('Error al cargar incidencias')
  } finally {
    loading.value = false
  }
}

const loadUsuarios = async () => {
  try {
    const response = await axios.get('/api/usuarios')
    
    // La API de usuarios retorna directamente un array
    if (response.data && Array.isArray(response.data)) {
      usuarios.value = response.data
      console.log('Usuarios cargados:', usuarios.value.length)
    }
  } catch (error) {
    console.error('Error loading usuarios:', error)
    ElMessage.error('Error al cargar usuarios')
  }
}

const applyFilters = () => {
  let data = [...allIncidencias.value]
  
  // Filtro por búsqueda
  if (filters.search) {
    const search = filters.search.toLowerCase()
    data = data.filter(incident => 
      incident.title.toLowerCase().includes(search) ||
      (incident.description && incident.description.toLowerCase().includes(search))
    )
  }
  
  // Filtro por estado
  if (filters.status) {
    data = data.filter(incident => incident.status === filters.status)
  }
  
  // Filtro por prioridad
  if (filters.priority) {
    data = data.filter(incident => incident.priority === filters.priority)
  }
  
  incidencias.value = data
}

const showOverdue = async () => {
  try {
    const response = await axios.get('/api/incidencias/vencidas')
    
    if (response.data && response.data.success && response.data.data) {
      allIncidencias.value = response.data.data
      // Limpiar filtros y aplicar solo incidencias vencidas
      Object.assign(filters, { search: '', status: '', priority: '' })
      applyFilters()
      ElMessage.info(`Se encontraron ${response.data.data.length} incidencias vencidas`)
    } else {
      console.warn('Respuesta inesperada:', response.data)
      ElMessage.warning('No se encontraron incidencias vencidas')
    }
  } catch (error) {
    // Si el endpoint no existe, crear filtro manual
    if (error.response?.status === 404) {
      const now = dayjs()
      const overdueIncidents = allIncidencias.value.filter(incident => 
        incident.due_date && 
        dayjs(incident.due_date).isBefore(now) && 
        !['resolved', 'closed'].includes(incident.status)
      )
      
      allIncidencias.value = overdueIncidents
      Object.assign(filters, { search: '', status: '', priority: '' })
      applyFilters()
      ElMessage.info(`Se encontraron ${overdueIncidents.length} incidencias vencidas`)
    } else {
      console.error('Error loading overdue incidents:', error)
      ElMessage.error('Error al cargar incidencias vencidas')
    }
  }
}

// CRUD Operations
const openCreateDialog = () => {
  isEditing.value = false
  resetForm()
  dialogVisible.value = true
}

const openEditDialog = (incident) => {
  isEditing.value = true
  Object.assign(incidentForm, {
    id: incident.id,
    title: incident.title,
    description: incident.description || '',
    priority: incident.priority,
    status: incident.status,
    user_id: incident.user_id,
    assigned_user_id: incident.assigned_user_id,
    due_date: incident.due_date,
    resolution: incident.resolution || ''
  })
  viewDialogVisible.value = false
  dialogVisible.value = true
}

const saveIncident = async () => {
  if (!incidentFormRef.value) return
  
  const valid = await incidentFormRef.value.validate().catch(() => false)
  if (!valid) return

  saving.value = true
  try {
    let response
    
    if (isEditing.value) {
      response = await axios.put(`/api/incidencias/${incidentForm.id}`, incidentForm)
    } else {
      response = await axios.post('/api/incidencias', incidentForm)
    }

    if (response.data && response.data.success) {
      ElMessage.success(
        isEditing.value ? 'Incidencia actualizada correctamente' : 'Incidencia creada correctamente'
      )
      dialogVisible.value = false
      loadIncidencias()
    } else {
      console.warn('Respuesta inesperada:', response.data)
      ElMessage.error('Respuesta inesperada del servidor')
    }
  } catch (error) {
    console.error('Error saving incident:', error)
    if (error.response?.data?.errors) {
      const errorMessages = Object.values(error.response.data.errors).flat()
      ElMessage.error(errorMessages.join(', '))
    } else {
      ElMessage.error('Error al guardar incidencia')
    }
  } finally {
    saving.value = false
  }
}

const viewIncident = async (incident) => {
  try {
    const response = await axios.get(`/api/incidencias/${incident.id}`)
    if (response.data && response.data.success && response.data.data) {
      selectedIncident.value = response.data.data
      viewDialogVisible.value = true
    } else {
      console.warn('Respuesta inesperada:', response.data)
      ElMessage.error('No se pudieron cargar los detalles')
    }
  } catch (error) {
    console.error('Error loading incident details:', error)
    ElMessage.error('Error al cargar detalles de la incidencia')
  }
}

const confirmDelete = (incident) => {
  ElMessageBox.confirm(
    `¿Está seguro de eliminar la incidencia "${incident.title}"? Esta acción no se puede deshacer.`,
    '⚠️ Confirmar Eliminación',
    {
      confirmButtonText: 'Sí, Eliminar',
      cancelButtonText: 'Cancelar',
      type: 'warning'
    }
  ).then(() => {
    deleteIncident(incident.id)
  }).catch(() => {
    // Usuario canceló
  })
}

const deleteIncident = async (id) => {
  try {
    const response = await axios.delete(`/api/incidencias/${id}`)
    if (response.data && response.data.success) {
      ElMessage.success('Incidencia eliminada correctamente')
      loadIncidencias()
    } else {
      console.warn('Respuesta inesperada:', response.data)
      ElMessage.error('No se pudo eliminar la incidencia')
    }
  } catch (error) {
    console.error('Error deleting incident:', error)
    ElMessage.error('Error al eliminar incidencia')
  }
}

const resetForm = () => {
  Object.assign(incidentForm, {
    title: '',
    description: '',
    priority: 'medium',
    status: 'open',
    user_id: null,
    assigned_user_id: null,
    due_date: null,
    resolution: ''
  })
}

const clearFilters = () => {
  Object.assign(filters, {
    search: '',
    status: '',
    priority: ''
  })
  // Recargar todas las incidencias sin filtros
  loadIncidencias()
}

// Montaje del componente
onMounted(async () => {
  // Cargar usuarios primero para que estén disponibles al mostrar incidencias
  await loadUsuarios()
  await loadIncidencias()
})
</script>

<style scoped>
.incidencias-container {
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
  color: #ef4444;
  filter: drop-shadow(0 2px 4px rgba(239, 68, 68, 0.3));
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
  justify-content: center;
}

.overdue-section {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  height: 100%;
}

.overdue-btn {
  background: linear-gradient(135deg, #ef4444, #dc2626) !important;
  color: white !important;
  border: none !important;
  box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3) !important;
  border-radius: 8px !important;
  font-weight: 600 !important;
  padding: 8px 16px !important;
  transition: all 0.2s ease !important;
  min-width: 100px !important;
  font-size: 13px !important;
}

.overdue-btn:hover {
  transform: translateY(-1px) !important;
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4) !important;
  background: linear-gradient(135deg, #dc2626, #b91c1c) !important;
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

.incident-title {
  display: flex;
  align-items: center;
  gap: 8px;
}

.incident-icon {
  color: #ef4444;
  font-size: 16px;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
}

.user-icon {
  color: #6b7280;
  font-size: 14px;
}

.no-assigned,
.no-date {
  color: #9ca3af;
  font-style: italic;
  font-size: 12px;
}

.overdue-date {
  color: #ef4444;
  font-weight: 600;
}

.warning-date {
  color: #f59e0b;
  font-weight: 600;
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
  background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
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
  background: linear-gradient(135deg, #ef4444, #dc2626) !important;
  border: none !important;
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4) !important;
  border-radius: 12px !important;
  padding: 16px 32px !important;
  font-size: 16px !important;
  font-weight: 600 !important;
  min-width: 200px !important;
  transition: all 0.3s ease !important;
}

.empty-create-btn:hover {
  transform: translateY(-2px) !important;
  box-shadow: 0 6px 16px rgba(239, 68, 68, 0.5) !important;
}

.empty-usuarios-btn {
  background: linear-gradient(135deg, #6366f1, #4f46e5) !important;
  color: white !important;
  border: none !important;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4) !important;
  border-radius: 12px !important;
  padding: 14px 28px !important;
  font-size: 15px !important;
  font-weight: 600 !important;
  min-width: 200px !important;
  transition: all 0.3s ease !important;
}

.empty-usuarios-btn:hover {
  transform: translateY(-2px) !important;
  box-shadow: 0 6px 16px rgba(99, 102, 241, 0.5) !important;
}

.empty-help {
  background: rgba(255, 255, 255, 0.8);
  border-radius: 12px;
  padding: 24px;
  border: 1px solid rgba(239, 68, 68, 0.2);
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
  color: #dc2626;
}

/* Mejorar botón crear */
.create-button {
  background: linear-gradient(135deg, #ef4444, #dc2626) !important;
  border: none !important;
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4) !important;
  border-radius: 10px !important;
  padding: 12px 24px !important;
  font-size: 15px !important;
  transition: all 0.3s ease !important;
}

.create-button:hover {
  transform: translateY(-2px) !important;
  box-shadow: 0 6px 16px rgba(239, 68, 68, 0.5) !important;
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
.incident-dialog :deep(.el-dialog),
.view-dialog :deep(.el-dialog) {
  border-radius: 16px;
  box-shadow: 0 25px 50px rgba(0,0,0,0.15);
  background: white;
}

.incident-dialog :deep(.el-dialog__header),
.view-dialog :deep(.el-dialog__header) {
  background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
  border-radius: 16px 16px 0 0;
  padding: 20px 24px;
  border-bottom: 1px solid #e5e7eb;
}

.incident-dialog :deep(.el-dialog__title),
.view-dialog :deep(.el-dialog__title) {
  font-size: 20px;
  font-weight: 700;
  color: #1f2937;
}

.incident-dialog :deep(.el-dialog__body),
.view-dialog :deep(.el-dialog__body) {
  padding: 24px;
  background: white;
}

.incident-details {
  max-height: 70vh;
  overflow-y: auto;
}

.description-section,
.resolution-section {
  margin-top: 20px;
}

.description-section h4,
.resolution-section h4 {
  color: #1f2937;
  margin-bottom: 12px;
  font-weight: 700;
  font-size: 16px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.description-content,
.resolution-content {
  background: #f8fafc;
  padding: 16px;
  border-radius: 8px;
  border-left: 4px solid #ef4444;
  color: #374151;
  line-height: 1.6;
  white-space: pre-wrap;
}

.resolution-content {
  border-left-color: #10b981;
}

/* Responsive */
@media (max-width: 1024px) {
  .incidencias-container {
    padding: 16px;
  }
  
  .page-title {
    font-size: 28px;
  }
  
  .empty-actions {
    flex-direction: column;
  }
  
  .empty-create-btn,
  .empty-usuarios-btn {
    min-width: 250px !important;
  }
  
  .filters-section .el-row {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
  }
}

@media (max-width: 768px) {
  .incidencias-container {
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
  .empty-usuarios-btn {
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
  
  .clear-filters-btn,
  .overdue-btn {
    width: 100% !important;
    min-width: 100% !important;
    font-size: 14px !important;
    padding: 12px 16px !important;
  }
  
  .stats-display,
  .overdue-section {
    justify-content: center;
  }
  
  .stats-display .el-tag {
    font-size: 14px !important;
    padding: 8px 16px !important;
  }
}
</style>