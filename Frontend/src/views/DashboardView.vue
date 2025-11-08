<template>
  <section class="dashboard-content">
    <h1 class="dashboard-title">Bienvenido a INEBEX</h1>
    
    <!-- Panel de notificaciones -->
    <div v-if="notificacionesHoy.length > 0" class="notifications-panel">
      <div class="notification-header">
        <h3>📅 Recordatorios de hoy</h3>
        <el-button size="small" type="text" @click="dismissAllNotifications">Marcar todas como vistas</el-button>
      </div>
      <div class="notification-list">
        <div v-for="notif in notificacionesHoy" :key="notif.id" class="notification-item">
          <div class="notification-content">
            <strong>{{ notif.title || 'Cita programada' }}</strong>
            <span class="notification-time">{{ formatTime(notif.begin) }}</span>
          </div>
          <el-button size="small" type="text" @click="dismissNotification(notif.id)">×</el-button>
        </div>
      </div>
    </div>

    <div class="dashboard-cards">
      <el-card class="dashboard-card citas-hoy" shadow="hover">
        <div class="card-title">Citas de hoy</div>
        <div class="card-value">{{ citasHoyCount }}</div>
      </el-card>
      <el-card class="dashboard-card revision" shadow="hover">
        <div class="card-title">En revisión</div>
        <div class="card-value">{{ expedientesRevisionCount }}</div>
      </el-card>
      <el-card class="dashboard-card aprobados" shadow="hover">
        <div class="card-title">Aprobados</div>
        <div class="card-value">{{ expedientesAprobadosCount }}</div>
      </el-card>
      <el-card class="dashboard-card rechazados" shadow="hover">
        <div class="card-title">Rechazados</div>
        <div class="card-value">{{ expedientesRechazadosCount }}</div>
      </el-card>
      <el-card class="dashboard-card notificaciones" shadow="hover">
        <div class="card-title">Notificaciones</div>
        <div class="card-value">{{ notificacionesHoyCount }}</div>
      </el-card>
    </div>
    <div class="dashboard-calendar">
      <div class="calendar-header">
        <div class="header-left">
          <h2 style="color:#3d5c2c; margin:0;">Citas</h2>
        </div>
        <div class="header-right">
          <div class="view-toggle">
            <el-button :type="viewMode === 'calendar' ? 'primary' : 'default'" size="small" @click="setView('calendar')">
              <el-icon><CalendarIcon /></el-icon> Calendario
            </el-button>
            <el-button :type="viewMode === 'list' ? 'primary' : 'default'" size="small" @click="setView('list')">
              <el-icon><List /></el-icon> Listado
            </el-button>
          </div>
          <el-button type="success" size="default" @click="() => { console.log('🎯 CLICK en Nueva Cita detectado!'); openCreateDialog(); }" style="margin-left:15px; font-weight:bold;">
            <el-icon><Plus /></el-icon> Nueva Cita
          </el-button>
        </div>
      </div>

      <!-- Calendar view -->
      <div v-if="viewMode === 'calendar'" class="calendar-wrapper" style="margin: 12px 0 20px; position: relative;">
        <!-- Usar div simple y montar FullCalendar manualmente -->
        <div id="calendar-container" ref="calendarRef" style="min-height: 400px;"></div>
        
        <!-- Botón flotante para nueva cita en vista calendario -->
        <el-button 
          type="success" 
          size="large" 
          circle 
          @click="() => { console.log('🎯 CLICK en botón flotante detectado!'); openCreateDialog(); }"
          class="floating-add-btn"
          title="Agregar nueva cita"
        >
          <el-icon size="20"><Plus /></el-icon>
        </el-button>
      </div>

      <!-- List / Filters view -->
      <div v-if="viewMode === 'list'" class="events-table">
        <div class="filters-bar">
          <div class="filters-left">
            <el-input v-model="searchQuery" placeholder="Buscar por título u observaciones" clearable size="small" style="width:300px" :disabled="isLoading" />
            <el-select v-model="statusFilter" placeholder="Estado" clearable size="small" style="width:160px; margin-left:12px" :disabled="isLoading">
            <el-option label="Todos" :value="''"></el-option>
            <el-option label="Pendiente" value="pending"></el-option>
            <el-option label="Confirmado" value="confirmed"></el-option>
            <el-option label="Completado" value="completed"></el-option>
            <el-option label="Cancelado" value="canceled"></el-option>
          </el-select>
          <el-date-picker v-model="dateFilter" type="date" placeholder="Fecha" size="small" style="margin-left:12px" :disabled="isLoading" />
          <el-button @click="clearFilters" size="small" style="margin-left:12px" :disabled="isLoading">Limpiar</el-button>
          </div>
          <div class="filters-right">
            <el-button type="success" size="default" @click="() => { console.log('🎯 CLICK en Agregar Cita (filtros) detectado!'); openCreateDialog(); }" style="font-weight:bold;">
              <el-icon><Plus /></el-icon> Agregar Cita
            </el-button>
          </div>
        </div>

        <!-- Indicador de carga -->
        <div v-if="isLoading" class="loading-container">
          <div class="loading-spinner">⏳</div>
          <div class="loading-text">Cargando citas...</div>
        </div>

        <!-- Tabla de datos -->
        <div v-else class="table-card">
          <table>
            <thead>
              <tr>
                <th style="width:160px">Fecha / Hora</th>
                <th>Título</th>
                <th style="width:140px">Estado</th>
                <th>Observaciones</th>
                <th style="width:180px">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="c in paginatedCitas" :key="c.id">
                <td class="cell-date">{{ formatDateTime(c.begin) }}</td>
                <td class="cell-title">{{ c.title }}</td>
                <td class="cell-status"><span :class="['status-badge', c.status]">{{ translateStatus(c.status) }}</span></td>
                <td class="cell-obs">{{ c.observations }}</td>
                <td class="cell-actions">
                  <div class="action-buttons">
                    <!-- Botones de acción rápida -->
                    <el-button-group size="small" v-if="c.status === 'pending'" style="margin-right:8px">
                      <el-button type="success" @click="changeStatus(c.id, 'confirmed')" title="Confirmar">
                        <el-icon><Check /></el-icon>
                      </el-button>
                      <el-button type="warning" @click="changeStatus(c.id, 'canceled')" title="Cancelar">
                        <el-icon><Close /></el-icon>
                      </el-button>
                    </el-button-group>
                    
                    <el-button-group size="small" v-if="c.status === 'confirmed'" style="margin-right:8px">
                      <el-button type="primary" @click="changeStatus(c.id, 'completed')" title="Completar">
                        <el-icon><CircleCheck /></el-icon>
                      </el-button>
                      <el-button type="warning" @click="changeStatus(c.id, 'canceled')" title="Cancelar">
                        <el-icon><Close /></el-icon>
                      </el-button>
                    </el-button-group>
                    
                    <!-- Selector de estado completo -->
                    <el-select v-model="c.status" size="small" style="width:110px; margin-right:6px" @change="changeStatus(c.id, $event)">
                      <el-option label="Pendiente" value="pending"></el-option>
                      <el-option label="Confirmado" value="confirmed"></el-option>
                      <el-option label="Completado" value="completed"></el-option>
                      <el-option label="Cancelado" value="canceled"></el-option>
                    </el-select>
                    
                    <!-- Botones principales -->
                    <el-button type="primary" size="small" @click="openEditDialog(c)" title="Editar cita">
                      <el-icon><Edit /></el-icon>
                    </el-button>
                    <el-button type="danger" size="small" @click="confirmDeleteAppointment(c)" title="Eliminar cita">
                      <el-icon><Delete /></el-icon>
                    </el-button>
                  </div>
                </td>
              </tr>
              <tr v-if="!isLoading && paginatedCitas.length === 0">
                <td colspan="5" style="text-align:center; padding:18px">
                  {{ dataLoaded ? 'No hay citas que coincidan con los filtros.' : 'No hay citas disponibles.' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="table-footer">
          <el-pagination
            background
            layout="prev, pager, next, sizes, total"
            :page-size="pageSize"
            :page-sizes="[5,10,20,50]"
            :current-page="currentPage"
            :total="filteredCitas.length"
            @size-change="onSizeChange"
            @current-change="onPageChange"
          />
        </div>
      </div>
    </div>
    <!-- Se eliminó la sección de depuración que listaba las citas crudas del backend -->
  
  <!-- Dialog para crear/editar cita -->
  <el-dialog v-model="dialogVisible" :title="editing ? 'Editar cita' : 'Nueva cita'" width="700px">
    <el-form :model="form" label-width="140px">
      <el-form-item label="Título" required>
        <el-input v-model="form.title" placeholder="Ingrese un título para la cita" />
      </el-form-item>
      
      <el-form-item label="Tipo de cita" required>
        <el-select v-model="form.appointment_type_id" placeholder="Seleccione el tipo de cita" style="width:100%" @change="calculateEndTime">
          <el-option 
            v-for="type in appointmentTypes" 
            :key="type.id" 
            :label="`${type.name} (${type.duration_minutes} min)`" 
            :value="type.id" 
          />
        </el-select>
      </el-form-item>
      
      <el-form-item label="Usuario responsable" required>
        <el-select v-model="form.user_id" placeholder="Seleccione el usuario responsable" style="width:100%">
          <el-option 
            v-for="user in users" 
            :key="user.id" 
            :label="`${user.first_name} ${user.last_name} (${user.email})`" 
            :value="user.id" 
          />
        </el-select>
      </el-form-item>
      
      <el-row :gutter="20">
        <el-col :span="12">
          <el-form-item label="Fecha / Hora inicio" required>
            <el-date-picker 
              v-model="form.begin" 
              type="datetime" 
              placeholder="Selecciona fecha y hora" 
              style="width:100%" 
              format="DD/MM/YYYY HH:mm"
              value-format="YYYY-MM-DD HH:mm:ss"
              @change="calculateEndTime"
            />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="Fecha / Hora fin" required>
            <el-date-picker 
              v-model="form.end" 
              type="datetime" 
              placeholder="Selecciona fecha y hora" 
              style="width:100%" 
              format="DD/MM/YYYY HH:mm"
              value-format="YYYY-MM-DD HH:mm:ss"
            />
          </el-form-item>
        </el-col>
      </el-row>
      
      <el-form-item label="Estado">
        <el-select v-model="form.status" style="width:100%">
          <el-option label="Pendiente" value="pending" />
          <el-option label="Confirmado" value="confirmed" />
          <el-option label="Completado" value="completed" />
          <el-option label="Cancelado" value="canceled" />
        </el-select>
      </el-form-item>
      
      <el-form-item label="Observaciones">
        <el-input 
          type="textarea" 
          v-model="form.observations" 
          rows="3" 
          placeholder="Ingrese observaciones adicionales (opcional)"
        />
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="dialogVisible = false">Cancelar</el-button>
      <el-button type="primary" @click="() => { console.log('💾 CLICK en Guardar detectado! Editing:', editing); editing ? updateAppointment() : createAppointment(); }">Guardar</el-button>
    </template>
  </el-dialog>
  </section>
</template>

<script setup>
import { ref, onMounted, computed, reactive } from 'vue'
import axios from 'axios'
import dayjs from 'dayjs'
import { Calendar } from '@fullcalendar/core'

// Guatemala está en GMT-6, entonces 6 horas atrás de UTC
const GUATEMALA_OFFSET_HOURS = 6

// Funciones para manejar zona horaria (corregidas)
function toLocalTime(utcDateString) {
  // Convertir de UTC a hora local de Guatemala (UTC - 6 horas)
  return dayjs(utcDateString).subtract(GUATEMALA_OFFSET_HOURS, 'hour')
}

function toUTCTime(localDateString) {
  // Convertir de hora local de Guatemala a UTC (Local + 6 horas)
  return dayjs(localDateString).add(GUATEMALA_OFFSET_HOURS, 'hour').format('YYYY-MM-DD HH:mm:ss')
}
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'
// Importar estilos personalizados para FullCalendar
import '../assets/fullcalendar-custom.css'
import { ElNotification, ElMessageBox } from 'element-plus'
import { Check, Close, CircleCheck, Edit, Delete, Plus, Calendar as CalendarIcon, List } from '@element-plus/icons-vue'

const citasHoyCount = ref(0)
const expedientesRevisionCount = ref(0)
const expedientesAprobadosCount = ref(0)
const expedientesRechazadosCount = ref(0)
const notificacionesHoyCount = ref(0)
const calendarEvents = ref([])
// indicador simple para renderizar FullCalendar solo cuando el componente esté listo
const calendarInitialized = ref(false)
const calendarRef = ref(null)
let calendar = null
const citasBackend = ref([])
// Estado de carga
const isLoading = ref(true)
const dataLoaded = ref(false)
// Notificaciones de hoy
const notificacionesHoy = ref([])
const dismissedNotifications = ref(new Set())
// CRUD dialog
const dialogVisible = ref(false)
const editing = ref(false)
const form = reactive({
  id: null,
  title: '',
  begin: '',
  end: '',
  appointment_type_id: null,
  user_id: null,
  observations: '',
  status: 'pending'
})

// Datos para el formulario
const appointmentTypes = ref([])
const users = ref([])

// Función para calcular fecha de fin automáticamente
function calculateEndTime() {
  if (form.begin && form.appointment_type_id) {
    const selectedType = appointmentTypes.value.find(t => t.id === form.appointment_type_id)
    if (selectedType && selectedType.duration_minutes) {
      const startTime = dayjs(form.begin)
      const endTime = startTime.add(selectedType.duration_minutes, 'minute')
      form.end = endTime.format('YYYY-MM-DD HH:mm:ss')
      
      console.log('⏰ Cálculo automático de hora:')
      console.log('  - Tipo:', selectedType.name)
      console.log('  - Duración:', selectedType.duration_minutes, 'minutos')
      console.log('  - Inicio:', startTime.format('YYYY-MM-DD HH:mm:ss'))
      console.log('  - Fin:', endTime.format('YYYY-MM-DD HH:mm:ss'))
    }
  }
}

function openCreateDialog() {
  console.log('🔧 openCreateDialog ejecutado')
  editing.value = false
  // Establecer fecha y hora por defecto (ahora + 1 hora en Guatemala)
  const now = dayjs()
  const defaultStart = now.add(1, 'hour').startOf('hour')
  const defaultEnd = defaultStart.add(1, 'hour')
  
  console.log('🌍 Fecha por defecto:')
  console.log('  - Ahora:', now.format('YYYY-MM-DD HH:mm:ss'))
  console.log('  - Start:', defaultStart.format('YYYY-MM-DD HH:mm:ss'))
  console.log('  - End:', defaultEnd.format('YYYY-MM-DD HH:mm:ss'))
  
  // Resetear el formulario usando Object.assign
  Object.assign(form, {
    id: null,
    title: '',
    begin: defaultStart.format('YYYY-MM-DD HH:mm:ss'),
    end: defaultEnd.format('YYYY-MM-DD HH:mm:ss'),
    appointment_type_id: null,
    user_id: users.value.length > 0 ? users.value[0].id : null,
    observations: '',
    status: 'pending'
  })
  
  console.log('📋 Formulario reseteado:', {
    editing: editing.value,
    dialogVisible: dialogVisible.value,
    form: form,
    usersCount: users.value.length,
    appointmentTypesCount: appointmentTypes.value.length
  })
  
  dialogVisible.value = true
  console.log('✅ Dialog abierto:', dialogVisible.value)
}

function openEditDialog(cita) {
  console.log('✏️ openEditDialog ejecutado para cita:', cita)
  editing.value = true
  
  console.log('🕐 Datos originales de la cita (usar directo, NO convertir):')
  console.log('  - Begin:', cita.begin)
  console.log('  - End:', cita.end)
  
  // NO CONVERTIR - usar directamente las fechas como vienen de la BD
  Object.assign(form, {
    id: cita.id,
    title: cita.title,
    begin: dayjs(cita.begin).format('YYYY-MM-DD HH:mm:ss'),  // Solo formatear, no convertir
    end: dayjs(cita.end).format('YYYY-MM-DD HH:mm:ss'),      // Solo formatear, no convertir
    appointment_type_id: cita.appointment_type_id,
    user_id: cita.user_id,
    observations: cita.observations,
    status: cita.status
  })
  
  console.log('📝 Formulario llenado (sin conversión de zona horaria):', {
    editing: editing.value,
    form: form
  })
  
  dialogVisible.value = true
  console.log('✅ Dialog editado abierto:', dialogVisible.value)
}

async function createAppointment() {
  console.log('💾 createAppointment ejecutado')
  try {
    console.log('🕐 Datos del formulario (NO CONVERTIR - usar hora Guatemala):')
    console.log('  - Begin:', form.begin)
    console.log('  - End:', form.end)
    
    // NO CONVERTIR - enviar directamente la hora de Guatemala
    const payload = {
      title: form.title,
      begin: form.begin,  // Sin conversión
      end: form.end,      // Sin conversión
      appointment_type_id: form.appointment_type_id,
      user_id: form.user_id,
      observations: form.observations,
      status: form.status
    }
    console.log('📤 Payload (hora Guatemala directa):', payload)
    
    const res = await axios.post('/api/citas', payload)
    console.log('✅ Respuesta del servidor:', res.data)
    
    await reloadCitas()
    dialogVisible.value = false
    ElNotification({ title: 'Cita creada', message: 'La cita fue creada correctamente', type: 'success' })
  } catch (err) {
    console.error('❌ Error creando cita:', err)
    console.error('❌ Respuesta del servidor:', err.response?.data)
    ElNotification({ title: 'Error', message: 'Error creando la cita', type: 'error' })
  }
}

async function updateAppointment() {
  console.log('🔄 updateAppointment ejecutado')
  try {
    console.log('🕐 Datos del formulario (NO CONVERTIR - usar hora Guatemala):')
    console.log('  - Begin:', form.begin)
    console.log('  - End:', form.end)
    
    // NO CONVERTIR - enviar directamente la hora de Guatemala
    const payload = {
      title: form.title,
      begin: form.begin,  // Sin conversión
      end: form.end,      // Sin conversión
      appointment_type_id: form.appointment_type_id,
      user_id: form.user_id,
      observations: form.observations,
      status: form.status
    }
    console.log('📤 Payload (hora Guatemala directa):', payload)
    console.log('🆔 ID de cita:', form.id)
    
    await axios.put(`/api/citas/${form.id}`, payload)
    console.log('✅ Cita actualizada exitosamente')
    
    await reloadCitas()
    dialogVisible.value = false
    ElNotification({ title: 'Cita actualizada', message: 'La cita fue actualizada correctamente', type: 'success' })
  } catch (err) {
    console.error('❌ Error actualizando cita:', err)
    console.error('❌ Respuesta del servidor:', err.response?.data)
    ElNotification({ title: 'Error', message: 'Error al actualizar la cita', type: 'error' })
  }
}

async function deleteAppointment(id) {
  try {
    await axios.delete(`/api/citas/${id}`)
    await reloadCitas()
    ElNotification({ title: 'Cita eliminada', message: 'La cita fue eliminada correctamente', type: 'success' })
  } catch (err) {
    console.error('Error eliminando cita', err)
    ElNotification({ title: 'Error', message: 'Error eliminando la cita', type: 'error' })
  }
}

async function confirmDeleteAppointment(cita) {
  try {
    await ElMessageBox.confirm(
      `¿Está seguro de eliminar la cita "${cita.title}" del ${formatDateTime(cita.begin)}?`,
      'Confirmar eliminación',
      {
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar',
        type: 'warning',
      }
    )
    await deleteAppointment(cita.id)
  } catch (err) {
    // Usuario canceló la operación
    console.log('Eliminación cancelada')
  }
}

async function changeStatus(citaId, status) {
  try {
    await axios.put(`/api/citas/${citaId}`, { status })
    await reloadCitas()
    ElNotification({ title: 'Estado actualizado', message: `Cita marcada como ${translateStatus(status)}`, type: 'success' })
  } catch (err) {
    console.error('Error cambiando estado', err)
    ElNotification({ title: 'Error', message: 'Error al cambiar el estado', type: 'error' })
  }
}

// Cargar tipos de citas disponibles
async function loadAppointmentTypes() {
  try {
    const response = await axios.get('/api/tipos-citas')
    appointmentTypes.value = response.data.data || response.data || []
  } catch (err) {
    console.error('Error cargando tipos de citas:', err)
    ElNotification({ title: 'Error', message: 'Error cargando tipos de citas', type: 'error' })
  }
}

// Cargar usuarios disponibles
async function loadUsers() {
  try {
    const response = await axios.get('/api/usuarios')
    users.value = response.data.data || response.data || []
  } catch (err) {
    console.error('Error cargando usuarios:', err)
    ElNotification({ title: 'Error', message: 'Error cargando usuarios', type: 'error' })
  }
}

async function reloadCitas() {
  try {
    console.log('🔄 Recargando citas desde /api/citas...')
    isLoading.value = true
    
    const r = await axios.get('/api/citas')
    console.log('📡 Respuesta del servidor:', r.data.length, 'citas')
    console.log('📡 Datos recibidos:', r.data)
    
    citasBackend.value = r.data
    console.log('💾 citasBackend actualizado:', citasBackend.value.length, 'citas')
    
    calendarEvents.value = citasBackend.value.map(c => ({ 
      id: c.id, 
      title: c.title || 'Cita', 
      date: c.begin,
      className: `status-${c.status || 'pending'}`,
      extendedProps: {
        status: c.status,
        observations: c.observations
      }
    }))
    console.log('📅 calendarEvents generados:', calendarEvents.value.length, 'eventos')
    console.log('📅 Eventos detalle:', calendarEvents.value)
    
    citasHoyCount.value = citasBackend.value.filter(c => c.begin && dayjs(c.begin).format('YYYY-MM-DD') === dayjs().format('YYYY-MM-DD')).length
    console.log('📊 Citas de hoy:', citasHoyCount.value)
    
    // Actualizar notificaciones de hoy
    updateNotificacionesHoy()
    
    // Actualizar calendario si está inicializado
    if (calendar && calendarInitialized.value) {
      calendar.removeAllEvents()
      calendar.addEventSource(calendarEvents.value)
      console.log('📅 Calendario actualizado con nuevos eventos')
    }
    
    // Marcar datos como cargados
    dataLoaded.value = true
    isLoading.value = false
    console.log('✅ reloadCitas completado')
  } catch (e) {
    console.error('❌ Error recargando citas:', e)
    isLoading.value = false
  }
}

// FullCalendar handlers
async function handleEventClick(info) {
  const ev = info.event
  const id = ev.id || (ev.extendedProps && ev.extendedProps.id)
  const cita = citasBackend.value.find(c => String(c.id) === String(id))
  if (cita) openEditDialog(cita)
}

async function handleEventDrop(info) {
  const ev = info.event
  const id = ev.id || (ev.extendedProps && ev.extendedProps.id)
  const newDate = ev.start ? dayjs(ev.start).toISOString() : null
  if (!id || !newDate) return
  try {
    await axios.patch(`/api/citas/${id}`, { begin: newDate })
    ElNotification({ title: 'Cita movida', message: 'La fecha de la cita fue actualizada', type: 'success' })
    await reloadCitas()
  } catch (err) {
    console.error('Error al mover evento', err)
    ElNotification({ title: 'Error', message: 'No se pudo actualizar la fecha en el backend', type: 'error' })
    info.revert()
  }
}
// filtros y paginación
const viewMode = ref('calendar') // 'calendar' | 'list' - Vista inicial es calendario
const searchQuery = ref('')
const statusFilter = ref('')
const dateFilter = ref(null)
const currentPage = ref(1)
const pageSize = ref(10)

const filteredCitas = computed(() => {
  const q = (searchQuery.value || '').toLowerCase().trim()
  return citasBackend.value.filter(c => {
    // filtro estado
    if (statusFilter.value && c.status !== statusFilter.value) return false
    // filtro fecha (por día)
    if (dateFilter.value) {
      const d = dayjs(c.begin).format('YYYY-MM-DD')
      if (d !== dayjs(dateFilter.value).format('YYYY-MM-DD')) return false
    }
    // filtro búsqueda en title u observations
    if (q) {
      const hay = ((c.title || '') + ' ' + (c.observations || '')).toLowerCase()
      return hay.indexOf(q) !== -1
    }
    return true
  }).sort((a,b) => new Date(a.begin) - new Date(b.begin))
})

const paginatedCitas = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  return filteredCitas.value.slice(start, start + pageSize.value)
})

