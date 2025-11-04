<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run seeders in the correct order to respect foreign key constraints
        $this->call([
            // First: Base tables without dependencies
            RoleSeeder::class,
            ProfileSeeder::class,
            AppointmentTypeSeeder::class,
            DocumentTypeSeeder::class,
            
            // Second: Tables that depend on base tables
            UserSeeder::class,
            
            // Third: Tables that depend on users and base tables  
            RecordSeeder::class,
            AppointmentSeeder::class,
            IncidentSeeder::class,
            NotificationSeeder::class,
        ]);
    }
}
