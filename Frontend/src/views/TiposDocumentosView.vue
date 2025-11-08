<template>
    <div class="tipos-documentos-container">
        <!-- Header con titulo y botón crear -->
        <div class="header-section">
            <el-row :gutter="20" align="middle">
                <el-col :span="16">
                    <h1 class="page-title">
                        <el-icon class="title-icon">
                            <Document />
                        </el-icon>
                        Tipos de Documentos
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
                            <el-col :span="8">
                                <el-input v-model="filters.search" placeholder="Buscar por nombre o descripción..."
                                    :prefix-icon="Search" @input="debounceSearch" clearable />
                            </el-col>
                            <el-col :span="6">
                                <el-select v-model="filters.is_required" placeholder="Filtrar por requerido" clearable
                                    @change="loadTiposDocumentos">
                                    <el-option label="Todos" value="" />
                                    <el-option label="Requeridos" :value="true" />
                                    <el-option label="Opcionales" :value="false" />
                                </el-select>
                            </el-col>
                            <el-col :span="4">
                                <el-button @click="clearFilters" :icon="Refresh" type="default"
                                    class="clear-filters-btn">
                                    Limpiar Filtros
                                </el-button>
                            </el-col>
                            <el-col :span="6" class="text-right">
                                <el-tag type="info" size="large">
                                    Total: {{ totalTiposDocumentos }}
                                </el-tag>
                            </el-col>
                        </el-row>
                    </el-card>
                </el-col>
            </el-row>
        </div>

        <!-- Tabla de tipos de documentos -->
        <div class="table-section">
            <el-card shadow="never">
                <el-table :data="tiposDocumentos" v-loading="loading" style="width: 100%"
                    :default-sort="{ prop: 'name', order: 'ascending' }" :table-layout="'auto'">

                    <!--<el-table-column prop="id" label="ID" width="60" sortable />-->

                    <el-table-column prop="name" label="Nombre" min-width="200" sortable>
                        <template #default="{ row }">
                            <div class="document-type-name">
                                <el-icon class="doc-icon">
                                    <Document />
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

                    <!--<el-table-column prop="is_required" label="Requerido" width="120" align="center" sortable>
                        <template #default="{ row }">
                            <el-tag :type="row.is_required ? 'danger' : 'info'" effect="light" size="small">
                                {{ row.is_required ? 'Requerido' : 'Opcional' }}
                            </el-tag>
                        </template>
                    </el-table-column>

                    <el-table-column prop="created_at" label="Fecha Creación" width="160" sortable>
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
                        :total="totalTiposDocumentos" @size-change="loadTiposDocumentos"
                        @current-change="loadTiposDocumentos" :prev-text="'Anterior'" :next-text="'Siguiente'" />
                </div>
            </el-card>
        </div>

        <!-- Modal Crear/Editar Tipo de Documento -->
        <el-dialog v-model="dialogVisible"
            :title="isEditing ? '✏️ Editar Tipo de Documento' : '📄 Nuevo Tipo de Documento'" width="600px"
            :close-on-click-modal="false" class="tipo-documento-dialog">

            <el-form ref="tipoDocumentoFormRef" :model="tipoDocumentoForm" :rules="formRules" label-width="140px"
                label-position="left">

                <el-form-item label="Nombre" prop="name">
                    <el-input v-model="tipoDocumentoForm.name" placeholder="Ej: Cédula de Identidad" />
                </el-form-item>

                <el-form-item label="Descripción" prop="description">
                    <el-input v-model="tipoDocumentoForm.description" type="textarea" :rows="3"
                        placeholder="Descripción del tipo de documento..." />
                </el-form-item>

                <!--<el-form-item label="¿Es requerido?" prop="is_required">
                    <el-switch v-model="tipoDocumentoForm.is_required" active-text="Requerido" inactive-text="Opcional"
                        :active-value="true" :inactive-value="false" />
                    <div class="switch-help">
                        <small>Los documentos requeridos son obligatorios para completar el expediente</small>
                    </div>
                </el-form-item>-->
            </el-form>

            <template #footer>
                <div class="dialog-footer">
                    <el-button @click="dialogVisible = false">Cancelar</el-button>
                    <el-button type="primary" @click="saveTipoDocumento" :loading="saving">
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
    Document,
    Plus,
    Search,
    Refresh,
    Edit,
    Delete
} from '@element-plus/icons-vue'
import axios from 'axios'
import dayjs from 'dayjs'

// Estados reactivos
const loading = ref(false)
const saving = ref(false)
const dialogVisible = ref(false)
const isEditing = ref(false)

// Datos
const tiposDocumentos = ref([])
const allTiposDocumentos = ref([])

// Paginación
const currentPage = ref(1)
const pageSize = ref(20)
const totalTiposDocumentos = computed(() => tiposDocumentos.value.length)

// Filtros
const filters = reactive({
    search: '',
    is_required: ''
})

// Formulario
const tipoDocumentoForm = reactive({
    name: '',
    description: '',
    is_required: true
})

// Reglas de validación
const formRules = {
    name: [
        { required: true, message: 'El nombre es obligatorio', trigger: 'blur' },
        { min: 3, max: 100, message: 'Debe tener entre 3 y 100 caracteres', trigger: 'blur' }
    ],
    description: [
        { max: 500, message: 'Máximo 500 caracteres', trigger: 'blur' }
    ]
}

const tipoDocumentoFormRef = ref()

