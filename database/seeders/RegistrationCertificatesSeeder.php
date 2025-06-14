<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RegistrationCertificate;
class RegistrationCertificatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RegistrationCertificate::create([
            'registration_id' => 1,
        ]);
        /* RegistrationCertificate::create([
            'registration_id' => 2,
        ]); */
    }
}
