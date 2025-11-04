<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IncidentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('incidents')->insert([
            'user_id' => 4,
            'assigned_user_id' => 3,
            'title' => 'Problema Técnico',
            'description' => 'El sistema responde lentamente al procesar documentos grandes.',
            'priority' => 'medium',
            'status' => 'open',
            'due_date' => now()->addDays(2),
            'created_at' => now()->subHours(3),
            'updated_at' => now()->subHours(3),
        ]);

        DB::table('incidents')->insert([
            'user_id' => 6,
            'assigned_user_id' => 5,
            'title' => 'Solicitud de Servicio',
            'description' => 'No puedo subir documento debido al límite de tamaño del archivo.',
            'priority' => 'low',
            'status' => 'resolved',
            'due_date' => now()->addDays(1),
            'resolved_at' => now()->subHours(2),
            'resolution' => 'Se incrementó el límite de tamaño de archivo a 10MB.',
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subHours(2),
        ]);

        DB::table('incidents')->insert([
            'user_id' => 3,
            'assigned_user_id' => 2,
            'title' => 'Preocupación de Seguridad',
            'description' => 'Intento de inicio de sesión sospechoso detectado desde ubicación desconocida.',
            'priority' => 'high',
            'status' => 'in_progress',
            'due_date' => now()->addHours(24),
            'created_at' => now()->subHours(6),
            'updated_at' => now()->subHours(6),
        ]);
    }
}
