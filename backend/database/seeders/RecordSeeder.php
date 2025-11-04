<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $records = [
            [
                'record_number' => 'REC-2025-001',
                'user_id' => 6, // Patricia Morales
                'profile_id' => 1, // Individual
                'status' => 'created',
                'observations' => 'Expediente individual creado para solicitud de crédito.',
                'notes' => 'Cliente con buen historial crediticio.',
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(10),
            ],
            [
                'record_number' => 'REC-2025-002',
                'user_id' => 7, // Roberto Pérez
                'profile_id' => 2, // Corporate
                'status' => 'under_review',
                'observations' => 'Expediente corporativo en revisión por el equipo de cumplimiento.',
                'notes' => 'Requiere documentación adicional del SAT.',
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(2),
            ],
            [
                'record_number' => 'REC-2025-003',
                'user_id' => 8, // Carmen Jiménez
                'profile_id' => 1, // Individual
                'status' => 'approved',
                'observations' => 'Expediente de empleado aprobado para procesos internos.',
                'notes' => 'Documentación completa y verificada.',
                'created_at' => now()->subDays(15),
                'updated_at' => now()->subDays(1),
            ],
        ];

        DB::table('records')->insert($records);
    }
}
