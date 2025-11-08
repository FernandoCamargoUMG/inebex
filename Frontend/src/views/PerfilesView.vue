<template>
    <div class="perfiles-container">
        <!-- Header con titulo y botón crear -->
        <div class="header-section">
            <el-row :gutter="20" align="middle">
                <el-col :span="16">
                    <h1 class="page-title">
                        <el-icon class="title-icon">
                            <User />
                        </el-icon>
                        Perfiles de Expediente
                    </h1>
                </el-col>
                <el-col :span="8" class="text-right">
                    <el-button type="primary" @click="openCreateDialog" :icon="Plus" size="large" class="create-button">
                        <strong>Nuevo Perfil</strong>
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
                            <el-col :span="8">
                                <el-input v-model="filters.search" placeholder="Buscar por nombre o descripción..."
                                    :prefix-icon="Search" @input="debounceSearch" clearable />
                            </el-col>
                            <el-col :span="4">
                                <el-button @click="clearFilters" :icon="Refresh" type="default"
                                    class="clear-filters-btn" style="width: 150%;">
                                    Limpiar Filtros
                                </el-button>
                            </el-col>
                            <el-col :span="6">
                                <div class="stats-display">
                                    <!--<el-tag type="info" size="large">
                    Total Perfiles: {{ totalPerfiles }}
                  </el-tag>-->
                                </div>
                            </el-col>
                            <el-col :span="6">
                                <div class="manage-types-section">
                                    <el-button type="success" :icon="Document"
                                        @click="$router.push('/tipos-documentos')" size="default"
                                        class="manage-types-btn">
                                        Gestionar Tipos
                                    </el-button>
                                </div>
                            </el-col>
                        </el-row>
                    </el-card>
                </el-col>
            </el-row>
        </div>

        <!-- Tabla de perfiles -->
        <div class="table-section">
            <el-card shadow="never">
                <!-- Estado vacío cuando no hay perfiles -->
                <div v-if="!loading && perfiles.length === 0" class="empty-state">
                    <el-empty description="No hay perfiles de expediente creados">
                        <div class="empty-actions">
                            <el-button type="primary" size="large" :icon="Plus" @click="openCreateDialog"
                                class="empty-create-btn">
                                Crear Primer Perfil
                            </el-button>
                            <el-button type="default" size="large" :icon="Document"
                                @click="$router.push('/tipos-documentos')" class="empty-manage-btn">
                                Gestionar Tipos de Documentos
                            </el-button>
                            <el-button type="success" size="large" :icon="User" @click="$router.push('/expedientes')"
                                class="empty-expedientes-btn">
                                Ver Expedientes
                            </el-button>
                        </div>
                        <!--<div class="empty-help">
              <p>Los perfiles definen qué tipos de documentos son necesarios para cada tipo de expediente.</p>
              <p>Primero crea algunos tipos de documentos, luego configura los perfiles según tus necesidades.</p>
            </div>-->
                    </el-empty>
                </div>

                <!-- Tabla cuando hay datos -->
                <el-table v-else :data="perfiles" v-loading="loading" style="width: 100%"
                    :default-sort="{ prop: 'name', order: 'ascending' }" :table-layout="'auto'">

                    <!--<el-table-column prop="id" label="ID" width="60" sortable />-->

                    <el-table-column prop="name" label="Nombre del Perfil" min-width="250" sortable>
                        <template #default="{ row }">
                            <div class="profile-name">
                                <el-icon class="profile-icon">
                                    <User />
                                </el-icon>
                                <strong>{{ row.name }}</strong>
                            </div>
                        </template>
                    </el-table-column>

                    <el-table-column prop="description" label="Descripción" min-width="300">
                        <template #default="{ row }">
                            <span class="description-text">{{ row.description || 'Sin descripción' }}</span>
                        </template>
                    </el-table-column>

                    <!--<el-table-column label="Documentos Requeridos" width="180" align="center">
            <template #default="{ row }">
              <div class="documents-count">
                <el-badge :value="row.document_types?.length || 0" :max="99" class="documents-badge">
                  <el-icon size="20"><Document /></el-icon>
                </el-badge>
                <span class="count-text">{{ row.document_types?.length || 0 }} tipos</span>
              </div>
            </template>
          </el-table-column>

          <el-table-column prop="created_at" label="Fecha Creación" width="160" sortable>
            <template #default="{ row }">
              {{ formatDate(row.created_at) }}
            </template>
          </el-table-column>-->

                    <el-table-column label="Acciones" width="150" fixed="right">
                        <template #default="{ row }">
                            <div class="action-buttons">
                                <el-tooltip content="Ver documentos" placement="top">
                                    <el-button type="info" :icon="View" @click="viewProfile(row)" size="small" plain />
                                </el-tooltip>

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
                        :total="totalPerfiles" @size-change="loadPerfiles" @current-change="loadPerfiles"
                        :prev-text="'Anterior'" :next-text="'Siguiente'" />
                </div>
            </el-card>
        </div>

        <!-- Modal Crear/Editar Perfil -->
        <el-dialog v-model="dialogVisible"
            :title="isEditing ? '✏️ Editar Perfil de Expediente' : '📄 Nuevo Perfil de Expediente'" width="900px"
            :close-on-click-modal="false" class="perfil-dialog" top="3vh">

            <el-form ref="perfilFormRef" :model="perfilForm" :rules="formRules" label-width="160px"
                label-position="left">

                <el-form-item label="Nombre del Perfil" prop="name">
                    <el-input v-model="perfilForm.name" placeholder="Ej: Expediente Nuevo Ingreso" />
                </el-form-item>

                <el-form-item label="Descripción" prop="description">
                    <el-input v-model="perfilForm.description" type="textarea" :rows="3"
                        placeholder="Descripción del perfil de expediente..." />
                </el-form-item>

                <!--<el-divider>Documentos Requeridos</el-divider>-->

                <!--<el-form-item label="Tipos de Documentos" prop="document_type_ids">
          <div class="document-types-selection">
            <el-transfer
              v-model="perfilForm.document_type_ids"
              :data="availableDocumentTypes"
              :titles="['Disponibles', 'Requeridos']"
              :button-texts="['Quitar', 'Agregar']"
              :render-content="renderDocumentType"
              filterable
              filter-placeholder="Buscar tipo..."
              style="text-align: left; display: inline-block">
            </el-transfer>
            <div class="selection-help">
              <el-icon><InfoFilled /></el-icon>
              <small>Seleccione los tipos de documentos que serán requeridos para este perfil</small>
            </div>
          </div>
        </el-form-item>-->
            </el-form>

            <template #footer>
                <div class="dialog-footer">
                    <el-button @click="dialogVisible = false">Cancelar</el-button>
                    <el-button type="primary" @click="savePerfil" :loading="saving">
                        {{ isEditing ? 'Actualizar' : 'Crear' }}
                    </el-button>
                </div>
            </template>
        </el-dialog>

        <!-- Modal Ver Perfil -->
        <el-dialog v-model="viewDialogVisible" title="🔍 Detalles del Perfil" width="800px" class="view-dialog">

            <div v-if="selectedPerfil" class="perfil-details">
                <el-descriptions title="Información del Perfil" :column="1" border>
                    <el-descriptions-item label="Nombre">
                        <strong>{{ selectedPerfil.name }}</strong>
                    </el-descriptions-item>
                    <el-descriptions-item label="Descripción">
                        {{ selectedPerfil.description || 'Sin descripción' }}
                    </el-descriptions-item>
                    <el-descriptions-item label="Fecha Creación">
                        {{ formatDate(selectedPerfil.created_at) }}
                    </el-descriptions-item>
                    <el-descriptions-item label="Total Documentos">
                        {{ selectedPerfil.document_types?.length || 0 }} tipos requeridos
                    </el-descriptions-item>
                </el-descriptions>

                <el-divider />

                <div class="documents-section">
                    <h4>📋 Documentos Requeridos</h4>
                    <el-empty v-if="!selectedPerfil.document_types?.length"
                        description="No hay documentos requeridos configurados" />
                    <div v-else class="document-types-list">
                        <el-card v-for="docType in selectedPerfil.document_types" :key="docType.id"
                            class="document-type-card" shadow="hover">
                            <div class="doc-type-info">
                                <div class="doc-type-header">
                                    <el-icon class="doc-icon">
                                        <Document />
                                    </el-icon>
                                    <strong>{{ docType.name }}</strong>
                                    <el-tag :type="docType.is_required ? 'danger' : 'info'" size="small" effect="light">
                                        {{ docType.is_required ? 'Obligatorio' : 'Opcional' }}
                                    </el-tag>
                                </div>
                                <p class="doc-type-description">
                                    {{ docType.description || 'Sin descripción' }}
                                </p>
                            </div>
                        </el-card>
                    </div>
                </div>
            </div>

            <template #footer>
                <div class="dialog-footer">
                    <el-button @click="viewDialogVisible = false">Cerrar</el-button>
                    <el-button type="primary" :icon="Edit" @click="openEditDialog(selectedPerfil)">
                        Editar Perfil
                    </el-button>
                </div>
            </template>
        </el-dialog>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, h } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
    User,
    Document,
    Plus,
    Search,
    Refresh,
    View,
    Edit,
    Delete,
    InfoFilled
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
const perfiles = ref([])
const allPerfiles = ref([])
const documentTypes = ref([])
const selectedPerfil = ref(null)

