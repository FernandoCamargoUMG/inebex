# 🚀 INEBEX API Collection - Guía de Uso

## 📥 Importar la Colección

1. **Abre Postman**
2. **Importa la colección**:
   - Haz clic en "Import" 
   - Selecciona el archivo `INEBX_API_Collection.postman_collection.json`
   - La colección aparecerá con todos los endpoints organizados

## 🌐 Variables de Entorno

La colección usa la variable `{{base_url}}` que está configurada por defecto como:
```
http://127.0.0.1:8000
```

Si tu servidor Laravel está en otro puerto, puedes:
1. Crear un nuevo environment en Postman
2. Agregar la variable `base_url` con tu URL

## 📋 Estructura de la Colección

### 👥 Usuarios (5 endpoints)
- `GET /api/usuarios` - Listar todos los usuarios
- `POST /api/usuarios` - Crear nuevo usuario
- `GET /api/usuarios/{id}` - Ver usuario específico
- `PUT /api/usuarios/{id}` - Actualizar usuario
- `DELETE /api/usuarios/{id}` - Eliminar usuario

### 🛡️ Roles (5 endpoints)
- CRUD completo para roles del sistema

### 👤 Perfiles (5 endpoints)
- CRUD completo para perfiles profesionales

### 📋 Expedientes (6 endpoints)
- CRUD completo + filtros por estado
- `GET /api/expedientes/estado/{status}` - Filtrar por estado

### 📅 Citas (6 endpoints)
- CRUD completo + vista de citas de hoy
- `GET /api/citas/hoy` - Citas del día actual

### 📄 Documentos (8 endpoints)
- CRUD completo + funciones especiales
- `POST /api/documentos` - Subir archivo (multipart/form-data)
- `GET /api/documentos/{id}/descargar` - Descargar archivo
- `GET /api/documentos/expediente/{record_id}` - Documentos por expediente

### 🔧 Tipos de Cita (5 endpoints)
- CRUD completo para tipos de citas

### 📋 Tipos de Documento (5 endpoints)
- CRUD completo para tipos de documentos

### 🚨 Incidencias (7 endpoints)
- CRUD completo + filtros especiales
- `GET /api/incidencias/prioridad/{priority}` - Por prioridad
- `GET /api/incidencias/vencidas` - Incidencias vencidas

### 🔔 Notificaciones (7 endpoints)
- CRUD completo + funciones especiales
- `GET /api/notificaciones/usuario/{user_id}/no-leidas` - Contar no leídas
- `PUT /api/notificaciones/{id}/leer` - Marcar como leída
- `PUT /api/notificaciones/marcar-todas-leidas` - Marcar todas como leídas

### 🧪 Pruebas (1 endpoint)
- `GET /api/test` - Endpoint de prueba

## 🎯 Ejemplos de Uso

### 1. Crear un Usuario
```json
POST /api/usuarios
{
  "first_name": "Juan",
  "last_name": "Pérez", 
  "email": "juan@ejemplo.com",
  "password": "password123",
  "phone": "+502 1111-2222",
  "address": "Guatemala City, Guatemala",
  "role_id": 5,
  "status": "active"
}
```

### 2. Subir un Documento
```
POST /api/documentos
Content-Type: multipart/form-data

record_id: 1
document_type_id: 1
title: "Cédula de Identidad"
description: "Documento de identificación"
file: [SELECCIONAR ARCHIVO PDF]
```

### 3. Crear una Cita
```json
POST /api/citas
{
  "user_id": 6,
  "appointment_type_id": 1,
  "title": "Consulta Inicial",
  "begin": "2025-11-05 10:00:00",
  "end": "2025-11-05 11:00:00",
  "status": "pending",
  "observations": "Primera cita del cliente"
}
```

## 🔍 Estados Disponibles

### Estados de Expedientes:
- `created` - Creado
- `under_review` - En revisión
- `active` - Activo
- `completed` - Completado
- `cancelled` - Cancelado

### Estados de Citas:
- `pending` - Pendiente
- `confirmed` - Confirmada
- `completed` - Completada
- `cancelled` - Cancelada

### Prioridades de Incidencias:
- `low` - Baja
- `medium` - Media
- `high` - Alta
- `critical` - Crítica

### Estados de Incidencias:
- `open` - Abierta
- `in_progress` - En progreso
- `resolved` - Resuelta
- `closed` - Cerrada

## 🗂️ IDs de Datos de Prueba

Con los seeders, tienes disponibles:
- **Usuarios**: IDs 1-10 (1-5: Administradores, 6-10: Clientes)
- **Roles**: IDs 1-5
- **Perfiles**: IDs 1-5
- **Tipos de Cita**: IDs 1-5
- **Tipos de Documento**: IDs 1-5
- **Expedientes**: IDs 1-5
- **Citas**: IDs 1-10
- **Documentos**: Dependiendo de los que subas
- **Incidencias**: IDs 1-5
- **Notificaciones**: IDs 1-10

## 🎨 Organización Visual

La colección está organizada con emojis para fácil navegación:
- 👥 Usuarios
- 🛡️ Roles  
- 👤 Perfiles
- 📋 Expedientes
- 📅 Citas
- 📄 Documentos
- 🔧 Tipos de Cita
- 📋 Tipos de Documento
- 🚨 Incidencias
- 🔔 Notificaciones
- 🧪 Pruebas

¡Ya tienes todo listo para probar tu API de INEBX! 🚀