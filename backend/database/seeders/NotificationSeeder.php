<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $notifications = [
            [
                'user_id' => 6, // Patricia Morales
                'title' => 'Cita Confirmada',
                'message' => 'Su cita para Consulta Inicial ha sido confirmada para mañana a las 10:00 AM.',
                'type' => 'info',
                'is_read' => false,
                'sent_at' => now()->subHours(2),
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ],
            [
                'user_id' => 7, // Roberto Pérez
                'title' => 'Documento Requerido',
                'message' => 'Por favor suba su documento de identidad para completar su expediente.',
                'type' => 'warning',
                'is_read' => false,
                'sent_at' => now()->subHours(5),
                'created_at' => now()->subHours(5),
                'updated_at' => now()->subHours(5),
            ],
            [
                'user_id' => 4, // María Rodríguez
                'title' => 'Nueva Incidencia Asignada',
                'message' => 'Se le ha asignado un nuevo problema técnico para resolución.',
                'type' => 'info',
                'is_read' => true,
                'sent_at' => now()->subHours(1),
                'created_at' => now()->subHours(1),
                'updated_at' => now()->subMinutes(30),
            ],
            [
                'user_id' => 3, // Carlos López
                'title' => 'Reporte Mensual Disponible',
                'message' => 'El reporte de rendimiento mensual está disponible para revisión.',
                'type' => 'info',
                'is_read' => false,
                'sent_at' => now()->subDays(1),
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
        ];

        DB::table('notifications')->insert($notifications);
    }
}
