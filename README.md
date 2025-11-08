# Sistema Integral de Gestión Académica y Administrativa

## 📘 Descripción General

**INEBEX** es un sistema interno administrativo que permite gestionar citas y expedientes digitales dentro de una institución.
El sistema ayuda a mantener organizadas las citas, generar recordatorios automáticos y almacenar documentos PDF asociados a cada expediente.

## 🎯 Estado Actual del Proyecto - ✅ FUNCIONANDO

### ✅ APIs DISPONIBLES Y FUNCIONANDO
- **GET** `http://localhost:8000/api/test` - Verificación de API ✅
- **GET** `http://localhost:8000/api/usuarios` - Lista de usuarios ✅  
- **GET** `http://localhost:8000/api/roles` - Lista de roles ✅
- **GET** `http://localhost:8000/api/citas` - Lista de citas ✅
- **CRUD Completo**: Usuarios, Roles y Citas con operaciones CREATE, READ, UPDATE, DELETE ✅

## 🧩 Módulos Principales

| Módulo                  | Descripción                                        |
| ----------------------- | -------------------------------------------------- |
| 👤 **Usuarios**         | Control de personal y roles administrativos.       |
| 📅 **Citas**            | Registro, actualización y recordatorio de citas.   |
| 🗂️ **Expedientes**     | Creación y control de expedientes institucionales. |
| 📄 **Documentos (PDF)** | Subida y consulta de archivos PDF.                 |
| 🔔 **Notificaciones**   | Recordatorios automáticos de citas.                |

## ⚙️ Tecnologías Utilizadas

| Capa                       | Tecnología                        |
| -------------------------- | --------------------------------- |
| **Backend**                | Laravel 12                        |
| **Frontend**               | React                             |
| **Base de datos**          | MySQL 10+                         |
| **Contraseñas**            | Cifrado con MD5                   |
| **Almacenamiento**         | `/storage/app/public/expedientes` |
| **Programación de tareas** | Laravel Scheduler                 |

## 🧱 Estructura del Backend (Laravel)

```
app/
 ├── Models/
 │   ├── Usuario.php
 │   ├── Rol.php
 │   ├── Cita.php
 │   ├── Expediente.php
 │   ├── Documento.php
 │   └── Notificacion.php
 │
 ├── Http/
 │   ├── Controllers/
 │   │   ├── UsuarioController.php
 │   │   ├── CitaController.php
 │   │   ├── ExpedienteController.php
 │   │   ├── DocumentoController.php
 │   │   └── NotificacionController.php
 │   └── Requests/
 │
 ├── Console/
 │   └── Commands/RecordatorioCitasDiarias.php
 │
 └── database/
     ├── migrations/
     ├── seeders/
     └── factories/
```

## 🔐 Contraseñas con MD5

> ⚠️ **Advertencia:** Solo para uso local o de demostración.

**Cuando crees o actualices usuarios, las contraseñas se guardan así:**
```php
$usuario->password = md5($request->password);
```

**Y cuando compares:**
```php
if ($usuario->password === md5($request->password)) {
    // contraseña válida
}
```

---

# 🌐 API Endpoints

> **Nota:** Todos los endpoints están bajo `/api` y no requieren token ni middleware de autenticación.

## 👤 Usuarios

| Método   | Endpoint             | Descripción                            |
| -------- | -------------------- | -------------------------------------- |
| `GET`    | `/api/usuarios`      | Listar todos los usuarios.             |
| `GET`    | `/api/usuarios/{id}` | Mostrar usuario por ID.                |
| `POST`   | `/api/usuarios`      | Crear usuario (usa md5 para password). |
| `PUT`    | `/api/usuarios/{id}` | Editar usuario.                        |
| `DELETE` | `/api/usuarios/{id}` | Eliminar usuario.                      |

**Ejemplo de creación:**
```json
{
  "nombre": "Nataly",
  "correo": "admin@inebex.com",
  "password": "admin123",
  "rol_id": 1
}
```

## 📅 Citas

| Método   | Endpoint          | Descripción                        |
| -------- | ----------------- | ---------------------------------- |
| `GET`    | `/api/citas`      | Listar citas.                      |
| `GET`    | `/api/citas/hoy`  | Listar citas programadas para hoy. |
| `GET`    | `/api/citas/{id}` | Detalle de cita.                   |
| `POST`   | `/api/citas`      | Crear nueva cita.                  |
| `PUT`    | `/api/citas/{id}` | Actualizar estado u horario.       |
| `DELETE` | `/api/citas/{id}` | Eliminar cita.                     |