function clearFilters() {
  searchQuery.value = ''
  statusFilter.value = ''
  dateFilter.value = null
  currentPage.value = 1
}

function onSizeChange(size) {
  pageSize.value = size
  currentPage.value = 1
}

function onPageChange(page) {
  currentPage.value = page
}

// Inicialización con lazy loading solo cuando se necesite el calendario
function setView(mode) {
  console.log('🔄 Cambiando vista a:', mode)
  
  viewMode.value = mode
  
  if (mode === 'calendar') {
    console.log('📅 Iniciando vista calendario...')
    // Resetear estado para permitir reinicialización
    calendarInitialized.value = false
    // Usar timeout para asegurar que el DOM esté listo
    setTimeout(() => {
      initializeCalendar()
    }, 100)
  } else if (mode === 'list') {
    console.log('📋 Cambiando a vista lista...')
    // Destruir calendario si existe para liberar memoria
    if (calendar) {
      console.log('🗑️ Destruyendo calendario al cambiar a lista...')
      calendar.destroy()
      calendar = null
      calendarInitialized.value = false
    }
  }
}

async function initializeCalendar() {
  try {
    console.log('🚀 Iniciando calendario con API nativa...')
    
    // Verificar que el elemento DOM esté disponible
    if (!calendarRef.value) {
      console.log('⏳ Esperando elemento DOM...')
      setTimeout(() => initializeCalendar(), 100)
      return
    }
    
    // Cargar datos si no están cargados
    if (citasBackend.value.length === 0) {
      console.log('📝 Cargando datos de citas...')
      await reloadCitas()
    }
    
    // Destruir calendario existente si hay uno
    if (calendar) {
      console.log('🗑️ Destruyendo calendario anterior...')
      calendar.destroy()
      calendar = null
    }
    
    console.log('🔨 Creando nuevo calendario...')
    
    // Crear calendario manualmente
    calendar = new Calendar(calendarRef.value, {
      plugins: [dayGridPlugin, interactionPlugin],
      initialView: 'dayGridMonth',
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: ''
      },
      events: calendarEvents.value,
      editable: true,
      selectable: true,
      eventClick: function(info) {
        console.log('Evento clickeado:', info.event.id)
        const cita = citasBackend.value.find(c => String(c.id) === String(info.event.id))
        if (cita) openEditDialog(cita)
      },
      eventDrop: function(info) {
        const id = info.event.id
        const newDate = dayjs(info.event.start).toISOString()
        
        axios.patch(`/api/citas/${id}`, { begin: newDate })
          .then(() => {
            ElNotification({ title: 'Cita movida', message: 'Fecha actualizada', type: 'success' })
            reloadCitas()
          })
          .catch(err => {
            console.error('Error moviendo evento:', err)
            ElNotification({ title: 'Error', message: 'No se pudo actualizar', type: 'error' })
            info.revert()
          })
      }
    })
    
    calendar.render()
    calendarInitialized.value = true
    console.log('✅ Calendario inicializado correctamente')
    
  } catch (error) {
    console.error('❌ Error inicializando calendario:', error)
    ElNotification({ 
      title: 'Error', 
      message: 'No se pudo cargar el calendario. Usando vista de lista.', 
      type: 'warning' 
    })
    viewMode.value = 'list'
  }
}