// Paginación
const currentPage = ref(1)
const pageSize = ref(20)
const totalPerfiles = computed(() => perfiles.value.length)

// Filtros
const filters = reactive({
    search: ''
})

// Formulario
const perfilForm = reactive({
    name: '',
    description: '',
    document_type_ids: []
})

// Datos para el transfer
const availableDocumentTypes = computed(() => {
    return documentTypes.value.map(docType => ({
        key: docType.id,
        label: docType.name,
        description: docType.description,
        is_required: docType.is_required
    }))
})

// Reglas de validación
const formRules = {
    name: [
        { required: true, message: 'El nombre es obligatorio', trigger: 'blur' },
        { min: 3, max: 100, message: 'Debe tener entre 3 y 100 caracteres', trigger: 'blur' }
    ],
    description: [
        { max: 500, message: 'Máximo 500 caracteres', trigger: 'blur' }
    ],
    document_type_ids: [
        { type: 'array', min: 1, message: 'Debe seleccionar al menos un tipo de documento', trigger: 'change' }
    ]
}

const perfilFormRef = ref()

// Funciones utilitarias
const formatDate = (dateString) => {
    if (!dateString) return 'N/A'
    return dayjs(dateString).format('DD/MM/YYYY HH:mm')
}

// Función para renderizar elementos del transfer
const renderDocumentType = (option) => {
    return h('div', { class: 'transfer-item' }, [
        h('div', { class: 'transfer-title' }, option.label),
        h('div', { class: 'transfer-description' }, option.description || 'Sin descripción'),
        h('el-tag', {
            type: option.is_required ? 'danger' : 'info',
            size: 'small',
            effect: 'light'
        }, option.is_required ? 'Obligatorio' : 'Opcional')
    ])
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
const loadPerfiles = async () => {
    loading.value = true
    try {
        const response = await axios.get('/api/perfiles')

        if (response.data.success) {
            allPerfiles.value = response.data.data
            applyFilters()
        }
    } catch (error) {
        console.error('Error loading perfiles:', error)
        ElMessage.error('Error al cargar perfiles')
    } finally {
        loading.value = false
    }
}

const loadDocumentTypes = async () => {
    try {
        const response = await axios.get('/api/tipos-documentos')

        if (response.data.success) {
            documentTypes.value = response.data.data
        }
    } catch (error) {
        console.error('Error loading document types:', error)
    }
}

const applyFilters = () => {
    let data = [...allPerfiles.value]

    // Filtro por búsqueda
    if (filters.search) {
        const search = filters.search.toLowerCase()
        data = data.filter(perfil =>
            perfil.name.toLowerCase().includes(search) ||
            (perfil.description && perfil.description.toLowerCase().includes(search))
        )
    }

    perfiles.value = data
}

// CRUD Operations
const openCreateDialog = () => {
    isEditing.value = false
    resetForm()
    dialogVisible.value = true
}

const openEditDialog = (perfil) => {
    isEditing.value = true
    Object.assign(perfilForm, {
        id: perfil.id,
        name: perfil.name,
        description: perfil.description || '',
        document_type_ids: perfil.document_types?.map(dt => dt.id) || []
    })
    viewDialogVisible.value = false
    dialogVisible.value = true
}

const savePerfil = async () => {
    if (!perfilFormRef.value) return

    const valid = await perfilFormRef.value.validate().catch(() => false)
    if (!valid) return

    saving.value = true
    try {
        let response

        if (isEditing.value) {
            response = await axios.put(`/api/perfiles/${perfilForm.id}`, perfilForm)
        } else {
            response = await axios.post('/api/perfiles', perfilForm)
        }

        if (response.data.success) {
            ElMessage.success(
                isEditing.value ? 'Perfil actualizado correctamente' : 'Perfil creado correctamente'
            )
            dialogVisible.value = false
            loadPerfiles()
        }
    } catch (error) {
        console.error('Error saving perfil:', error)
        ElMessage.error('Error al guardar perfil')
    } finally {
        saving.value = false
    }
}

const viewProfile = async (perfil) => {
    try {
        const response = await axios.get(`/api/perfiles/${perfil.id}`)
        if (response.data.success) {
            selectedPerfil.value = response.data.data
            viewDialogVisible.value = true
        }
    } catch (error) {
        console.error('Error loading perfil details:', error)
        ElMessage.error('Error al cargar detalles del perfil')
    }
}

const confirmDelete = (perfil) => {
    ElMessageBox.confirm(
        `¿Está seguro de eliminar el perfil "${perfil.name}"? Esta acción no se puede deshacer.`,
        '⚠️ Confirmar Eliminación',
        {
            confirmButtonText: 'Sí, Eliminar',
            cancelButtonText: 'Cancelar',
            type: 'warning'
        }
    ).then(() => {
        deletePerfil(perfil.id)
    }).catch(() => {
        // Usuario canceló
    })
}

const deletePerfil = async (id) => {
    try {
        const response = await axios.delete(`/api/perfiles/${id}`)
        if (response.data.success) {
            ElMessage.success('Perfil eliminado correctamente')
            loadPerfiles()
        }
    } catch (error) {
        console.error('Error deleting perfil:', error)
        if (error.response?.status === 400) {
            ElMessage.error('No se puede eliminar: hay expedientes usando este perfil')
        } else {
            ElMessage.error('Error al eliminar perfil')
        }
    }
}

const resetForm = () => {
    Object.assign(perfilForm, {
        name: '',
        description: '',
        document_type_ids: []
    })
}

const clearFilters = () => {
    Object.assign(filters, {
        search: ''
    })
    applyFilters()
}

// Montaje del componente
onMounted(() => {
    loadPerfiles()
    loadDocumentTypes()
})
</script>

<style scoped>
.perfiles-container {
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
    color: #8b5cf6;
    filter: drop-shadow(0 2px 4px rgba(139, 92, 246, 0.3));
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

.manage-types-section {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    height: 100%;
}

.manage-types-btn {
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: white !important;
    border: none !important;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3) !important;
    border-radius: 8px !important;
    font-weight: 600 !important;
    padding: 8px 20px !important;
    transition: all 0.2s ease !important;
    min-width: 140px !important;
}

.manage-types-btn:hover {
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

.profile-name {
    display: flex;
    align-items: center;
    gap: 8px;
}

.profile-icon {
    color: #8b5cf6;
    font-size: 16px;
}

.description-text {
    color: #6b7280;
    font-size: 14px;
    line-height: 1.5;
}

.documents-count {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
}

.documents-badge {
    cursor: pointer;
    transition: transform 0.2s ease;
}

.documents-badge:hover {
    transform: scale(1.1);
}

.documents-badge :deep(.el-badge__content) {
    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    border: none;
    box-shadow: 0 2px 4px rgba(139, 92, 246, 0.3);
    font-size: 11px;
    font-weight: 600;
}

.count-text {
    font-size: 11px;
    color: #6b7280;
    font-weight: 500;
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

/* Transfer component styles */
.document-types-selection {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.selection-help {
    margin-top: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #6b7280;
    font-size: 13px;
    text-align: center;
}

.transfer-item {
    padding: 8px 0;
    border-bottom: 1px solid #f3f4f6;
}

.transfer-title {
    font-weight: 600;
    color: #374151;
    margin-bottom: 4px;
    font-size: 14px;
}

.transfer-description {
    font-size: 11px;
    color: #6b7280;
    margin-bottom: 6px;
    line-height: 1.3;
}

/* Modal detalles */
.perfil-details {
    max-height: 70vh;
    overflow-y: auto;
}

.documents-section h4 {
    color: #1f2937;
    margin-bottom: 16px;
    font-weight: 700;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.document-types-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 16px;
}

.document-type-card {
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    transition: all 0.2s ease;
}

.document-type-card:hover {
    border-color: #8b5cf6;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(139, 92, 246, 0.15);
}

.doc-type-info {
    padding: 16px;
}

.doc-type-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}

.doc-icon {
    color: #8b5cf6;
    font-size: 16px;
}

.doc-type-description {
    color: #6b7280;
    font-size: 13px;
    line-height: 1.4;
    margin: 0;
}

/* Mejorar botón crear */
.create-button {
    background: linear-gradient(135deg, #8b5cf6, #7c3aed) !important;
    border: none !important;
    box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4) !important;
    border-radius: 10px !important;
    padding: 12px 24px !important;
    font-size: 15px !important;
    transition: all 0.3s ease !important;
}

.create-button:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 16px rgba(139, 92, 246, 0.5) !important;
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
.perfil-dialog :deep(.el-dialog),
.view-dialog :deep(.el-dialog) {
    border-radius: 16px;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
    background: white;
}

.perfil-dialog :deep(.el-dialog__header),
.view-dialog :deep(.el-dialog__header) {
    background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
    border-radius: 16px 16px 0 0;
    padding: 20px 24px;
    border-bottom: 1px solid #e5e7eb;
}

.perfil-dialog :deep(.el-dialog__title),
.view-dialog :deep(.el-dialog__title) {
    font-size: 20px;
    font-weight: 700;
    color: #1f2937;
}

.perfil-dialog :deep(.el-dialog__body),
.view-dialog :deep(.el-dialog__body) {
    padding: 24px;
    background: white;
}

.perfil-dialog :deep(.el-form-item__label) {
    font-weight: 600;
    color: #374151;
}

.perfil-dialog :deep(.el-input__inner),
.perfil-dialog :deep(.el-textarea__inner) {
    border-radius: 8px;
    border: 1px solid #d1d5db;
    transition: all 0.2s ease;
}

.perfil-dialog :deep(.el-input__inner:focus),
.perfil-dialog :deep(.el-textarea__inner:focus) {
    border-color: #8b5cf6;
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}

/* Transfer styles */
.perfil-dialog :deep(.el-transfer) {
    text-align: left;
    display: flex;
    justify-content: center;
    width: 100%;
    margin: 0 auto;
}

.perfil-dialog :deep(.el-transfer-panel) {
    border-radius: 8px;
    border: 1px solid #d1d5db;
    width: 280px !important;
    height: 350px !important;
}

.perfil-dialog :deep(.el-transfer-panel__header) {
    background: #f8fafc;
    border-radius: 8px 8px 0 0;
    font-weight: 600;
    padding: 12px 16px;
    border-bottom: 1px solid #e5e7eb;
}

.perfil-dialog :deep(.el-transfer-panel__body) {
    padding: 8px;
    height: calc(100% - 50px);
    overflow-y: auto;
}

.perfil-dialog :deep(.el-transfer-panel__list) {
    height: calc(100% - 40px);
    overflow-y: auto;
}

.perfil-dialog :deep(.el-transfer-panel__item) {
    padding: 8px 12px;
    margin: 2px 0;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.perfil-dialog :deep(.el-transfer-panel__item:hover) {
    background: #f0f9ff;
}

.perfil-dialog :deep(.el-transfer__buttons) {
    padding: 0 20px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 8px;
}

.perfil-dialog :deep(.el-transfer__button) {
    border-radius: 6px !important;
    font-size: 12px !important;
    padding: 8px 16px !important;
    min-width: 80px !important;
}

/* Estado vacío */
.empty-state {
    padding: 60px 40px;
    text-align: center;
    background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
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
    background: linear-gradient(135deg, #8b5cf6, #7c3aed) !important;
    border: none !important;
    box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4) !important;
    border-radius: 12px !important;
    padding: 16px 32px !important;
    font-size: 16px !important;
    font-weight: 600 !important;
    min-width: 200px !important;
    transition: all 0.3s ease !important;
}

.empty-create-btn:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 16px rgba(139, 92, 246, 0.5) !important;
}

.empty-manage-btn {
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

.empty-manage-btn:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 16px rgba(16, 185, 129, 0.5) !important;
}

.empty-expedientes-btn {
    background: linear-gradient(135deg, #f59e0b, #d97706) !important;
    color: white !important;
    border: none !important;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4) !important;
    border-radius: 12px !important;
    padding: 14px 28px !important;
    font-size: 15px !important;
    font-weight: 600 !important;
    min-width: 200px !important;
    transition: all 0.3s ease !important;
}

.empty-expedientes-btn:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 16px rgba(245, 158, 11, 0.5) !important;
}

.empty-help {
    background: rgba(255, 255, 255, 0.8);
    border-radius: 12px;
    padding: 24px;
    border: 1px solid rgba(139, 92, 246, 0.2);
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
    color: #8b5cf6;
}

/* Responsive */
@media (max-width: 1024px) {
    .perfiles-container {
        padding: 16px;
    }

    .page-title {
        font-size: 28px;
    }

    .document-types-list {
        grid-template-columns: 1fr;
    }

    .empty-actions {
        flex-direction: column;
    }

    .empty-create-btn,
    .empty-manage-btn,
    .empty-expedientes-btn {
        min-width: 250px !important;
    }

    .filters-section .el-row {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .manage-types-section {
        justify-content: center;
    }

    .manage-types-btn {
        min-width: 100% !important;
    }
}

@media (max-width: 768px) {
    .perfiles-container {
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
    .empty-manage-btn,
    .empty-expedientes-btn {
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
        margin-bottom: 8px;
    }

    .clear-filters-btn,
    .manage-types-btn {
        width: 100% !important;
        min-width: 100% !important;
    }

    .stats-display,
    .manage-types-section {
        justify-content: center;
    }
}
</style>