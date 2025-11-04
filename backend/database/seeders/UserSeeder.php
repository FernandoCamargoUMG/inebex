<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'first_name' => 'Fernando',
                'last_name' => 'Camargo',
                'email' => 'superadmin@inebex.com',
                'password' => md5('admin123'), // MD5 como especificado
                'status' => 'active',
                'role_id' => 1, // Super Admin
                'phone' => '+502 1234-5678',
                'address' => 'Guatemala City, Guatemala',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Ana',
                'last_name' => 'García',
                'email' => 'admin@inebex.com',
                'password' => md5('admin123'),
                'status' => 'active',
                'role_id' => 2, // Admin
                'phone' => '+502 2345-6789',
                'address' => 'Antigua Guatemala, Sacatepéquez',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Carlos',
                'last_name' => 'López',
                'email' => 'manager@inebex.com',
                'password' => md5('manager123'),
                'status' => 'active',
                'role_id' => 3, // Manager
                'phone' => '+502 3456-7890',
                'address' => 'Quetzaltenango, Guatemala',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'María',
                'last_name' => 'Rodríguez',
                'email' => 'maria@inebex.com',
                'password' => md5('empleado123'),
                'status' => 'active',
                'role_id' => 4, // Employee
                'phone' => '+502 4567-8901',
                'address' => 'Escuintla, Guatemala',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Luis',
                'last_name' => 'Hernández',
                'email' => 'luis@inebex.com',
                'password' => md5('empleado123'),
                'status' => 'active',
                'role_id' => 4, // Employee
                'phone' => '+502 5678-9012',
                'address' => 'Cobán, Alta Verapaz',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Patricia',
                'last_name' => 'Morales',
                'email' => 'patricia@ejemplo.com',
                'password' => md5('cliente123'),
                'status' => 'active',
                'role_id' => 5, // Client
                'phone' => '+502 6789-0123',
                'address' => 'Huehuetenango, Guatemala',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Roberto',
                'last_name' => 'Pérez',
                'email' => 'roberto@ejemplo.com',
                'password' => md5('cliente123'),
                'status' => 'active',
                'role_id' => 5, // Client
                'phone' => '+502 7890-1234',
                'address' => 'Puerto Barrios, Izabal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Carmen',
                'last_name' => 'Jiménez',
                'email' => 'carmen@ejemplo.com',
                'password' => md5('cliente123'),
                'status' => 'active',
                'role_id' => 5, // Client
                'phone' => '+502 8901-2345',
                'address' => 'Flores, Petén',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Diego',
                'last_name' => 'Vásquez',
                'email' => 'diego@ejemplo.com',
                'password' => md5('cliente123'),
                'status' => 'active',
                'role_id' => 5, // Client
                'phone' => '+502 9012-3456',
                'address' => 'Jalapa, Guatemala',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Elena',
                'last_name' => 'Castillo',
                'email' => 'elena@ejemplo.com',
                'password' => md5('cliente123'),
                'status' => 'active',
                'role_id' => 5, // Client
                'phone' => '+502 0123-4567',
                'address' => 'Retalhuleu, Guatemala',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('users')->insert($users);
    }
}