onMounted(async () => {
  try {
    console.log('🚀 Componente DashboardView montado')
    console.log('📦 Plugins importados:')
    console.log('  - dayGridPlugin:', dayGridPlugin)
    console.log('  - interactionPlugin:', interactionPlugin)
    
    // Cargar datos iniciales INMEDIATAMENTE
    console.log('📊 Cargando datos iniciales...')
    isLoading.value = true
    await reloadCitas()
    const hoy = dayjs().format('YYYY-MM-DD')

    // Notificar al usuario si tiene citas hoy
    if (citasHoyCount.value && citasHoyCount.value > 0) {
      console.log('🔔 Mostrando notificación de citas de hoy:', citasHoyCount.value)
      ElNotification({ title: 'Recordatorio', message: `Tienes ${citasHoyCount.value} cita(s) para hoy`, type: 'info' })
    } else {
      console.log('📅 No hay citas para hoy')
    }

    // Expedientes (defensivo: el backend puede devolver un objeto con `data` o un array directo)
    try {
      const expedientesRes = await axios.get('/api/expedientes')
      const expedientesData = Array.isArray(expedientesRes.data) ? expedientesRes.data : (expedientesRes.data && Array.isArray(expedientesRes.data.data) ? expedientesRes.data.data : [])
      expedientesRevisionCount.value = expedientesData.filter(e => e.estado === 'revision' || e.estado === 'en_revision').length
      expedientesAprobadosCount.value = expedientesData.filter(e => e.estado === 'aprobado').length
      expedientesRechazadosCount.value = expedientesData.filter(e => e.estado === 'rechazado').length
    } catch (e) {
      console.warn('Error cargando expedientes:', e)
    }

    // Notificaciones
    try {
      const notificacionesRes = await axios.get('/api/notificaciones')
      const notifs = Array.isArray(notificacionesRes.data) ? notificacionesRes.data : (notificacionesRes.data && Array.isArray(notificacionesRes.data.data) ? notificacionesRes.data.data : [])
      notificacionesHoyCount.value = notifs.filter(n => {
        const fecha = (n.fecha || n.created_at || '')
        return fecha && dayjs(fecha).format('YYYY-MM-DD') === hoy
      }).length
    } catch (e) {
      console.warn('Error cargando notificaciones:', e)
    }

    // Cargar datos para el formulario de citas
    console.log('📋 Cargando tipos de citas y usuarios...')
    await Promise.all([
      loadAppointmentTypes(),
      loadUsers()
    ])

    console.log('✅ Datos iniciales cargados correctamente')
    console.log('📊 Resumen final:')
    console.log('  - Citas cargadas:', citasBackend.value.length)
    console.log('  - Eventos calendario:', calendarEvents.value.length)
    console.log('  - Tipos de citas:', appointmentTypes.value.length)
    console.log('  - Usuarios:', users.value.length)
    console.log('  - Vista actual:', viewMode.value)
    console.log('  - Calendario inicializado:', calendarInitialized.value)
    
    // Como la vista inicial es calendario, inicializarlo automáticamente
    console.log('📅 Inicializando calendario como vista por defecto...')
    setTimeout(() => {
      initializeCalendar()
    }, 200)
    
  } catch (e) {
    console.error('❌ Error al obtener datos del backend:', e)
  }
})

