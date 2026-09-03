<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;

final class PatientController extends Controller
{
    public function dashboard(): void
    {
        $patient = $this->patientsRepo()->findByUserId((int) $this->user()['id']);

        $this->render('patient/dashboard', [
            'patient' => $patient,
            'appointments' => $patient ? $this->appointmentsRepo()->upcomingForPatient((int) $patient['id'], 4) : [],
            'results' => $patient ? $this->clinical()->listLabResultsByPatient((int) $patient['id']) : [],
            'billings' => $patient ? $this->clinical()->listBillings((int) $patient['id']) : [],
            'notifications' => $this->clinical()->listNotifications((int) $this->user()['id']),
            'prescriptions' => $patient ? $this->clinical()->listPrescriptionsByPatient((int) $patient['id']) : [],
        ], 'layouts/dashboard');
    }

    public function appointments(): void
    {
        $patient = $this->patientsRepo()->findByUserId((int) $this->user()['id']);
        $status = trim((string) $this->request()->query('status', ''));
        $filters = [
            'patient_id' => $patient ? (int) $patient['id'] : null,
            'status' => $status !== '' ? $status : null,
        ];

        $total = $filters['patient_id'] !== null
            ? $this->appointmentsRepo()->countAppointments($filters)
            : 0;
        $paginator = paginate($total, 10);

        $this->render('patient/appointments', [
            'patient' => $patient,
            'appointments' => $patient
                ? $this->appointmentsRepo()->listAppointments($filters, $paginator->limit(), $paginator->offset())
                : [],
            'doctors' => $this->doctorsRepo()->listDoctors(),
            'services' => $this->clinical()->listServices(),
            'selectedStatus' => $status,
            'paginator' => $paginator,
        ], 'layouts/dashboard');
    }

    public function storeAppointment(Request $request): void
    {
        $patient = $this->patientsRepo()->findByUserId((int) $this->user()['id']);
        if (!$patient) {
            $this->redirect('/patient', 'Profili i pacientit mungon.', 'danger');
        }

        foreach (['doctor_id', 'service_id', 'scheduled_for'] as $field) {
            if (trim((string) $request->input($field)) === '') {
                $this->rememberInput($request->all());
                $this->redirect('/patient/appointments', 'Plotesoni te dhenat e terminit.', 'danger');
            }
        }

        $scheduledFor = $this->normalizeScheduledFor($request->input('scheduled_for'));
        if ($scheduledFor === null || strtotime($scheduledFor) < time() - 300) {
            $this->rememberInput($request->all());
            $this->redirect('/patient/appointments', 'Zgjidhni nje date dhe ore te vlefshme per terminin.', 'danger');
        }

        try {
            $this->appointmentsRepo()->createAppointment([
                'patient_id' => (int) $patient['id'],
                'doctor_id' => (int) $request->input('doctor_id'),
                'service_id' => (int) $request->input('service_id'),
                'scheduled_for' => $scheduledFor,
                'status' => 'requested',
                'location' => 'Qendra Med Life',
                'created_by_user_id' => (int) $this->user()['id'],
                'notes' => (string) $request->input('notes', ''),
            ]);
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                $this->rememberInput($request->all());
                $this->redirect('/patient/appointments', 'Ky termin ekziston tashme. Zgjidhni nje kohe tjeter.', 'danger');
            }

            throw $e;
        }

        $this->clinical()->logAudit(
            (int) $this->user()['id'],
            (string) $this->user()['full_name'],
            'Kerkoi termin te ri',
            'Patient Appointments',
            'info'
        );

        $this->redirect('/patient/appointments', 'Kerkesa per termin u dergua.');
    }

    public function results(): void
    {
        $patient = $this->patientsRepo()->findByUserId((int) $this->user()['id']);

        $this->render('patient/results', [
            'results' => $patient ? $this->clinical()->listLabResultsByPatient((int) $patient['id']) : [],
            'prescriptions' => $patient ? $this->clinical()->listPrescriptionsByPatient((int) $patient['id']) : [],
        ], 'layouts/dashboard');
    }

    public function billing(): void
    {
        $patient = $this->patientsRepo()->findByUserId((int) $this->user()['id']);
        $status = trim((string) $this->request()->query('status', ''));

        $this->render('patient/billing', [
            'billings' => $patient ? $this->clinical()->listBillings((int) $patient['id'], ['status' => $status]) : [],
            'summary' => $this->clinical()->billingSummary(),
            'selectedStatus' => $status,
        ], 'layouts/dashboard');
    }

    public function notifications(): void
    {
        $this->render('patient/notifications', [
            'notifications' => $this->clinical()->listNotifications((int) $this->user()['id']),
        ], 'layouts/dashboard');
    }

    public function markNotificationRead(Request $request): void
    {
        if ((string) $request->input('action') === 'mark_all') {
            $this->clinical()->markAllNotificationsRead((int) $this->user()['id']);
            $this->redirect('/patient/notifications', 'Te gjitha njoftimet u shenuan si te lexuara.');
        }

        $notificationId = (int) $request->input('notification_id');
        if ($notificationId <= 0) {
            $this->redirect('/patient/notifications', 'Zgjidhni nje njoftim te vlefshem.', 'danger');
        }

        $this->clinical()->markNotificationRead($notificationId, (int) $this->user()['id']);
        $this->redirect('/patient/notifications', 'Njoftimi u shenua si i lexuar.');
    }

    public function updateAppointmentStatus(Request $request): void
    {
        $patient = $this->patientsRepo()->findByUserId((int) $this->user()['id']);
        if (!$patient) {
            $this->redirect('/patient', 'Profili i pacientit mungon.', 'danger');
        }

        $appointmentId = (int) $request->input('appointment_id');
        $status = trim((string) $request->input('status'));
        $appointment = $this->appointmentsRepo()->findById($appointmentId);

        if (!$appointment || (int) $appointment['patient_id'] !== (int) $patient['id'] || $status !== 'cancelled') {
            $this->redirect('/patient/appointments', 'Perditesimi i terminit deshtoi.', 'danger');
        }

        $this->appointmentsRepo()->updateStatus($appointmentId, $status);
        $this->clinical()->logAudit(
            (int) $this->user()['id'],
            (string) $this->user()['full_name'],
            'Anulloi terminin',
            'Appointment #' . $appointmentId,
            'medium'
        );

        $this->redirect('/patient/appointments?status=' . urlencode($status), 'Termini u anulua.');
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
