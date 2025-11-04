<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documentTypes = [
            [
                'name' => 'Cédula de Identidad', 
                'description' => 'Documento de identificación personal',
                'is_required' => true,
                'is_active' => true, 
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'name' => 'Constancia de Ingresos', 
                'description' => 'Comprobante de ingresos económicos',
                'is_required' => true,
                'is_active' => true, 
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'name' => 'Estado de Cuenta Bancario', 
                'description' => 'Movimientos bancarios recientes',
                'is_required' => false,
                'is_active' => true, 
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'name' => 'Contrato Legal', 
                'description' => 'Documentos contractuales',
                'is_required' => false,
                'is_active' => true, 
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'name' => 'Declaración de Impuestos', 
                'description' => 'Declaración fiscal anual',
                'is_required' => true,
                'is_active' => true, 
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'name' => 'Licencia Comercial', 
                'description' => 'Permiso para actividad comercial',
                'is_required' => false,
                'is_active' => true, 
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'name' => 'Certificado Académico', 
                'description' => 'Título o certificado de estudios',
                'is_required' => false,
                'is_active' => true, 
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'name' => 'Carta de Recomendación', 
                'description' => 'Referencias personales o profesionales',
                'is_required' => false,
                'is_active' => true, 
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'name' => 'Registro de Propiedad', 
                'description' => 'Documentos de bienes inmuebles',
                'is_required' => false,
                'is_active' => true, 
                'created_at' => now(), 
                'updated_at' => now()
            ],
        ];

        DB::table('document_types')->insert($documentTypes);
    }
}