function formatDateTime(dt) {
  if (!dt) return ''
  // NO convertir - mostrar directamente la hora como está en la BD
  return dayjs(dt).format('DD/MM/YYYY HH:mm')
}

function translateStatus(s) {
  if (!s) return ''
  const map = {
    pending: 'Pendiente',
    completed: 'Completado',
    canceled: 'Cancelado',
    confirmed: 'Confirmado'
  }
  return map[s] || s
}

// Funciones para notificaciones
function formatTime(dt) {
  if (!dt) return ''
  return dayjs(dt).format('HH:mm')
}

function dismissNotification(id) {
  dismissedNotifications.value.add(id)
  updateNotificacionesHoy()
}

function dismissAllNotifications() {
  notificacionesHoy.value.forEach(notif => {
    dismissedNotifications.value.add(notif.id)
  })
  updateNotificacionesHoy()
}

function updateNotificacionesHoy() {
  const hoy = dayjs().format('YYYY-MM-DD')
  notificacionesHoy.value = citasBackend.value
    .filter(c => {
      if (!c.begin) return false
      if (dismissedNotifications.value.has(c.id)) return false
      return dayjs(c.begin).format('YYYY-MM-DD') === hoy
    })
    .sort((a, b) => dayjs(a.begin).diff(dayjs(b.begin)))
}
</script>


