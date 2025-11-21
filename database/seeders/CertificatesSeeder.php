<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Certificate;
use Illuminate\Support\Str;
class CertificatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
         // Creamos el certificado para el user 1 (curso 1)
        /* Certificate::create([
            'registration_id' => 1,
            'code'            => 'CERT-' . strtoupper(Str::random(8)),
            'total_score'     => 95, // puedes ajustar la puntuación que quieras
        ]);

        Certificate::create([
            'registration_id' => 1,
            'code'            => 'CERT-' . strtoupper(Str::random(8)),
            'total_score'     => 100, // otra puntuación de ejemplo
        ]);

        // Creamos el certificado para el user 2 (curso 1)
        Certificate::create([
            'registration_id' => 4,
            'code'            => 'CERT-' . strtoupper(Str::random(8)),
            'total_score'     => 75, // otra puntuación de ejemplo
        ]);
        Certificate::create([
            'registration_id' => 4,
            'code'            => 'CERT-' . strtoupper(Str::random(8)),
            'total_score'     => 70, // otra puntuación de ejemplo
        ]);

        Certificate::create([
            'registration_id' => 4,
            'code'            => 'CERT-' . strtoupper(Str::random(8)),
            'total_score'     => 50, // otra puntuación de ejemplo
        ]);

        Certificate::create([
            'registration_id' => 3,
            'code'            => 'CERT-' . strtoupper(Str::random(8)),
            'total_score'     => 60, // otra puntuación de ejemplo
        ]);

        Certificate::create([
            'registration_id' => 3,
            'code'            => 'CERT-' . strtoupper(Str::random(8)),
            'total_score'     => 60, // otra puntuación de ejemplo
        ]); */

        
    }
}
