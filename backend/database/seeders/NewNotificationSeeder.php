<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NewNotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $notifications = [
            [
                'user_id' => 6, // Patricia Morales
                'title' => 'Documentos Recibidos',
                'message' => 'Hemos recibido sus documentos para el expediente EXP-2025-001. Procederemos con la revisión.',
                'type' => 'info',
                'is_read' => false,
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ],
            [
                'user_id' => 7, // Roberto Pérez
                'title' => 'Cita Confirmada',
                'message' => 'Su cita para revisión de contratos ha sido confirmada para hoy a las 13:30.',
                'type' => 'reminder',
                'is_read' => false,
                'created_at' => now()->subHours(1),
                'updated_at' => now()->subHours(1),
            ],
            [
                'user_id' => 8, // Carmen Jiménez
                'title' => 'Expediente Completado',
                'message' => 'Su expediente académico EXP-2025-003 ha sido completado exitosamente.',
                'type' => 'success',
                'is_read' => true,
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(1),
            ],
            [
                'user_id' => 9, // Diego Vásquez
                'title' => 'Documentos Pendientes',
                'message' => 'Faltan documentos fiscales para completar su expediente EXP-2025-004.',
                'type' => 'warning',
                'is_read' => false,
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
            [
                'user_id' => 10, // Elena Castillo
                'title' => 'Revisión en Proceso',
                'message' => 'Su expediente de microempresa está siendo revisado por nuestro equipo legal.',
                'type' => 'info',
                'is_read' => false,
                'created_at' => now()->subDays(4),
                'updated_at' => now()->subDays(4),
            ],
            [
                'user_id' => 4, // María Rodríguez (Empleado)
                'title' => 'Nueva Asignación',
                'message' => 'Se le ha asignado la revisión del expediente EXP-2025-001.',
                'type' => 'info',
                'is_read' => true,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(2),
            ],
            [
                'user_id' => 5, // Luis Hernández (Empleado)
                'title' => 'Recordatorio de Cita',
                'message' => 'Tiene una cita programada mañana con Roberto Pérez a las 10:30.',
                'type' => 'reminder',
                'is_read' => false,
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subHours(3),
            ],
            [
                'user_id' => 3, // Carlos López (Manager)
                'title' => 'Aprobación Requerida',
                'message' => 'El expediente EXP-2025-004 requiere su aprobación para continuar.',
                'type' => 'warning',
                'is_read' => false,
                'created_at' => now()->subHours(6),
                'updated_at' => now()->subHours(6),
            ],
        ];

        DB::table('notifications')->insert($notifications);
    }
}