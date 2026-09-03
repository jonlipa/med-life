<?php

declare(strict_types=1);

namespace App\Support;

final class FallbackData
{
    public static function stats(): array
    {
        return [
            ['label' => 'Paciente aktive', 'value' => 3],
            ['label' => 'Doktore aktiv', 'value' => 4],
            ['label' => 'Termine ne sistem', 'value' => 6],
        ];
    }

    public static function services(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Kardiologji',
                'department' => 'Kardiologji',
                'fee' => 60.00,
                'duration_minutes' => 40,
                'description' => 'Kontroll kardiologjik, EKG dhe plan trajtimi.',
            ],
            [
                'id' => 2,
                'name' => 'Pediatri',
                'department' => 'Pediatri',
                'fee' => 45.00,
                'duration_minutes' => 30,
                'description' => 'Vizita pediatrike dhe ndjekje e zhvillimit.',
            ],
            [
                'id' => 3,
                'name' => 'Laborator',
                'department' => 'Laborator',
                'fee' => 25.00,
                'duration_minutes' => 20,
                'description' => 'Analiza gjaku, urine dhe panele kontrolli.',
            ],
            [
                'id' => 4,
                'name' => 'Radiologji',
                'department' => 'Radiologji',
                'fee' => 80.00,
                'duration_minutes' => 45,
                'description' => 'Ekografi dhe raporte diagnostike.',
            ],
            [
                'id' => 5,
                'name' => 'Mjekesi Familjare',
                'department' => 'Mjekesi Familjare',
                'fee' => 35.00,
                'duration_minutes' => 25,
                'description' => 'Kontrolle te pergjithshme dhe ndjekje periodike.',
            ],
        ];
    }

    public static function doctors(): array
    {
        return [
            [
                'id' => 1,
                'full_name' => 'Dr. Arben Hoxha',
                'department' => 'Kardiologji',
                'specialization' => 'Kardiolog',
                'experience_years' => 12,
                'availability_text' => 'E hene - e premte, 08:00 - 16:00',
                'room' => 'B-03',
                'bio' => 'Konsulta kardiake, EKG dhe ndjekje e pacienteve me risk kardiovaskular.',
                'availability_notes' => 'Urgjencat trajtohen ne oret e para.',
                'hero_image_path' => 'assets/images/doctor-hero.png',
                'image_path' => 'assets/images/doctors/doctor-1.png',
                'assigned_patients' => 2,
            ],
            [
                'id' => 2,
                'full_name' => 'Dr. Elona Berisha',
                'department' => 'Pediatri',
                'specialization' => 'Pediatre',
                'experience_years' => 9,
                'availability_text' => '08:00 - 16:00',
                'room' => 'A-11',
                'bio' => 'Vizita pediatrike, imunizim dhe ndjekje e zhvillimit.',
                'availability_notes' => 'Kontrollet periodike pas ores 13:00.',
                'hero_image_path' => 'assets/images/doctors/doctor-3.png',
                'image_path' => 'assets/images/doctors/doctor-3.png',
                'assigned_patients' => 1,
            ],
            [
                'id' => 3,
                'full_name' => 'Dr. Gent Basha',
                'department' => 'Neurologji',
                'specialization' => 'Neurolog',
                'experience_years' => 11,
                'availability_text' => '09:00 - 17:00',
                'room' => 'C-04',
                'bio' => 'Diagnostikim neurologjik, migrene dhe ndjekje e pacienteve kronike.',
                'availability_notes' => 'Konsulta te specializuara ne mengjes.',
                'hero_image_path' => 'assets/images/doctors/doctor-2.png',
                'image_path' => 'assets/images/doctors/doctor-2.png',
                'assigned_patients' => 0,
            ],
            [
                'id' => 4,
                'full_name' => 'Dr. Mira Dervishi',
                'department' => 'Radiologji',
                'specialization' => 'Radiologe',
                'experience_years' => 14,
                'availability_text' => '08:30 - 15:30',
                'room' => 'R-02',
                'bio' => 'Ekografi, interpretim imazherik dhe raporte diagnostike.',
                'availability_notes' => 'Raportet e avancuara publikohen brenda dites.',
                'hero_image_path' => 'assets/images/doctors/doctor-4.png',
                'image_path' => 'assets/images/doctors/doctor-4.png',
                'assigned_patients' => 0,
            ],
        ];
    }

    public static function featuredDoctors(int $limit = 3): array
    {
        return array_slice(self::doctors(), 0, max(1, $limit));
    }

    public static function contact(): array
    {
        return [
            'clinic_email' => 'info@medlife-ks.com',
            'support_phone' => '+383 38 555 100',
            'address' => 'Rr. Ilaz Kodra, Prishtine',
        ];
    }
}
