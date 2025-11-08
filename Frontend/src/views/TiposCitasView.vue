<template>
    <div class="tipos-citas-container">
        <!-- Header con titulo y botón crear -->
        <div class="header-section">
            <el-row :gutter="20" align="middle">
                <el-col :span="16">
                    <h1 class="page-title">
                        <el-icon class="title-icon">
                            <Calendar />
                        </el-icon>
                        Tipos de Citas
                    </h1>
                </el-col>
                <el-col :span="8" class="text-right">
                    <el-button type="primary" @click="openCreateDialog" :icon="Plus" size="large" class="create-button">
                        <strong>Nuevo Tipo</strong>
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
                            <el-col :span="10">
                                <el-input v-model="filters.search" placeholder="Buscar por nombre o descripción..."
                                    :prefix-icon="Search" @input="debounceSearch" clearable />
                            </el-col>
                            <el-col :span="4">
                                <el-select v-model="filters.status" placeholder="Estado" @change="applyFilters"
                                    clearable style="width: 150%;">
                                    <el-option label="Todos" value="" />
                                    <el-option label="Activos" value="active" />
                                    <el-option label="Inactivos" value="inactive" />
                                </el-select>
                            </el-col>
                            <el-col :span="6" :offset="4">
                                <el-button @click="clearFilters" :icon="Refresh" type="default"
                                    class="clear-filters-btn" style="width: 100%;">
                                    Limpiar Filtros
                                </el-button>
                            </el-col>
                            <!--<el-col :span="3">
                                <div class="stats-display">
                                    <el-tag type="info" size="large">
                                        Total: {{ totalTipos }}
                                    </el-tag>
                                </div>
                            </el-col>
                            <el-col :span="3">
                                <div class="manage-citas-section">
                                    <el-button type="success" :icon="Calendar" @click="$router.push('/dashboard')"
                                        size="default" class="manage-citas-btn">
                                        Ver Citas
                                    </el-button>
                                </div>
                            </el-col>-->
                        </el-row>
                    </el-card>
                </el-col>
            </el-row>
        </div>

        <!-- Tabla de tipos de citas -->
        <div class="table-section">
            <el-card shadow="never">
                <!-- Estado vacío cuando no hay tipos -->
                <div v-if="!loading && tiposCitas.length === 0" class="empty-state">
                    <el-empty description="No hay tipos de citas creados">
                        <div class="empty-actions">
                            <!-- <el-button type="primary" size="large" :icon="Plus" @click="openCreateDialog"
                                class="empty-create-btn">
                                Crear Primer Tipo de Cita
                            </el-button>-->
                            <!--<el-button type="success" size="large" :icon="Calendar" @click="$router.push('/citas')"
                                class="empty-citas-btn">
                                Ver Citas Existentes
                            </el-button>-->
                            <!--<el-button type="info" size="large" :icon="User" @click="$router.push('/usuarios')"
                                class="empty-usuarios-btn">
                                Gestionar Usuarios
                            </el-button>-->
                        </div>
                        <!--<div class="empty-help">
                            <p>Los tipos de citas definen las diferentes categorías de citas que pueden programarse.</p>
                            <p>Ejemplos: Consulta General, Emergencia, Revisión, Seguimiento, etc.</p>
                        </div>-->
                    </el-empty>
                </div>

                <!-- Tabla cuando hay datos -->
                <el-table v-else :data="tiposCitas" v-loading="loading" style="width: 100%"
                    :default-sort="{ prop: 'name', order: 'ascending' }" :table-layout="'auto'">

                    <el-table-column prop="name" label="Nombre del Tipo" min-width="200" sortable>
                        <template #default="{ row }">
                            <div class="tipo-name">
                                <el-icon class="tipo-icon">
                                    <Calendar />
                                </el-icon>
                                <strong>{{ row.name }}</strong>
                            </div>
                        </template>
                    </el-table-column>

                    <el-table-column prop="description" label="Descripción" min-width="250">
                        <template #default="{ row }">
                            <span class="description-text">{{ row.description || 'Sin descripción' }}</span>
                        </template>
                    </el-table-column>

                    <el-table-column prop="duration_minutes" label="Duración" width="120" sortable align="center">
                        <template #default="{ row }">
                            <el-tag :type="getDurationTagType(row.duration_minutes)" size="small" effect="light">
                                {{ formatDuration(row.duration_minutes) }}
                            </el-tag>
                        </template>
                    </el-table-column>

                    <el-table-column prop="is_active" label="Estado" width="100" sortable align="center">
                        <template #default="{ row }">
                            <el-switch v-model="row.is_active" :active-text="'Activo'" :inactive-text="'Inactivo'"
                                @change="toggleStatus(row)" :disabled="updating === row.id" />
                        </template>
                    </el-table-column>

                    <!--<el-table-column prop="created_at" label="Fecha Creación" width="160" sortable>
                        <template #default="{ row }">
                            {{ formatDate(row.created_at) }}
                        </template>
                    </el-table-column>-->

                    <el-table-column label="Acciones" width="120" fixed="right">
                        <template #default="{ row }">
                            <div class="action-buttons">
                                <el-tooltip content="Editar" placement="top">
                                    <el-button type="warning" :icon="Edit" @click="openEditDialog(row)" size="small"
                                        plain />
                                </el-tooltip>

                                <el-tooltip content="Eliminar" placement="top">
                                    <el-button type="danger" :icon="Delete" @click="confirmDelete(row)" size="small"
                                        plain />
                                </el-tooltip>
                            </div>
                        </template>
                    </el-table-column>
                </el-table>

                <!-- Paginación -->
                <div class="pagination-section">
                    <el-pagination v-model:current-page="currentPage" v-model:page-size="pageSize"
                        :page-sizes="[10, 20, 50, 100]" layout="total, sizes, prev, pager, next, jumper"
                        :total="totalTipos" @size-change="loadTiposCitas" @current-change="loadTiposCitas"
                        :prev-text="'Anterior'" :next-text="'Siguiente'" />
                </div>
            </el-card>
        </div>

        <!-- Modal Crear/Editar Tipo de Cita -->
        <el-dialog v-model="dialogVisible" :title="isEditing ? '✏️ Editar Tipo de Cita' : '📅 Nuevo Tipo de Cita'"
            width="700px" :close-on-click-modal="false" class="tipo-dialog" top="5vh">

            <el-form ref="tipoFormRef" :model="tipoForm" :rules="formRules" label-width="140px" label-position="left">

                <el-form-item label="Nombre del Tipo" prop="name">
                    <el-input v-model="tipoForm.name" placeholder="Ej: Consulta General, Emergencia, Revisión..." />
                </el-form-item>

                <el-form-item label="Descripción" prop="description">
                    <el-input v-model="tipoForm.description" type="textarea" :rows="3"
                        placeholder="Descripción del tipo de cita..." />
                </el-form-item>

                <el-form-item label="Duración (minutos)" prop="duration_minutes">
                    <el-input-number v-model="tipoForm.duration_minutes" :min="15" :max="480" :step="15"
                        placeholder="Duración en minutos" style="width: 100%">
                        <template #append>minutos</template>
                    </el-input-number>
                    <div class="duration-help">
                        <small>Duración sugerida para este tipo de cita (15-480 minutos)</small>
                    </div>
                </el-form-item>

                <!--<el-form-item label="Estado" prop="is_active">
                    <el-switch v-model="tipoForm.is_active" :active-text="'Activo'" :inactive-text="'Inactivo'"
                        active-color="#13ce66" inactive-color="#ff4949" />
                    <div class="status-help">
                        <small>Solo los tipos activos estarán disponibles para crear citas</small>
                    </div>
                </el-form-item>-->
            </el-form>

            <template #footer>
                <div class="dialog-footer">
                    <el-button @click="dialogVisible = false">Cancelar</el-button>
                    <el-button type="primary" @click="saveTipo" :loading="saving">
                        {{ isEditing ? 'Actualizar' : 'Crear' }}
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
    Calendar,
    Plus,
    Search,
    Refresh,
    Edit,
    Delete,
    User
} from '@element-plus/icons-vue'
import axios from 'axios'
import dayjs from 'dayjs'

