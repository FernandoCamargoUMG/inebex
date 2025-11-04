<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NewAppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $appointments = [
            [
                'user_id' => 6, // Patricia Morales
                'appointment_type_id' => 1, // Consulta Inicial
                'title' => 'Consulta inicial para expediente de préstamo',
                'begin' => Carbon::today()->addDays(1)->setTime(9, 0),
                'end' => Carbon::today()->addDays(1)->setTime(10, 0),
                'status' => 'confirmed',
                'observations' => 'Primera evaluación para solicitud de crédito personal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 7, // Roberto Pérez
                'appointment_type_id' => 2, // Seguimiento Regular
                'title' => 'Seguimiento de expediente empresarial',
                'begin' => Carbon::today()->addDays(2)->setTime(10, 30),
                'end' => Carbon::today()->addDays(2)->setTime(11, 0),
                'status' => 'pending',
                'observations' => 'Revisión de avances en documentación corporativa',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 8, // Carmen Jiménez
                'appointment_type_id' => 3, // Revisión de Documentos
                'title' => 'Revisión de documentos académicos',
                'begin' => Carbon::today()->setTime(14, 0),
                'end' => Carbon::today()->setTime(14, 45),
                'status' => 'pending',
                'observations' => 'Validación de certificados y títulos universitarios',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 9, // Diego Vásquez
                'appointment_type_id' => 4, // Aprobación Final
                'title' => 'Aprobación final de expediente',
                'begin' => Carbon::today()->addDays(3)->setTime(11, 0),
                'end' => Carbon::today()->addDays(3)->setTime(11, 30),
                'status' => 'confirmed',
                'observations' => 'Última revisión para cierre de caso',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 10, // Elena Castillo
                'appointment_type_id' => 5, // Actualización de Estado
                'title' => 'Actualización de estado de trámite',
                'begin' => Carbon::today()->addDays(1)->setTime(16, 0),
                'end' => Carbon::today()->addDays(1)->setTime(16, 15),
                'status' => 'pending',
                'observations' => 'Informar sobre cambios en el proceso',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 6, // Patricia Morales (segunda cita)
                'appointment_type_id' => 7, // Entrega de Resultados
                'title' => 'Entrega de documentos finalizados',
                'begin' => Carbon::today()->addDays(5)->setTime(15, 30),
                'end' => Carbon::today()->addDays(5)->setTime(15, 50),
                'status' => 'pending',
                'observations' => 'Entrega de expediente completo y documentación',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Cita para HOY - para probar recordatorios
            [
                'user_id' => 7, // Roberto Pérez
                'appointment_type_id' => 6, // Consulta Urgente
                'title' => 'Consulta urgente - Revisión de contratos',
                'begin' => Carbon::today()->setTime(13, 30),
                'end' => Carbon::today()->setTime(15, 0),
                'status' => 'confirmed',
                'observations' => 'Revisión urgente de documentos legales',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('appointments')->insert($appointments);
    }
}