<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;

final class ReceptionController extends Controller
{
    public function dashboard(): void
    {
        $queue = $this->patientsRepo()->listQueue();
        $billings = $this->clinical()->listBillings();

        $this->render('reception/dashboard', [
            'metrics' => [
                ['label' => 'Paciente ne queue', 'value' => count($queue)],
                ['label' => 'Termine aktive', 'value' => count($this->appointmentsRepo()->listAppointments())],
                ['label' => 'Fatura ne pritje', 'value' => $this->clinical()->billingSummary()['pending']],
            ],
            'queue' => array_slice($queue, 0, 6),
            'billings' => array_slice($billings, 0, 6),
            'doctors' => $this->doctorsRepo()->listDoctors(),
            'services' => $this->clinical()->listServices(),
            'patients' => $this->patientsRepo()->listPatients(),
        ], 'layouts/dashboard');
    }

    public function intake(): void
    {
        $status = trim((string) $this->request()->query('status', ''));

        $this->render('reception/intake', [
            'doctors' => $this->doctorsRepo()->listDoctors(),
            'queue' => $this->patientsRepo()->listQueue($status !== '' ? $status : null),
            'selectedStatus' => $status,
        ], 'layouts/dashboard');
    }

    public function storeIntake(Request $request): void
    {
        $payload = array_map(
            static fn (mixed $value) => is_string($value) ? trim($value) : $value,
            $request->all(),
        );

        foreach (['email', 'phone', 'current_doctor_id', 'reason_for_visit'] as $field) {
            if (($payload[$field] ?? '') === '') {
                $this->rememberInput($payload);
                $this->redirect('/reception/intake', 'Plotesoni te dhenat kryesore te intake.', 'danger');
            }
        }

        $errors = validate(['email' => 'email'], ['email' => $payload['email']]);
        if ($errors !== []) {
            $this->rememberInput($payload);
            $this->redirect('/reception/intake', 'Email-i nuk eshte ne formatin e sakte.', 'danger');
        }

        $patientId = $this->patientsRepo()->createIntakePatient([
            'current_doctor_id' => (int) $payload['current_doctor_id'],
            'date_of_birth' => (string) ($payload['date_of_birth'] ?? '1990-01-01'),
            'phone' => (string) $payload['phone'],
            'email' => (string) $payload['email'],
            'address' => (string) ($payload['address'] ?? 'Pa adrese'),
            'emergency_contact' => (string) ($payload['emergency_contact'] ?? 'N/A'),
            'insurance_provider' => (string) ($payload['insurance_provider'] ?? 'Pa Sigurim'),
            'blood_type' => (string) ($payload['blood_type'] ?? 'N/A'),
            'summary' => (string) ($payload['summary'] ?? 'Pacient i regjistruar ne recepsion.'),
            'allergies' => (string) ($payload['allergies'] ?? ''),
            'clinical_notes' => (string) ($payload['clinical_notes'] ?? ''),
            'reason_for_visit' => (string) $payload['reason_for_visit'],
            'intake_notes' => (string) ($payload['intake_notes'] ?? ''),
            'status' => (string) ($payload['status'] ?? 'new'),
        ], (int) $this->user()['id']);

        $this->clinical()->logAudit(
            (int) $this->user()['id'],
            (string) $this->user()['full_name'],
            'Krijoi intake te ri',
            'Patient #' . $patientId,
            'medium'
        );

        $this->redirect('/reception/queue', 'Pacienti u shtua ne queue.');
    }

    public function queue(): void
    {
        $status = trim((string) $this->request()->query('status', ''));

        $this->render('reception/queue', [
            'queue' => $this->patientsRepo()->listQueue($status !== '' ? $status : null),
            'selectedStatus' => $status,
        ], 'layouts/dashboard');
    }

    public function appointments(): void
    {
        $status = trim((string) $this->request()->query('status', ''));
        $filters = [
            'status' => $status !== '' ? $status : null,
        ];

        $total = $this->appointmentsRepo()->countAppointments($filters);
        $paginator = paginate($total, 15);

        $this->render('reception/appointments', [
            'appointments' => $this->appointmentsRepo()->listAppointments($filters, $paginator->limit(), $paginator->offset()),
            'patients' => $this->patientsRepo()->listPatients(),
            'doctors' => $this->doctorsRepo()->listDoctors(),
            'services' => $this->clinical()->listServices(),
            'selectedStatus' => $status,
            'paginator' => $paginator,
        ], 'layouts/dashboard');
    }

