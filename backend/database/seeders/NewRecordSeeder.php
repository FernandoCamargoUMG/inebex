<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NewRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $records = [
            [
                'user_id' => 6, // Patricia Morales
                'profile_id' => 1, // Cliente Individual
                'record_number' => 'EXP-2025-001',
                'status' => 'active',
                'notes' => 'Expediente para solicitud de crédito personal. Cliente con historial crediticio limpio.',
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(1),
            ],
            [
                'user_id' => 7, // Roberto Pérez
                'profile_id' => 2, // Empresa Corporativa
                'record_number' => 'EXP-2025-002',
                'status' => 'active',
                'notes' => 'Expediente empresarial para registro de nueva sociedad. Documentación en proceso.',
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(2),
            ],
            [
                'user_id' => 8, // Carmen Jiménez
                'profile_id' => 5, // Estudiante Universitario
                'record_number' => 'EXP-2025-003',
                'status' => 'completed',
                'notes' => 'Expediente académico completado. Certificaciones validadas exitosamente.',
                'created_at' => now()->subDays(15),
                'updated_at' => now()->subDays(3),
            ],
            [
                'user_id' => 9, // Diego Vásquez
                'profile_id' => 6, // Profesional Independiente
                'record_number' => 'EXP-2025-004',
                'status' => 'active',
                'notes' => 'Expediente para registro profesional independiente. Pendiente documentos fiscales.',
                'created_at' => now()->subDays(7),
                'updated_at' => now()->subDays(1),
            ],
            [
                'user_id' => 10, // Elena Castillo
                'profile_id' => 7, // Microempresa
                'record_number' => 'EXP-2025-005',
                'status' => 'active',
                'notes' => 'Expediente para constitución de microempresa familiar. En proceso de revisión.',
                'created_at' => now()->subDays(12),
                'updated_at' => now()->subDays(4),
            ],
        ];

        DB::table('records')->insert($records);
    }
}