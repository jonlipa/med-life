<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;

final class DoctorController extends Controller
{
    public function dashboard(): void
    {
        $user = $this->user();
        $doctor = $this->doctorsRepo()->findByUserId((int) $user['id']);
        $patients = $doctor ? $this->patientsRepo()->listPatients((int) $doctor['id']) : [];
        $appointments = $doctor ? $this->appointmentsRepo()->upcomingForDoctor((int) $doctor['id'], 5) : [];

        $this->render('doctor/dashboard', [
            'doctor' => $doctor,
            'metrics' => [
                ['label' => 'Pacientet e caktuar', 'value' => count($patients)],
                ['label' => 'Terminet ne radhe', 'value' => count($appointments)],
                ['label' => 'Ngarkesa klinike', 'value' => $doctor['availability_text'] ?? '08:00 - 16:00'],
            ],
            'patients' => array_slice($patients, 0, 5),
            'appointments' => $appointments,
            'activity' => $this->clinical()->listAuditEvents(6),
        ], 'layouts/dashboard');
    }

    public function patients(): void
    {
        $doctor = $this->doctorsRepo()->findByUserId((int) $this->user()['id']);
        $query = trim((string) $this->request()->query('q', ''));
        $total = $doctor ? $this->patientsRepo()->countPatients((int) $doctor['id']) : 0;
        $paginator = paginate($total, 10);
        $patients = $doctor ? $this->patientsRepo()->listPatients((int) $doctor['id'], $paginator->limit(), $paginator->offset()) : [];

        if ($query !== '') {
            $patients = array_values(array_filter(
                $patients,
                static function (array $patient) use ($query): bool {
                    $haystacks = [
                        (string) ($patient['full_name'] ?? ''),
                        (string) ($patient['medical_record_number'] ?? ''),
                        (string) ($patient['phone'] ?? ''),
                    ];

                    foreach ($haystacks as $haystack) {
                        if ($haystack !== '' && stripos($haystack, $query) !== false) {
                            return true;
                        }
                    }

                    return false;
                }
            ));
        }

        $this->render('doctor/patients', [
            'patients' => $patients,
            'filters' => ['q' => $query],
            'paginator' => $paginator,
        ], 'layouts/dashboard');
    }

    public function records(): void
    {
        $doctor = $this->doctorsRepo()->findByUserId((int) $this->user()['id']);
        $patients = $doctor ? $this->patientsRepo()->listPatients((int) $doctor['id']) : [];
        $selectedPatientId = (int) ($this->request()->query('patient') ?: ($patients[0]['id'] ?? 0));
        $selectedPatient = $selectedPatientId > 0 ? $this->patientsRepo()->findById($selectedPatientId) : null;

        $this->render('doctor/records', [
            'patients' => $patients,
            'selectedPatient' => $selectedPatient,
            'records' => $doctor ? $this->clinical()->latestRecordsByDoctor((int) $doctor['id']) : [],
            'prescriptions' => $selectedPatient ? $this->clinical()->listPrescriptionsByPatient((int) $selectedPatient['id']) : [],
            'results' => $selectedPatient ? $this->clinical()->listLabResultsByPatient((int) $selectedPatient['id']) : [],
        ], 'layouts/dashboard');
    }