    public function storeAppointment(Request $request): void
    {
        foreach (['patient_id', 'doctor_id', 'service_id', 'scheduled_for'] as $field) {
            if (trim((string) $request->input($field)) === '') {
                $this->rememberInput($request->all());
                $this->redirect('/reception/appointments', 'Plotesoni fushat e terminit.', 'danger');
            }
        }

        $scheduledFor = $this->normalizeScheduledFor($request->input('scheduled_for'));
        if ($scheduledFor === null) {
            $this->rememberInput($request->all());
            $this->redirect('/reception/appointments', 'Zgjidhni nje date dhe ore te vlefshme per terminin.', 'danger');
        }

        try {
            $this->appointmentsRepo()->createAppointment([
                'patient_id' => (int) $request->input('patient_id'),
                'doctor_id' => (int) $request->input('doctor_id'),
                'service_id' => (int) $request->input('service_id'),
                'scheduled_for' => $scheduledFor,
                'status' => (string) $request->input('status', 'scheduled'),
                'location' => (string) $request->input('location', 'Qendra Med Life'),
                'created_by_user_id' => (int) $this->user()['id'],
                'notes' => (string) $request->input('notes', ''),
            ]);
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                $this->rememberInput($request->all());
                $this->redirect('/reception/appointments', 'Ky termin ekziston tashme. Zgjidhni nje kohe tjeter.', 'danger');
            }

            throw $e;
        }

        $this->clinical()->logAudit(
            (int) $this->user()['id'],
            (string) $this->user()['full_name'],
            'Krijoi termin te ri',
            'Appointments',
            'medium'
        );

        $this->redirect('/reception/appointments', 'Termini u ruajt me sukses.');
    }

    public function updateQueueStatus(Request $request): void
    {
        $intakeId = (int) $request->input('intake_id');
        $status = trim((string) $request->input('status'));
        $allowedStatuses = ['new', 'scheduled', 'in_progress', 'completed', 'cancelled'];
        $entry = $this->patientsRepo()->findQueueEntry($intakeId);

        if (!$entry || !in_array($status, $allowedStatuses, true)) {
            $this->redirect('/reception/queue', 'Perditesimi i queue deshtoi.', 'danger');
        }

        $this->patientsRepo()->updateQueueStatus($intakeId, $status);
        $this->clinical()->logAudit(
            (int) $this->user()['id'],
            (string) $this->user()['full_name'],
            'Perditesoi queue status',
            (string) ($entry['medical_record_number'] ?? ('Intake #' . $intakeId)) . ' -> ' . $status,
            'medium'
        );

        $this->redirect('/reception/queue?status=' . urlencode($status), 'Statusi i queue u perditesua.');
    }

    public function updateAppointmentStatus(Request $request): void
    {
        $appointmentId = (int) $request->input('appointment_id');
        $status = trim((string) $request->input('status'));
        $allowedStatuses = ['requested', 'scheduled', 'confirmed', 'completed', 'cancelled'];
        $appointment = $this->appointmentsRepo()->findById($appointmentId);

        if (!$appointment || !in_array($status, $allowedStatuses, true)) {
            $this->redirect('/reception/appointments', 'Perditesimi i terminit deshtoi.', 'danger');
        }

        $this->appointmentsRepo()->updateStatus($appointmentId, $status);
        $this->clinical()->logAudit(
            (int) $this->user()['id'],
            (string) $this->user()['full_name'],
            'Perditesoi statusin e terminit',
            'Appointment #' . $appointmentId . ' -> ' . $status,
            'medium'
        );

        $this->redirect('/reception/appointments?status=' . urlencode($status), 'Statusi i terminit u perditesua.');
    }

    private function normalizeScheduledFor(mixed $value): ?string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $timestamp = strtotime($raw);
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d H:i:s', $timestamp);
    }
}
