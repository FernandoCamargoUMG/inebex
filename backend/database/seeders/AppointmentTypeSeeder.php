<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AppointmentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $appointmentTypes = [
            [
                'name' => 'Consulta Inicial', 
                'description' => 'Primera cita para evaluación de expediente',
                'duration_minutes' => 60, 
                'is_active' => true, 
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'name' => 'Seguimiento Regular', 
                'description' => 'Reunión de seguimiento del progreso',
                'duration_minutes' => 30, 
                'is_active' => true, 
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'name' => 'Revisión de Documentos', 
                'description' => 'Verificación y análisis de documentación',
                'duration_minutes' => 45, 
                'is_active' => true, 
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'name' => 'Aprobación Final', 
                'description' => 'Cita para finalización del expediente',
                'duration_minutes' => 30, 
                'is_active' => true, 
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'name' => 'Actualización de Estado', 
                'description' => 'Breve reunión para informar cambios',
                'duration_minutes' => 15, 
                'is_active' => true, 
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'name' => 'Consulta Urgente', 
                'description' => 'Cita de emergencia para casos críticos',
                'duration_minutes' => 90, 
                'is_active' => true, 
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'name' => 'Entrega de Resultados', 
                'description' => 'Cita para entregar documentos finales',
                'duration_minutes' => 20, 
                'is_active' => true, 
                'created_at' => now(), 
                'updated_at' => now()
            ],
        ];

        DB::table('appointment_types')->insert($appointmentTypes);
    }
}
