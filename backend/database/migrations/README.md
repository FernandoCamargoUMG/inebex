# Traducciones de Base de Datos - Español a Inglés

Este archivo documenta todas las traducciones de nombres de tablas y campos del español al inglés para mantener consistencia en la base de datos.

## Tablas

| Español | Inglés | Descripción |
|---------|--------|-------------|
| `roles` | `roles` | Roles de usuario (ya en inglés) |
| `tipos_citas` | `appointment_types` | Tipos de citas |
| `tipos_documentos` | `document_types` | Tipos de documentos |
| `perfiles` | `profiles` | Perfiles de usuario |
| `expedientes` | `records` | Expedientes/Registros |
| `citas` | `appointments` | Citas |
| `incidencias` | `incidents` | Incidencias/Reportes |
| `documentos` | `documents` | Documentos |
| `notificaciones` | `notifications` | Notificaciones |

## Campos por Tabla

### `appointment_types` (antes `tipos_citas`)
| Español | Inglés | Tipo |
|---------|--------|------|
| `name` | `name` | string (ya en inglés) |
| `duration_minutes` | `duration_minutes` | integer (ya en inglés) |
| `active` | `active` | boolean (ya en inglés) |

### `document_types` (antes `tipos_documentos`)
| Español | Inglés | Tipo |
|---------|--------|------|
| `name` | `name` | string (ya en inglés) |
| `active` | `active` | boolean (ya en inglés) |

### `profiles` (antes `perfiles`)
| Español | Inglés | Tipo |
|---------|--------|------|
| `nombre` | `name` | string |
| `activo` | `active` | boolean |

### `records` (antes `expedientes`)
| Español | Inglés | Tipo |
|---------|--------|------|
| `user_id` | `user_id` | foreign (ya en inglés) |
| `perfil_id` | `profile_id` | foreign |
| `estado` | `status` | enum |
| `descripcion` | `observations` | text |

### `appointments` (antes `citas`)
| Español | Inglés | Tipo |
|---------|--------|------|
| `user_id` | `user_id` | foreign (ya en inglés) |
| `tipo_cita_id` | `appointment_type_id` | foreign |
| `titulo` | `title` | string |
| `begin` | `begin` | dateTime (ya en inglés) |
| `end` | `end` | dateTime (ya en inglés) |
| `status` | `status` | enum (ya en inglés) |
| `observaciones` | `observations` | text |

### `incidents` (antes `incidencias`)
| Español | Inglés | Tipo |
|---------|--------|------|
| `reported_by_user_id` | `reported_by_user_id` | foreign (ya en inglés) |
| `type` | `type` | string (ya en inglés) |
| `description` | `description` | text (ya en inglés) |
| `status` | `status` | enum (ya en inglés) |

### `documents` (antes `documentos`)
| Español | Inglés | Tipo |
|---------|--------|------|
| `expediente_id` | `record_id` | foreign |
| `document_type_id` | `document_type_id` | foreign (ya en inglés) |
| `original_name` | `original_name` | string (ya en inglés) |
| `path` | `path` | string (ya en inglés) |
| `size_bytes` | `size_bytes` | bigInteger (ya en inglés) |
| `format` | `format` | enum (ya en inglés) |
| `uploaded_by_user_id` | `uploaded_by_user_id` | foreign (ya en inglés) |

### `notifications` (antes `notificaciones`)
| Español | Inglés | Tipo |
|---------|--------|------|
| `user_id` | `user_id` | foreign (ya en inglés) |
| `title` | `title` | string (ya en inglés) |
| `message` | `message` | text (ya en inglés) |
| `read` | `read` | boolean (ya en inglés) |
| `sent_at` | `sent_at` | timestamp (ya en inglés) |

## Valores de Enum

### Status de Records (antes estado de expedientes)
| Español | Inglés |
|---------|--------|
| `creado` | `created` |
| `en_revision` | `under_review` |
| `aprobado` | `approved` |
| `rechazado` | `rejected` |

### Status de Appointments (antes estado de citas)
| Español | Inglés |
|---------|--------|
| `pending` | `pending` (ya en inglés) |
| `completed` | `completed` (ya en inglés) |
| `canceled` | `canceled` (ya en inglés) |
| `confirmed` | `confirmed` (ya en inglés) |

### Status de Incidents (antes estado de incidencias)
| Español | Inglés |
|---------|--------|
| `pendiente` | `pending` |
| `resuelto` | `resolved` |

## Notas
- Todas las tablas usan convenciones de Laravel (plural, snake_case)
- Los campos foreign key siguen el patrón `table_id` 
- Los timestamps `created_at` y `updated_at` son estándar de Laravel
- Los campos boolean usan valores `true`/`false`