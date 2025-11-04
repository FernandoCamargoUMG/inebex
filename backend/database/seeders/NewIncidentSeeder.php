<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NewIncidentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('incidents')->insert([
            'user_id' => 6,
            'assigned_user_id' => 4,
            'title' => 'Error en documento digital',
            'description' => 'El PDF de mi constancia de ingresos se ve corrupto cuando lo abro.',
            'priority' => 'medium',
            'status' => 'open',
            'due_date' => now()->addDays(3),
            'created_at' => now()->subHours(5),
            'updated_at' => now()->subHours(5),
        ]);

        DB::table('incidents')->insert([
            'user_id' => 7,
            'assigned_user_id' => 5,
            'title' => 'Problema con carga de documentos',
            'description' => 'No puedo subir archivos PDF que superen los 5MB, necesito subir un contrato de 8MB.',
            'priority' => 'high',
            'status' => 'in_progress',
            'due_date' => now()->addDays(1),
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subHours(2),
        ]);

        DB::table('incidents')->insert([
            'user_id' => 8,
            'assigned_user_id' => 4,
            'title' => 'Consulta sobre estado de expediente',
            'description' => 'No he recibido notificaciones sobre el avance de mi expediente en los últimos días.',
            'priority' => 'low',
            'status' => 'resolved',
            'due_date' => now()->addDays(2),
            'resolved_at' => now()->subHours(12),
            'resolution' => 'Se configuraron las notificaciones automáticas para el usuario.',
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subHours(12),
        ]);

        DB::table('incidents')->insert([
            'user_id' => 9,
            'assigned_user_id' => 3,
            'title' => 'Solicitud de extensión de plazo',
            'description' => 'Necesito más tiempo para entregar los documentos fiscales debido a demoras en el SAT.',
            'priority' => 'medium',
            'status' => 'open',
            'due_date' => now()->addDays(5),
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ]);

        DB::table('incidents')->insert([
            'user_id' => 10,
            'assigned_user_id' => 5,
            'title' => 'Error en información personal',
            'description' => 'Mi dirección aparece incorrecta en el sistema, necesito actualizarla.',
            'priority' => 'low',
            'status' => 'open',
            'due_date' => now()->addDays(4),
            'created_at' => now()->subHours(8),
            'updated_at' => now()->subHours(8),
        ]);

        DB::table('incidents')->insert([
            'user_id' => 4,
            'assigned_user_id' => 2,
            'title' => 'Problema técnico con sistema',
            'description' => 'El sistema se vuelve lento cuando hay muchos usuarios conectados simultáneamente.',
            'priority' => 'critical',
            'status' => 'in_progress',
            'due_date' => now()->addHours(24),
            'created_at' => now()->subHours(4),
            'updated_at' => now()->subHours(1),
        ]);
    }
}