// Estados reactivos
const loading = ref(false)
const saving = ref(false)
const updating = ref(null)
const dialogVisible = ref(false)
const isEditing = ref(false)

// Datos
const tiposCitas = ref([])
const allTiposCitas = ref([])

// Paginación
const currentPage = ref(1)
const pageSize = ref(20)
const totalTipos = computed(() => tiposCitas.value.length)

// Filtros
const filters = reactive({
    search: '',
    status: ''
})

// Formulario
const tipoForm = reactive({
    name: '',
    description: '',
    duration_minutes: 30,
    is_active: true
})

// Reglas de validación
const formRules = {
    name: [
        { required: true, message: 'El nombre es obligatorio', trigger: 'blur' },
        { min: 3, max: 255, message: 'Debe tener entre 3 y 255 caracteres', trigger: 'blur' }
    ],
    description: [
        { max: 500, message: 'Máximo 500 caracteres', trigger: 'blur' }
    ],
    duration_minutes: [
        { required: true, message: 'La duración es obligatoria', trigger: 'blur' },
        { type: 'number', min: 15, max: 480, message: 'Debe estar entre 15 y 480 minutos', trigger: 'blur' }
    ]
}

const tipoFormRef = ref()

// Funciones utilitarias
const formatDate = (dateString) => {
    if (!dateString) return 'N/A'
    return dayjs(dateString).format('DD/MM/YYYY HH:mm')
}