    public function updateRecords(Request $request): void
    {
        $doctor = $this->doctorsRepo()->findByUserId((int) $this->user()['id']);
        if (!$doctor) {
            $this->redirect('/doctor', 'Profili i doktorit nuk u gjet.', 'danger');
        }

        $action = (string) $request->input('action');
        $allowedActions = ['record', 'prescription', 'lab'];
        if (!in_array($action, $allowedActions, true)) {
            $this->redirect('/doctor/records', 'Zgjidhni nje veprim klinik te vlefshem.', 'danger');
        }

        $patientId = (int) $request->input('patient_id');
        $patient = $this->patientsRepo()->findById($patientId);

        if (!$patient) {
            $this->redirect('/doctor/records', 'Pacienti nuk u gjet.', 'danger');
        }

        if ($action === 'record') {
            if (trim((string) $request->input('diagnosis_summary')) === '') {
                $this->redirect('/doctor/records?patient=' . $patientId, 'Diagnoza/summary eshte e detyrueshme.', 'danger');
            }

            $this->clinical()->addMedicalRecord(
                $patientId,
                (int) $doctor['id'],
                trim((string) $request->input('diagnosis_summary')),
                trim((string) $request->input('allergies')),
                trim((string) $request->input('clinical_notes')),
            );
        } elseif ($action === 'prescription') {
            if (trim((string) $request->input('medication_name')) === '') {
                $this->redirect('/doctor/records?patient=' . $patientId, 'Emri i medikamentit eshte i detyrueshem.', 'danger');
            }

            $this->clinical()->addPrescription(
                $patientId,
                (int) $doctor['id'],
                trim((string) $request->input('medication_name')),
                trim((string) $request->input('instructions')),
            );
        } else {
            if (trim((string) $request->input('test_name')) === '') {
                $this->redirect('/doctor/records?patient=' . $patientId, 'Emri i testit laboratorik eshte i detyrueshem.', 'danger');
            }

            $this->clinical()->addLabResult(
                $patientId,
                (int) $doctor['id'],
                trim((string) $request->input('test_name')),
                trim((string) $request->input('status', 'in_progress')),
                trim((string) $request->input('result_summary')),
            );
        }

        $this->clinical()->logAudit(
            (int) $this->user()['id'],
            (string) $this->user()['full_name'],
            'Perditesoi kartelen klinike',
            (string) ($patient['full_name'] ?? $patient['medical_record_number']),
            'high'
        );

        $this->redirect('/doctor/records?patient=' . $patientId, 'Perditesimi klinik u ruajt me sukses.');
    }

    public function availability(): void
    {
        $doctor = $this->doctorsRepo()->findByUserId((int) $this->user()['id']);
        $status = trim((string) $this->request()->query('status', ''));

        $this->render('doctor/availability', [
            'doctor' => $doctor,
            'appointments' => $doctor
                ? $this->appointmentsRepo()->listAppointments([
                    'doctor_id' => (int) $doctor['id'],
                    'status' => $status !== '' ? $status : null,
                ])
                : [],
            'selectedStatus' => $status,
        ], 'layouts/dashboard');
    }

    public function saveAvailability(Request $request): void
    {
        $doctor = $this->doctorsRepo()->findByUserId((int) $this->user()['id']);
        if (!$doctor) {
            $this->redirect('/doctor', 'Profili i doktorit mungon.', 'danger');
        }

        $this->doctorsRepo()->updateAvailability(
            (int) $doctor['id'],
            trim((string) $request->input('availability_text')),
            trim((string) $request->input('availability_notes')),
        );

        $this->clinical()->logAudit(
            (int) $this->user()['id'],
            (string) $this->user()['full_name'],
            'Perditesoi disponueshmerine',
            'Doctor Availability',
            'info'
        );

        $this->redirect('/doctor/availability', 'Disponueshmeria u perditesua.');
    }

    public function updateAppointmentStatus(Request $request): void
    {
        $doctor = $this->doctorsRepo()->findByUserId((int) $this->user()['id']);
        if (!$doctor) {
            $this->redirect('/doctor', 'Profili i doktorit mungon.', 'danger');
        }

        $appointmentId = (int) $request->input('appointment_id');
        $status = trim((string) $request->input('status'));
        $allowedStatuses = ['confirmed', 'in_progress', 'completed', 'cancelled'];
        $appointment = $this->appointmentsRepo()->findById($appointmentId);

        if (!$appointment || (int) $appointment['doctor_id'] !== (int) $doctor['id'] || !in_array($status, $allowedStatuses, true)) {
            $this->redirect('/doctor/availability', 'Perditesimi i terminit deshtoi.', 'danger');
        }

        $this->appointmentsRepo()->updateStatus($appointmentId, $status);
        $this->clinical()->logAudit(
            (int) $this->user()['id'],
            (string) $this->user()['full_name'],
            'Perditesoi statusin e terminit',
            'Appointment #' . $appointmentId . ' -> ' . $status,
            'medium'
        );

        $this->redirect('/doctor/availability?status=' . urlencode($status), 'Statusi i terminit u perditesua.');
    }
}
