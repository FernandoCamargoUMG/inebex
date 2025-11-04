<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $appointments = [
            [
                'user_id' => 5, // Jane Client
                'appointment_type_id' => 1, // Initial Consultation
                'title' => 'Initial Consultation - Loan Application',
                'begin' => now()->addDays(2)->setTime(10, 0),
                'end' => now()->addDays(2)->setTime(11, 0),
                'status' => 'confirmed',
                'observations' => 'Client interested in personal loan options.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 5, // Jane Client
                'appointment_type_id' => 2, // Follow-up Meeting
                'title' => 'Follow-up - Document Review',
                'begin' => now()->addDays(7)->setTime(14, 30),
                'end' => now()->addDays(7)->setTime(15, 0),
                'status' => 'pending',
                'observations' => 'Review submitted documents and next steps.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 4, // John Employee
                'appointment_type_id' => 5, // Status Update
                'title' => 'Weekly Status Meeting',
                'begin' => now()->addDays(1)->setTime(9, 0),
                'end' => now()->addDays(1)->setTime(9, 15),
                'status' => 'confirmed',
                'observations' => 'Weekly team status update meeting.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('appointments')->insert($appointments);
    }
}