const formatDuration = (minutes) => {
    if (!minutes) return 'N/A'
    if (minutes < 60) {
        return `${minutes}m`
    } else {
        const hours = Math.floor(minutes / 60)
        const remainingMinutes = minutes % 60
        return remainingMinutes > 0 ? `${hours}h ${remainingMinutes}m` : `${hours}h`
    }
}

const getDurationTagType = (minutes) => {
    if (!minutes) return 'info'
    if (minutes <= 30) return 'success'
    if (minutes <= 60) return 'warning'
    return 'danger'
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
const loadTiposCitas = async () => {
    loading.value = true
    try {
        const response = await axios.get('/api/tipos-citas')

        if (response.data.success) {
            allTiposCitas.value = response.data.data
            applyFilters()
        }
    } catch (error) {
        console.error('Error loading tipos citas:', error)
        ElMessage.error('Error al cargar tipos de citas')
    } finally {
        loading.value = false
    }
}

const applyFilters = () => {
    let data = [...allTiposCitas.value]

    // Filtro por búsqueda
    if (filters.search) {
        const search = filters.search.toLowerCase()
        data = data.filter(tipo =>
            tipo.name.toLowerCase().includes(search) ||
            (tipo.description && tipo.description.toLowerCase().includes(search))
        )
    }

    // Filtro por estado
    if (filters.status) {
        if (filters.status === 'active') {
            data = data.filter(tipo => tipo.is_active)
        } else if (filters.status === 'inactive') {
            data = data.filter(tipo => !tipo.is_active)
        }
    }

    tiposCitas.value = data
}

// CRUD Operations
const openCreateDialog = () => {
    isEditing.value = false
    resetForm()
    dialogVisible.value = true
}

const openEditDialog = (tipo) => {
    isEditing.value = true
    Object.assign(tipoForm, {
        id: tipo.id,
        name: tipo.name,
        description: tipo.description || '',
        duration_minutes: tipo.duration_minutes || 30,
        is_active: tipo.is_active
    })
    dialogVisible.value = true
}

const saveTipo = async () => {
    if (!tipoFormRef.value) return

    const valid = await tipoFormRef.value.validate().catch(() => false)
    if (!valid) return

    saving.value = true
    try {
        let response

        if (isEditing.value) {
            response = await axios.put(`/api/tipos-citas/${tipoForm.id}`, tipoForm)
        } else {
            response = await axios.post('/api/tipos-citas', tipoForm)
        }

        if (response.data.success) {
            ElMessage.success(
                isEditing.value ? 'Tipo de cita actualizado correctamente' : 'Tipo de cita creado correctamente'
            )
            dialogVisible.value = false
            loadTiposCitas()
        }
    } catch (error) {
        console.error('Error saving tipo:', error)
        if (error.response?.data?.errors) {
            // Mostrar errores de validación específicos
            const errorMessages = Object.values(error.response.data.errors).flat()
            ElMessage.error(errorMessages.join(', '))
        } else {
            ElMessage.error('Error al guardar tipo de cita')
        }
    } finally {
        saving.value = false
    }
}

const toggleStatus = async (tipo) => {
    updating.value = tipo.id
    try {
        const response = await axios.put(`/api/tipos-citas/${tipo.id}`, {
            ...tipo,
            is_active: tipo.is_active
        })

        if (response.data.success) {
            ElMessage.success('Estado actualizado correctamente')
            // Actualizar el item en la lista local
            const index = allTiposCitas.value.findIndex(t => t.id === tipo.id)
            if (index !== -1) {
                allTiposCitas.value[index] = response.data.data
            }
            applyFilters()
        }
    } catch (error) {
        console.error('Error updating status:', error)
        // Revertir el cambio en caso de error
        tipo.is_active = !tipo.is_active
        ElMessage.error('Error al cambiar estado')
    } finally {
        updating.value = null
    }
}

const confirmDelete = (tipo) => {
    ElMessageBox.confirm(
        `¿Está seguro de eliminar el tipo de cita "${tipo.name}"? Esta acción no se puede deshacer.`,
        '⚠️ Confirmar Eliminación',
        {
            confirmButtonText: 'Sí, Eliminar',
            cancelButtonText: 'Cancelar',
            type: 'warning'
        }
    ).then(() => {
        deleteTipo(tipo.id)
    }).catch(() => {
        // Usuario canceló
    })
}

const deleteTipo = async (id) => {
    try {
        const response = await axios.delete(`/api/tipos-citas/${id}`)
        if (response.data.success) {
            ElMessage.success('Tipo de cita eliminado correctamente')
            loadTiposCitas()
        }
    } catch (error) {
        console.error('Error deleting tipo:', error)
        if (error.response?.status === 409) {
            ElMessage.error('No se puede eliminar: hay citas usando este tipo')
        } else {
            ElMessage.error('Error al eliminar tipo de cita')
        }
    }
}

const resetForm = () => {
    Object.assign(tipoForm, {
        name: '',
        description: '',
        duration_minutes: 30,
        is_active: true
    })
}

const clearFilters = () => {
    Object.assign(filters, {
        search: '',
        status: ''
    })
    applyFilters()
}

// Montaje del componente
onMounted(() => {
    loadTiposCitas()
})
</script>

<style scoped>
.tipos-citas-container {
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
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.title-icon {
    font-size: 36px;
    color: #f59e0b;
    filter: drop-shadow(0 2px 4px rgba(245, 158, 11, 0.3));
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

.filter-card :deep(.el-select) {
    width: 100%;
}

.filter-card :deep(.el-select .el-input__inner) {
    border-radius: 8px;
    border: 1px solid #d1d5db;
    transition: all 0.2s ease;
}

.filter-card :deep(.el-select .el-input__inner:focus) {
    border-color: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
}

.filter-card :deep(.el-input__inner) {
    border-radius: 8px;
    border: 1px solid #d1d5db;
    transition: all 0.2s ease;
}

.filter-card :deep(.el-input__inner:focus) {
    border-color: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
}

.stats-display {
    display: flex;
    justify-content: center;
}

.manage-citas-section {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    height: 100%;
}

.manage-citas-btn {
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: white !important;
    border: none !important;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3) !important;
    border-radius: 8px !important;
    font-weight: 600 !important;
    padding: 8px 16px !important;
    transition: all 0.2s ease !important;
    min-width: 100px !important;
    font-size: 13px !important;
}

.manage-citas-btn:hover {
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4) !important;
    background: linear-gradient(135deg, #059669, #047857) !important;
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

.tipo-name {
    display: flex;
    align-items: center;
    gap: 8px;
}

.tipo-icon {
    color: #f59e0b;
    font-size: 16px;
}

.description-text {
    color: #6b7280;
    font-size: 14px;
    line-height: 1.5;
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
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important;
    border: 1px solid rgba(0, 0, 0, 0.08) !important;
}

.action-buttons .el-button:hover {
    transform: translateY(-1px) scale(1.05) !important;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12) !important;
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
    background: linear-gradient(135deg, #fef3c7 0%, #fcd34d 100%);
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
    background: linear-gradient(135deg, #f59e0b, #d97706) !important;
    border: none !important;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4) !important;
    border-radius: 12px !important;
    padding: 16px 32px !important;
    font-size: 16px !important;
    font-weight: 600 !important;
    min-width: 200px !important;
    transition: all 0.3s ease !important;
}

.empty-create-btn:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 16px rgba(245, 158, 11, 0.5) !important;
}

.empty-citas-btn {
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

.empty-citas-btn:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 16px rgba(16, 185, 129, 0.5) !important;
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
    border: 1px solid rgba(245, 158, 11, 0.2);
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
    color: #d97706;
}

/* Mejorar botón crear */
.create-button {
    background: linear-gradient(135deg, #f59e0b, #d97706) !important;
    border: none !important;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4) !important;
    border-radius: 10px !important;
    padding: 12px 24px !important;
    font-size: 15px !important;
    transition: all 0.3s ease !important;
}

.create-button:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 16px rgba(245, 158, 11, 0.5) !important;
}

.clear-filters-btn {
    border-radius: 8px !important;
    font-weight: 500 !important;
    transition: all 0.2s ease !important;
    background: #f8fafc !important;
    border: 1px solid #e2e8f0 !important;
    color: #64748b !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
}

.clear-filters-btn:hover {
    background: #f1f5f9 !important;
    border-color: #cbd5e1 !important;
    color: #475569 !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15) !important;
}

/* Mejorar diálogos */
.tipo-dialog :deep(.el-dialog) {
    border-radius: 16px;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
    background: white;
}

.tipo-dialog :deep(.el-dialog__header) {
    background: linear-gradient(135deg, #fef3c7 0%, #fcd34d 100%);
    border-radius: 16px 16px 0 0;
    padding: 20px 24px;
    border-bottom: 1px solid #e5e7eb;
}

.tipo-dialog :deep(.el-dialog__title) {
    font-size: 20px;
    font-weight: 700;
    color: #1f2937;
}

.tipo-dialog :deep(.el-dialog__body) {
    padding: 24px;
    background: white;
}

.tipo-dialog :deep(.el-form-item__label) {
    font-weight: 600;
    color: #374151;
}

.tipo-dialog :deep(.el-input__inner),
.tipo-dialog :deep(.el-textarea__inner) {
    border-radius: 8px;
    border: 1px solid #d1d5db;
    transition: all 0.2s ease;
}

.tipo-dialog :deep(.el-input__inner:focus),
.tipo-dialog :deep(.el-textarea__inner:focus) {
    border-color: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
}

.duration-help,
.status-help {
    margin-top: 4px;
    color: #6b7280;
    font-size: 12px;
}

/* Responsive */
@media (max-width: 1024px) {
    .tipos-citas-container {
        padding: 16px;
    }

    .page-title {
        font-size: 28px;
    }

    .empty-actions {
        flex-direction: column;
    }

    .empty-create-btn,
    .empty-citas-btn,
    .empty-usuarios-btn {
        min-width: 250px !important;
    }

    .filters-section .el-row {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .manage-citas-section {
        justify-content: center;
    }

    .manage-citas-btn {
        min-width: 100% !important;
        font-size: 12px !important;
        padding: 6px 12px !important;
    }
    
    .stats-display {
        justify-content: center;
    }
}

@media (max-width: 768px) {
    .tipos-citas-container {
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
    .empty-citas-btn,
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
    .manage-citas-btn {
        width: 100% !important;
        min-width: 100% !important;
        font-size: 14px !important;
        padding: 12px 16px !important;
    }

    .stats-display,
    .manage-citas-section {
        justify-content: center;
    }
    
    .stats-display .el-tag {
        font-size: 14px !important;
        padding: 8px 16px !important;
    }
}
</style>