// ...existing code...
<style scoped>
.dashboard-content {
  flex: 1;
  padding: 48px 40px;
}
.dashboard-title {
  font-size: 2.1rem;
  color: #3d5c2c;
  font-weight: 700;
  margin-bottom: 32px;
  letter-spacing: 1px;
  text-align: center;
}
.dashboard-cards {
  display: flex;
  gap: 2rem;
  flex-wrap: wrap;
  justify-content: center;
  margin-bottom: 32px;
}
.dashboard-card {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  text-align: center;
  border: none;
  min-width: 180px;
  min-height: 120px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  transition: box-shadow 0.2s;
}
.dashboard-card.citas-hoy { border-top: 4px solid #6b7a4b; }
.dashboard-card.revision { border-top: 4px solid #b6a800; }
.dashboard-card.aprobados { border-top: 4px solid #3d5c2c; }
.dashboard-card.rechazados { border-top: 4px solid #b64b4b; }
.dashboard-card.notificaciones { border-top: 4px solid #4b7ab6; }
.dashboard-card:hover {
  box-shadow: 0 4px 16px rgba(107, 122, 75, 0.12);
}
.card-title {
  color: #6b7a4b;
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 8px;
  letter-spacing: 0.5px;
}
.card-value {
  color: #3d5c2c;
  font-size: 2.2rem;
  font-weight: bold;
}
.dashboard-calendar {
  margin: 0 auto;
  max-width: 900px;
  background: #f8f9f8;
  border-radius: 16px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  padding: 24px 16px 32px 16px;
}

.calendar-header {
  display:flex;
  justify-content:space-between;
  align-items:center;
}
.view-toggle el-button {
  margin-left:6px;
}
.filters-bar {
  display:flex;
  align-items:center;
  gap:8px;
  margin-bottom:12px;
  flex-wrap:wrap;
}
.table-footer {
  display:flex;
  justify-content:flex-end;
  margin-top:12px;
}

.events-table table {
  width: 100%;
  border-collapse: collapse;
}
/* Minimal FullCalendar styling as fallback in case package CSS isn't available. */
.calendar-wrapper .fc {
  font-family: Inter, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
  background: #ffffff;
  border-radius: 10px;
  padding: 8px;
  box-shadow: 0 6px 18px rgba(28,45,25,0.04);
}
.calendar-wrapper .fc .fc-toolbar-title {
  color: #2f4b2a;
  font-weight: 700;
}
.calendar-wrapper .fc .fc-daygrid-day-frame {
  padding: 6px;
}
.calendar-wrapper .fc .fc-daygrid-event {
  background: #4caf50;
  color: #fff;
  border-radius: 6px;
  padding: 2px 6px;
  font-size: 0.8rem;
  font-weight: 700;
}
.events-table th, .events-table td {
  padding: 10px 12px;
  border-bottom: 1px solid #e6e6e6;
  text-align: left;
}
.events-table thead th {
  background: transparent;
  color: #3d5c2c;
  font-weight: 600;
}

.table-card {
  background: #fff;
  border-radius: 12px;
  padding: 12px;
  box-shadow: 0 6px 18px rgba(28,45,25,0.06);
}
.events-table tbody tr:nth-child(odd) {
  background: #ffffff;
}
.events-table tbody tr:nth-child(even) {
  background: #fbfcfb;
}
.cell-date {
  color: #2f4b2a;
  font-weight: 600;
  white-space: nowrap;
}
.cell-title {
  color: #3d5c2c;
  font-weight: 600;
}
.cell-obs {
  color: #5b6b5b;
  max-width: 520px;
  white-space: normal;
}
.status-badge {
  display: inline-block;
  padding: 6px 10px;
  border-radius: 999px;
  color: #fff;
  font-weight: 700;
  text-transform: capitalize;
  font-size: 0.85rem;
}
.status-badge.pending { background: #f0ad4e; color: #2b2b2b }
.status-badge.confirmed { background: #4caf50 }
.status-badge.completed { background: #2e7d32 }
.status-badge.canceled { background: #e04b4b }

/* Panel de notificaciones */
.notifications-panel {
  background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
  border: 1px solid #f4d03f;
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 24px;
  box-shadow: 0 2px 8px rgba(244, 208, 63, 0.15);
}

.notification-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.notification-header h3 {
  margin: 0;
  color: #856404;
  font-size: 1.1rem;
  font-weight: 600;
}

.notification-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.notification-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: rgba(255, 255, 255, 0.7);
  padding: 8px 12px;
  border-radius: 8px;
  border-left: 4px solid #f39c12;
}

.notification-content {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.notification-content strong {
  color: #2c3e50;
  font-size: 0.95rem;
}

.notification-time {
  color: #856404;
  font-size: 0.85rem;
  font-weight: 600;
}

/* Indicador de carga */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 6px 18px rgba(28,45,25,0.06);
}

.loading-spinner {
  font-size: 2rem;
  animation: spin 1s linear infinite;
}

.loading-text {
  margin-top: 12px;
  color: #6b7a4b;
  font-weight: 600;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 900px) {
  .events-table table, .events-table thead, .events-table tbody, .events-table th, .events-table td, .events-table tr {
    display: block;
  }
  .events-table thead tr { display: none; }
  .events-table tbody tr { margin-bottom: 12px; border-bottom: 1px solid #eee }
  .events-table td { padding-left: 50%; position: relative }
  .events-table td::before {
    position: absolute; left: 12px; top: 10px; width: 45%; white-space: nowrap; font-weight:600; color:#3d5c2c
  }
  .cell-date::before { content: 'Fecha / Hora' }
  .cell-title::before { content: 'Título' }  
  .cell-status::before { content: 'Estado' }
  .cell-obs::before { content: 'Observaciones' }
  
  .notification-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  
  .cell-actions .action-buttons {
    flex-direction: column;
    gap: 4px;
  }
  
  .cell-actions::before { 
    content: 'Acciones' 
  }
}

/* Header de citas mejorado */
.calendar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding: 15px 20px;
  background: linear-gradient(135deg, #f8fffe 0%, #e8f5e8 100%);
  border-radius: 12px;
  border: 1px solid #d4e6d4;
  box-shadow: 0 2px 8px rgba(61, 92, 44, 0.1);
}

.header-left h2 {
  color: #3d5c2c;
  font-size: 1.5rem;
  font-weight: 700;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 15px;
}

.view-toggle {
  display: flex;
  gap: 8px;
}

/* Barra de filtros mejorada */
.filters-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding: 15px;
  background: #f9faf9;
  border-radius: 8px;
  border: 1px solid #e0e6e0;
}

.filters-left {
  display: flex;
  align-items: center;
}

.filters-right {
  display: flex;
  align-items: center;
}

/* Estilos para botones de acción */
.cell-actions {
  min-width: 280px;
}

.action-buttons {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
}

.el-button-group .el-button {
  padding: 6px 8px;
}

.el-button-group .el-button .el-icon {
  font-size: 14px;
}

/* Mejorar el aspecto de los selectores de estado */
.el-select .el-input__inner {
  font-size: 12px;
}

/* Botón flotante para nueva cita */
.floating-add-btn {
  position: absolute;
  bottom: 20px;
  right: 20px;
  z-index: 1000;
  box-shadow: 0 4px 16px rgba(61, 92, 44, 0.3);
  border: none;
  width: 56px;
  height: 56px;
  transition: all 0.3s ease;
}

.floating-add-btn:hover {
  transform: scale(1.1);
  box-shadow: 0 6px 20px rgba(61, 92, 44, 0.4);
}

/* Responsive para header */
@media (max-width: 768px) {
  .calendar-header {
    flex-direction: column;
    gap: 15px;
  }
  
  .header-right {
    width: 100%;
    justify-content: center;
  }
  
  .floating-add-btn {
    bottom: 15px;
    right: 15px;
    width: 50px;
    height: 50px;
  }
  
  .filters-bar {
    flex-direction: column;
    gap: 15px;
  }
  
  .filters-left {
    flex-direction: column;
    gap: 10px;
    width: 100%;
  }
  
  .filters-left .el-input {
    width: 100% !important;
  }
}

</style>