**Ejemplo de creación:**
```json
{
  "usuario_id": 2,
  "tipo_cita_id": 1,
  "titulo": "Revisión de expediente",
  "inicio": "2025-11-07 10:00:00",
  "fin": "2025-11-07 10:30:00",
  "estado": "pendiente"
}
```

## 🗂️ Expedientes

| Método   | Endpoint                | Descripción                 |
| -------- | ----------------------- | --------------------------- |
| `GET`    | `/api/expedientes`      | Listar expedientes.         |
| `GET`    | `/api/expedientes/{id}` | Ver expediente.             |
| `POST`   | `/api/expedientes`      | Crear expediente.           |
| `PUT`    | `/api/expedientes/{id}` | Actualizar estado o perfil. |
| `DELETE` | `/api/expedientes/{id}` | Eliminar expediente.        |

**Ejemplo de creación:**
```json
{
  "usuario_id": 3,
  "perfil_id": 1,
  "estado": "en_revision",
  "observaciones": "Faltan documentos"
}
```

## 📄 Documentos (solo PDF)

| Método   | Endpoint                          | Descripción                       |
| -------- | --------------------------------- | --------------------------------- |
| `POST`   | `/api/documentos`                 | Subir documento PDF.              |
| `GET`    | `/api/documentos/{expediente_id}` | Listar documentos por expediente. |
| `DELETE` | `/api/documentos/{id}`            | Eliminar documento.               |

**Validación:**
```php
$request->validate([
  'archivo' => 'required|mimes:pdf|max:5120',
]);
```

## 🔔 Notificaciones

| Método   | Endpoint                        | Descripción                      |
| -------- | ------------------------------- | -------------------------------- |
| `GET`    | `/api/notificaciones`           | Listar todas las notificaciones. |
| `PUT`    | `/api/notificaciones/{id}/leer` | Marcar como leída.               |
| `DELETE` | `/api/notificaciones/{id}`      | Eliminar notificación.           |

---

# 🕒 Recordatorios Automáticos de Citas

**Archivo:** `app/Console/Commands/RecordatorioCitasDiarias.php`

```php
public function handle()
{
    $hoy = now()->toDateString();

    $citas = Cita::whereDate('inicio', $hoy)
        ->where('estado', 'pendiente')
        ->get();

    foreach ($citas as $cita) {
        Notificacion::create([
            'usuario_id' => $cita->usuario_id,
            'titulo' => 'Cita programada para hoy',
            'mensaje' => "Tienes una cita a las {$cita->inicio->format('H:i')}.",
        ]);
    }

    $this->info('Recordatorios generados correctamente.');
}
```

**Programación (Kernel.php):**
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('citas:recordatorio')->dailyAt('07:00');
}
```

---

# ⚙️ Configuración del Frontend (React)

## Instalación de dependencias:
```bash
npm install axios react-router-dom react-toastify @fullcalendar/react @fullcalendar/daygrid
```

| Librería                  | Uso                           |
| ------------------------- | ----------------------------- |
| **axios**                 | Peticiones HTTP hacia Laravel |
| **react-router-dom**      | Navegación entre vistas       |
| **react-toastify**        | Mensajes visuales             |
| **@fullcalendar/react**   | Calendario de citas           |
| **@fullcalendar/daygrid** | Vista mensual/semanal         |

## Ejemplo de componente para mostrar citas:

```jsx
import FullCalendar from '@fullcalendar/react';
import dayGridPlugin from '@fullcalendar/daygrid';
import axios from 'axios';
import { useEffect, useState } from 'react';

function CitasCalendar() {
  const [events, setEvents] = useState([]);

  useEffect(() => {
    axios.get('/api/citas').then(res => setEvents(res.data));
  }, []);

  return (
    <FullCalendar
      plugins={[dayGridPlugin]}
      initialView="dayGridMonth"
      events={events.map(cita => ({
        title: cita.titulo,
        date: cita.inicio
      }))}
    />
  );
}
```

---

# 🧰 Comandos Útiles

| Comando                      | Descripción                           |
| ---------------------------- | ------------------------------------- |
| `php artisan migrate --seed` | Crear base de datos y datos iniciales |
| `php artisan serve`          | Iniciar servidor Laravel              |
| `php artisan schedule:work`  | Ejecutar recordatorios                |
| `npm run dev`                | Iniciar React frontend                |

---

# ✅ Conclusión

**simplificado:**

- ✅ No requiere autenticación
- ✅ Maneja usuarios con contraseñas MD5  
- ✅ Gestiona citas, expedientes y PDFs
- ✅ Genera recordatorios automáticos
- ✅ Es totalmente compatible con React