// Funciones utilitarias
const formatDate = (dateString) => {
    if (!dateString) return 'N/A'
    return dayjs(dateString).format('DD/MM/YYYY HH:mm')
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
const loadTiposDocumentos = async () => {
    loading.value = true
    try {
        const response = await axios.get('/api/tipos-documentos')

        if (response.data.success) {
            allTiposDocumentos.value = response.data.data
            applyFilters()
        }
    } catch (error) {
        console.error('Error loading tipos documentos:', error)
        ElMessage.error('Error al cargar tipos de documentos')
    } finally {
        loading.value = false
    }
}

const applyFilters = () => {
    let data = [...allTiposDocumentos.value]

    // Filtro por búsqueda
    if (filters.search) {
        const search = filters.search.toLowerCase()
        data = data.filter(tipo =>
            tipo.name.toLowerCase().includes(search) ||
            (tipo.description && tipo.description.toLowerCase().includes(search))
        )
    }

    // Filtro por requerido
    if (filters.is_required !== '') {
        data = data.filter(tipo => tipo.is_required === filters.is_required)
    }

    tiposDocumentos.value = data
}

// CRUD Operations
const openCreateDialog = () => {
    isEditing.value = false
    resetForm()
    dialogVisible.value = true
}

const openEditDialog = (tipoDocumento) => {
    isEditing.value = true
    Object.assign(tipoDocumentoForm, {
        id: tipoDocumento.id,
        name: tipoDocumento.name,
        description: tipoDocumento.description || '',
        is_required: tipoDocumento.is_required
    })
    dialogVisible.value = true
}

const saveTipoDocumento = async () => {
    if (!tipoDocumentoFormRef.value) return

    const valid = await tipoDocumentoFormRef.value.validate().catch(() => false)
    if (!valid) return

    saving.value = true
    try {
        let response

        if (isEditing.value) {
            response = await axios.put(`/api/tipos-documentos/${tipoDocumentoForm.id}`, tipoDocumentoForm)
        } else {
            response = await axios.post('/api/tipos-documentos', tipoDocumentoForm)
        }

        if (response.data.success) {
            ElMessage.success(
                isEditing.value ? 'Tipo de documento actualizado correctamente' : 'Tipo de documento creado correctamente'
            )
            dialogVisible.value = false
            loadTiposDocumentos()
        }
    } catch (error) {
        console.error('Error saving tipo documento:', error)
        ElMessage.error('Error al guardar tipo de documento')
    } finally {
        saving.value = false
    }
}

const confirmDelete = (tipoDocumento) => {
    ElMessageBox.confirm(
        `¿Está seguro de eliminar el tipo de documento "${tipoDocumento.name}"? Esta acción no se puede deshacer.`,
        '⚠️ Confirmar Eliminación',
        {
            confirmButtonText: 'Sí, Eliminar',
            cancelButtonText: 'Cancelar',
            type: 'warning'
        }
    ).then(() => {
        deleteTipoDocumento(tipoDocumento.id)
    }).catch(() => {
        // Usuario canceló
    })
}

const deleteTipoDocumento = async (id) => {
    try {
        const response = await axios.delete(`/api/tipos-documentos/${id}`)
        if (response.data.success) {
            ElMessage.success('Tipo de documento eliminado correctamente')
            loadTiposDocumentos()
        }
    } catch (error) {
        console.error('Error deleting tipo documento:', error)
        if (error.response?.status === 400) {
            ElMessage.error('No se puede eliminar: hay documentos usando este tipo')
        } else {
            ElMessage.error('Error al eliminar tipo de documento')
        }
    }
}

const resetForm = () => {
    Object.assign(tipoDocumentoForm, {
        name: '',
        description: '',
        is_required: true
    })
}

const clearFilters = () => {
    Object.assign(filters, {
        search: '',
        is_required: ''
    })
    applyFilters()
}

// Montaje del componente
onMounted(() => {
    loadTiposDocumentos()
})
</script>

<style scoped>
.tipos-documentos-container {
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
    color: #3b82f6;
    filter: drop-shadow(0 2px 4px rgba(59, 130, 246, 0.3));
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

.document-type-name {
    display: flex;
    align-items: center;
    gap: 8px;
}

.doc-icon {
    color: #3b82f6;
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

/* Mejorar diálogo */
.tipo-documento-dialog :deep(.el-dialog) {
    border-radius: 16px;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
    background: white;
}

.tipo-documento-dialog :deep(.el-dialog__header) {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border-radius: 16px 16px 0 0;
    padding: 20px 24px;
    border-bottom: 1px solid #e5e7eb;
}

.tipo-documento-dialog :deep(.el-dialog__title) {
    font-size: 20px;
    font-weight: 700;
    color: #1f2937;
}

.tipo-documento-dialog :deep(.el-dialog__body) {
    padding: 24px;
    background: white;
}

.tipo-documento-dialog :deep(.el-form-item__label) {
    font-weight: 600;
    color: #374151;
}

.tipo-documento-dialog :deep(.el-input__inner),
.tipo-documento-dialog :deep(.el-textarea__inner) {
    border-radius: 8px;
    border: 1px solid #d1d5db;
    transition: all 0.2s ease;
}

.tipo-documento-dialog :deep(.el-input__inner:focus),
.tipo-documento-dialog :deep(.el-textarea__inner:focus) {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.switch-help {
    margin-top: 8px;
    color: #6b7280;
}

/* Mejorar tags */
.table-section :deep(.el-tag) {
    font-weight: 600;
    border: none;
    padding: 4px 12px;
    border-radius: 20px;
}

.table-section :deep(.el-tag.el-tag--danger) {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
}

.table-section :deep(.el-tag.el-tag--info) {
    background: linear-gradient(135deg, #6b7280, #4b5563);
    color: white;
}

/* Responsive */
@media (max-width: 1024px) {
    .tipos-documentos-container {
        padding: 16px;
    }

    .page-title {
        font-size: 28px;
    }
}

@media (max-width: 768px) {
    .tipos-documentos-container {
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
}
</style>