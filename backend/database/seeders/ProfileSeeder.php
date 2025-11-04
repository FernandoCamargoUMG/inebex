<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $profiles = [
            [
                'name' => 'Cliente Individual', 
                'description' => 'Persona física con expediente personal',
                'requirements' => 'Cédula, constancia de ingresos, referencias',
                'is_active' => true, 
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'name' => 'Empresa Corporativa', 
                'description' => 'Entidad empresarial con fines comerciales',
                'requirements' => 'Licencia comercial, estados financieros, representante legal',
                'is_active' => true, 
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'name' => 'Entidad Gubernamental', 
                'description' => 'Organización del sector público',
                'requirements' => 'Documentos oficiales, autorización institucional',
                'is_active' => true, 
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'name' => 'Organización Sin Fines de Lucro', 
                'description' => 'ONG o fundación benéfica',
                'requirements' => 'Registro de ONG, estatutos, junta directiva',
                'is_active' => true, 
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'name' => 'Estudiante Universitario', 
                'description' => 'Estudiante con expediente académico',
                'requirements' => 'Certificado de estudios, constancia de matrícula',
                'is_active' => true, 
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'name' => 'Profesional Independiente', 
                'description' => 'Trabajador por cuenta propia',
                'requirements' => 'Certificaciones profesionales, declaración fiscal',
                'is_active' => true, 
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'name' => 'Microempresa', 
                'description' => 'Pequeño negocio familiar',
                'requirements' => 'Registro mercantil, licencia municipal',
                'is_active' => true, 
                'created_at' => now(), 
                'updated_at' => now()
            ],
        ];

        DB::table('profiles')->insert($profiles);
    }
}
