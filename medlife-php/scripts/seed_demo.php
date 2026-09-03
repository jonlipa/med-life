<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap/init.php';

$db = app('db');
if (!$db instanceof \PDO || !db_available()) {
    fwrite(STDERR, 'Database unavailable: ' . (db_status()['message'] ?? 'Lidhja me databazen deshtoi.') . PHP_EOL);
    exit(1);
}

$truncateOrder = [
    'password_reset_tokens',
    'audit_logs',
    'notifications',
    'billings',
    'lab_results',
    'prescriptions',
    'medical_records',
    'intake_forms',
    'appointments',
    'services',
    'patients',
    'doctors',
    'users',
    'settings',
];

$db->beginTransaction();

try {
    $db->exec('SET FOREIGN_KEY_CHECKS = 0');
    foreach ($truncateOrder as $table) {
        $db->exec("DELETE FROM {$table}");
    }
    $db->exec('SET FOREIGN_KEY_CHECKS = 1');

    $insert = static function (string $table, array $rows) use ($db): void {
        foreach ($rows as $row) {
            $columns = array_keys($row);
            $placeholders = array_map(static fn (string $column): string => ':' . $column, $columns);
            $sql = sprintf(
                'INSERT INTO %s (%s) VALUES (%s)',
                $table,
                implode(', ', $columns),
                implode(', ', $placeholders),
            );

            $statement = $db->prepare($sql);
            $statement->execute($row);
        }
    };

    $insert('settings', [
        ['setting_key' => 'clinic_email', 'setting_value' => 'info@medlife-ks.com', 'updated_at' => '2026-03-30 09:00:00'],
        ['setting_key' => 'support_phone', 'setting_value' => '+383 38 555 100', 'updated_at' => '2026-03-30 09:00:00'],
        ['setting_key' => 'clinic_address', 'setting_value' => 'Rr. Ilaz Kodra, Prishtine', 'updated_at' => '2026-03-30 09:00:00'],
        ['setting_key' => 'session_timeout', 'setting_value' => '30 minuta', 'updated_at' => '2026-03-30 09:00:00'],
        ['setting_key' => 'patient_portal_enabled', 'setting_value' => 'true', 'updated_at' => '2026-03-30 09:00:00'],
        ['setting_key' => 'notifications_enabled', 'setting_value' => 'true', 'updated_at' => '2026-03-30 09:00:00'],
    ]);

    $insert('users', [
        ['id' => 1, 'role' => 'admin', 'username' => 'admin', 'email' => 'admin@medlife.local', 'password_hash' => password_hash('ChangeMe#2026', PASSWORD_DEFAULT), 'full_name' => 'Arben Kola', 'title' => 'Administrator Klinik', 'phone' => '+383 44 111 101', 'avatar_path' => null, 'created_at' => '2026-03-30 08:00:00'],
        ['id' => 2, 'role' => 'doctor', 'username' => 'dr_arben', 'email' => 'arben.hoxha@medlife.local', 'password_hash' => password_hash('Doctor#2026!', PASSWORD_DEFAULT), 'full_name' => 'Dr. Arben Hoxha', 'title' => 'Kardiolog', 'phone' => '+383 44 111 201', 'avatar_path' => null, 'created_at' => '2026-03-30 08:00:00'],
        ['id' => 3, 'role' => 'doctor', 'username' => 'dr_elona', 'email' => 'elona.berisha@medlife.local', 'password_hash' => password_hash('Doctor#2026!', PASSWORD_DEFAULT), 'full_name' => 'Dr. Elona Berisha', 'title' => 'Pediatre', 'phone' => '+383 44 111 202', 'avatar_path' => null, 'created_at' => '2026-03-30 08:00:00'],
        ['id' => 4, 'role' => 'doctor', 'username' => 'dr_gent', 'email' => 'gent.basha@medlife.local', 'password_hash' => password_hash('Doctor#2026!', PASSWORD_DEFAULT), 'full_name' => 'Dr. Gent Basha', 'title' => 'Neurolog', 'phone' => '+383 44 111 203', 'avatar_path' => null, 'created_at' => '2026-03-30 08:00:00'],
        ['id' => 5, 'role' => 'doctor', 'username' => 'dr_mira', 'email' => 'mira.dervishi@medlife.local', 'password_hash' => password_hash('Doctor#2026!', PASSWORD_DEFAULT), 'full_name' => 'Dr. Mira Dervishi', 'title' => 'Radiologe', 'phone' => '+383 44 111 204', 'avatar_path' => null, 'created_at' => '2026-03-30 08:00:00'],
        ['id' => 6, 'role' => 'reception', 'username' => 'reception', 'email' => 'jeta.krasniqi@medlife.local', 'password_hash' => password_hash('Reception#2026!', PASSWORD_DEFAULT), 'full_name' => 'Jeta Krasniqi', 'title' => 'Koordinatore Recepsioni', 'phone' => '+383 44 111 301', 'avatar_path' => null, 'created_at' => '2026-03-30 08:00:00'],
        ['id' => 7, 'role' => 'patient', 'username' => 'patient_aurora', 'email' => 'aurora.berisha@medlife.local', 'password_hash' => password_hash('Patient#2026!', PASSWORD_DEFAULT), 'full_name' => 'Aurora Berisha', 'title' => 'Pacient', 'phone' => '+383 44 111 401', 'avatar_path' => null, 'created_at' => '2026-03-30 08:00:00'],
        ['id' => 8, 'role' => 'patient', 'username' => 'patient_ilir', 'email' => 'ilir.dema@medlife.local', 'password_hash' => password_hash('Patient#2026!', PASSWORD_DEFAULT), 'full_name' => 'Ilir Dema', 'title' => 'Pacient', 'phone' => '+383 44 111 402', 'avatar_path' => null, 'created_at' => '2026-03-30 08:00:00'],
    ]);

    $insert('doctors', [
        ['id' => 1, 'user_id' => 2, 'department' => 'Kardiologji', 'specialization' => 'Kardiolog', 'experience_years' => 12, 'availability_text' => 'E hene - e premte, 08:00 - 16:00', 'room' => 'B-03', 'bio' => 'Konsulta kardiake, EKG dhe ndjekje e pacienteve me risk kardiovaskular.', 'availability_notes' => 'Urgjencat trajtohen ne oret e para.', 'hero_image_path' => 'assets/images/doctor-hero.png', 'image_path' => 'assets/images/doctors/doctor-1.png', 'created_at' => '2026-03-30 08:00:00'],
        ['id' => 2, 'user_id' => 3, 'department' => 'Pediatri', 'specialization' => 'Pediatre', 'experience_years' => 9, 'availability_text' => '08:00 - 16:00', 'room' => 'A-11', 'bio' => 'Vizita pediatrike, imunizim dhe ndjekje e zhvillimit.', 'availability_notes' => 'Kontrollet periodike pas ores 13:00.', 'hero_image_path' => 'assets/images/doctors/doctor-3.png', 'image_path' => 'assets/images/doctors/doctor-3.png', 'created_at' => '2026-03-30 08:00:00'],
        ['id' => 3, 'user_id' => 4, 'department' => 'Neurologji', 'specialization' => 'Neurolog', 'experience_years' => 11, 'availability_text' => '09:00 - 17:00', 'room' => 'C-04', 'bio' => 'Diagnostikim neurologjik, migrene dhe ndjekje e pacienteve kronike.', 'availability_notes' => 'Konsulta te specializuara ne mengjes.', 'hero_image_path' => 'assets/images/doctors/doctor-2.png', 'image_path' => 'assets/images/doctors/doctor-2.png', 'created_at' => '2026-03-30 08:00:00'],
        ['id' => 4, 'user_id' => 5, 'department' => 'Radiologji', 'specialization' => 'Radiologe', 'experience_years' => 14, 'availability_text' => '08:30 - 15:30', 'room' => 'R-02', 'bio' => 'Ekografi, interpretim imazherik dhe raporte diagnostike.', 'availability_notes' => 'Raportet e avancuara publikohen brenda dites.', 'hero_image_path' => 'assets/images/doctors/doctor-4.png', 'image_path' => 'assets/images/doctors/doctor-4.png', 'created_at' => '2026-03-30 08:00:00'],
    ]);

    $insert('patients', [
        ['id' => 1, 'user_id' => 7, 'current_doctor_id' => 1, 'medical_record_number' => 'ML-24017', 'date_of_birth' => '1992-08-14', 'phone' => '+383 44 111 401', 'email' => 'aurora.berisha@medlife.local', 'address' => 'Lagjja Arberia, Prishtine', 'emergency_contact' => 'Blerim Berisha - +383 44 200 111', 'insurance_provider' => 'Sigal', 'blood_type' => 'A+', 'summary' => 'Hipertension i kontrolluar me terapi te lehte.', 'allergies' => 'Peniciline', 'clinical_notes' => 'Pacientja ndjek planin e kontrollit cdo 14 dite.', 'created_at' => '2026-03-30 08:00:00'],
        ['id' => 2, 'user_id' => 8, 'current_doctor_id' => 1, 'medical_record_number' => 'ML-24102', 'date_of_birth' => '1968-03-22', 'phone' => '+383 44 111 402', 'email' => 'ilir.dema@medlife.local', 'address' => 'Ulpiane, Prishtine', 'emergency_contact' => 'Arta Dema - +383 44 222 102', 'insurance_provider' => 'Elsig', 'blood_type' => 'B+', 'summary' => 'Vleresim kardiak pas episodeve te lodhjes se shpejte.', 'allergies' => '', 'clinical_notes' => 'Duhet ndjekje me EKG dhe kontroll laboratorik ne 30 dite.', 'created_at' => '2026-03-30 08:00:00'],
        ['id' => 3, 'user_id' => null, 'current_doctor_id' => 2, 'medical_record_number' => 'ML-24118', 'date_of_birth' => '2018-11-03', 'phone' => '+383 44 700 818', 'email' => 'prindi.elira@example.com', 'address' => 'Veternik, Prishtine', 'emergency_contact' => 'Elira Sadiku - +383 44 700 818', 'insurance_provider' => 'Publike', 'blood_type' => 'O+', 'summary' => 'Kontroll pediatrik periodik dhe vaksinim.', 'allergies' => 'Pluhur', 'clinical_notes' => 'Shendet i mire, kontrolli i ardhshem pas tre muajve.', 'created_at' => '2026-03-30 08:00:00'],
    ]);

    $insert('services', [
        ['id' => 1, 'name' => 'Kardiologji', 'department' => 'Kardiologji', 'fee' => 60.00, 'duration_minutes' => 40, 'description' => 'Kontroll kardiologjik, EKG dhe plan trajtimi.'],
        ['id' => 2, 'name' => 'Pediatri', 'department' => 'Pediatri', 'fee' => 45.00, 'duration_minutes' => 30, 'description' => 'Vizita pediatrike dhe ndjekje e zhvillimit.'],
        ['id' => 3, 'name' => 'Laborator', 'department' => 'Laborator', 'fee' => 25.00, 'duration_minutes' => 20, 'description' => 'Analiza gjaku, urine dhe panele kontrolli.'],
        ['id' => 4, 'name' => 'Radiologji', 'department' => 'Radiologji', 'fee' => 80.00, 'duration_minutes' => 45, 'description' => 'Ekografi dhe raporte diagnostike.'],
        ['id' => 5, 'name' => 'Mjekesi Familjare', 'department' => 'Mjekesi Familjare', 'fee' => 35.00, 'duration_minutes' => 25, 'description' => 'Kontrolle te pergjithshme dhe ndjekje periodike.'],
    ]);

    $insert('appointments', [
        ['id' => 1, 'patient_id' => 1, 'doctor_id' => 1, 'service_id' => 1, 'scheduled_for' => '2026-04-24 10:00:00', 'status' => 'scheduled', 'location' => 'Dhoma B-03', 'created_by_user_id' => 6, 'notes' => '', 'created_at' => '2026-03-30 08:00:00'],
        ['id' => 2, 'patient_id' => 1, 'doctor_id' => 1, 'service_id' => 3, 'scheduled_for' => '2026-04-28 09:30:00', 'status' => 'requested', 'location' => 'Laboratori', 'created_by_user_id' => 7, 'notes' => 'Kerkese pacienti', 'created_at' => '2026-03-30 08:00:00'],
        ['id' => 3, 'patient_id' => 2, 'doctor_id' => 1, 'service_id' => 1, 'scheduled_for' => '2026-03-30 10:00:00', 'status' => 'scheduled', 'location' => 'Dhoma B-03', 'created_by_user_id' => 6, 'notes' => '', 'created_at' => '2026-03-30 08:00:00'],
        ['id' => 4, 'patient_id' => 2, 'doctor_id' => 1, 'service_id' => 3, 'scheduled_for' => '2026-03-30 11:30:00', 'status' => 'completed', 'location' => 'Laboratori', 'created_by_user_id' => 2, 'notes' => '', 'created_at' => '2026-03-30 08:00:00'],
        ['id' => 5, 'patient_id' => 3, 'doctor_id' => 2, 'service_id' => 2, 'scheduled_for' => '2026-03-30 13:00:00', 'status' => 'scheduled', 'location' => 'Dhoma A-11', 'created_by_user_id' => 6, 'notes' => '', 'created_at' => '2026-03-30 08:00:00'],
        ['id' => 6, 'patient_id' => 3, 'doctor_id' => 2, 'service_id' => 2, 'scheduled_for' => '2026-04-02 08:45:00', 'status' => 'scheduled', 'location' => 'Dhoma A-11', 'created_by_user_id' => 6, 'notes' => '', 'created_at' => '2026-03-30 08:00:00'],
    ]);

    $insert('medical_records', [
        ['id' => 1, 'patient_id' => 1, 'doctor_id' => 1, 'diagnosis_summary' => 'Hipertension i lehte ne monitorim.', 'allergies' => 'Peniciline', 'clinical_notes' => 'Presioni stabil. Pacientja ndjek keshillat per diete dhe aktivitet.', 'updated_at' => '2026-03-22 11:00:00'],
        ['id' => 2, 'patient_id' => 2, 'doctor_id' => 1, 'diagnosis_summary' => 'Nevoje per vleresim kardiak me EKG.', 'allergies' => '', 'clinical_notes' => 'Lodhje e shpejte ne ecje te gjata. Ne pritje te kontrollit te radhes.', 'updated_at' => '2026-03-20 09:20:00'],
    ]);

    $insert('prescriptions', [
        ['id' => 1, 'patient_id' => 1, 'doctor_id' => 1, 'medication_name' => 'Rosumet 10mg', 'instructions' => '1 tablete ne darke per 30 dite.', 'created_at' => '2026-03-18 10:20:00'],
        ['id' => 2, 'patient_id' => 1, 'doctor_id' => 1, 'medication_name' => 'Vitamine D3', 'instructions' => '1 kapsule cdo mengjes per 60 dite.', 'created_at' => '2026-03-18 10:25:00'],
    ]);

    $insert('lab_results', [
        ['id' => 1, 'patient_id' => 1, 'doctor_id' => 1, 'name' => 'Hemogram i plote', 'status' => 'completed', 'requested_at' => '2026-03-22 08:30:00', 'result_summary' => 'Vlerat brenda kufijve normal.', 'completed_at' => '2026-03-22 14:10:00'],
        ['id' => 2, 'patient_id' => 2, 'doctor_id' => 1, 'name' => 'Panel lipidik', 'status' => 'in_progress', 'requested_at' => '2026-03-29 10:00:00', 'result_summary' => 'Ne proces verifikimi.', 'completed_at' => null],
        ['id' => 3, 'patient_id' => 1, 'doctor_id' => 1, 'name' => 'EKG raport', 'status' => 'completed', 'requested_at' => '2026-03-18 11:00:00', 'result_summary' => 'Ritmi sinus, pa ndryshime akute.', 'completed_at' => '2026-03-18 11:40:00'],
    ]);

    $insert('billings', [
        ['id' => 1, 'patient_id' => 1, 'appointment_id' => 1, 'amount' => 75.00, 'status' => 'pending', 'issued_at' => '2026-03-28 09:00:00'],
        ['id' => 2, 'patient_id' => 1, 'appointment_id' => 2, 'amount' => 45.00, 'status' => 'paid', 'issued_at' => '2026-02-12 16:00:00'],
        ['id' => 3, 'patient_id' => 2, 'appointment_id' => 3, 'amount' => 60.00, 'status' => 'overdue', 'issued_at' => '2026-03-24 12:20:00'],
    ]);

    $insert('notifications', [
        ['id' => 1, 'user_id' => 7, 'title' => 'Rezultati i analizes u publikua', 'message' => 'Hemogrami i plote eshte gati per shikim ne portal.', 'is_read' => 0, 'created_at' => '2026-03-22 14:15:00'],
        ['id' => 2, 'user_id' => 7, 'title' => 'Fatura mujore ne pritje', 'message' => 'Fatura per konsulten e fundit pret konfirmimin e pageses.', 'is_read' => 1, 'created_at' => '2026-03-24 08:00:00'],
        ['id' => 3, 'user_id' => 1, 'title' => 'Audit i ri ne panelin administrativ', 'message' => 'U regjistrua ndryshim ne settings nga llogaria admin.', 'is_read' => 0, 'created_at' => '2026-03-29 17:20:00'],
    ]);

    $insert('intake_forms', [
        ['id' => 1, 'patient_id' => 3, 'edited_by_user_id' => 6, 'reason_for_visit' => 'Kontroll pediatrik', 'insurance_provider' => 'Publike', 'intake_notes' => 'Vizite periodike dhe verifikim i vaksinave.', 'status' => 'scheduled', 'created_at' => '2026-03-29 14:00:00', 'updated_at' => '2026-03-29 14:20:00'],
        ['id' => 2, 'patient_id' => 2, 'edited_by_user_id' => 6, 'reason_for_visit' => 'Konsulte kardiologjike', 'insurance_provider' => 'Elsig', 'intake_notes' => 'Pacienti solli analizat paraprake.', 'status' => 'new', 'created_at' => '2026-03-30 08:10:00', 'updated_at' => '2026-03-30 08:30:00'],
    ]);

    $insert('audit_logs', [
        ['id' => 1, 'actor_user_id' => 1, 'actor_name' => 'Arben Kola', 'action_text' => 'Perditesoi settings e sesionit', 'target_text' => 'Portal Settings', 'severity' => 'info', 'created_at' => '2026-03-29 17:20:00'],
        ['id' => 2, 'actor_user_id' => 6, 'actor_name' => 'Jeta Krasniqi', 'action_text' => 'Krijoi intake te ri', 'target_text' => 'Pacienti Ilir Dema', 'severity' => 'medium', 'created_at' => '2026-03-30 08:30:00'],
        ['id' => 3, 'actor_user_id' => 2, 'actor_name' => 'Dr. Arben Hoxha', 'action_text' => 'Shtoi recete te re', 'target_text' => 'Aurora Berisha', 'severity' => 'high', 'created_at' => '2026-03-18 10:25:00'],
    ]);

    $db->commit();
    fwrite(STDOUT, "Seed demo u krijua me sukses.\n");
} catch (Throwable $exception) {
    $db->rollBack();
    $db->exec('SET FOREIGN_KEY_CHECKS = 1');
    fwrite(STDERR, "Seed demo deshtoi: {$exception->getMessage()}\n");
    exit(1);
}
