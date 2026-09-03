<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;

final class AdminController extends Controller
{
    public function dashboard(): void
    {
        $departmentLoad = $this->doctorsRepo()->departmentLoad();
        $billingSummary = $this->clinical()->billingSummary();

        $metrics = [
            ['label' => 'Total paciente', 'value' => $this->patientsRepo()->countPatients()],
            ['label' => 'Doktore aktiv', 'value' => $this->doctorsRepo()->countDoctors()],
            ['label' => 'Termine sot', 'value' => $this->appointmentsRepo()->countToday()],
            ['label' => 'Te ardhura totale', 'value' => money($billingSummary['total'])],
        ];

        $this->render('admin/dashboard', [
            'metrics' => $metrics,
            'billingSummary' => $billingSummary,
            'recentUsers' => $this->usersRepo()->recentUsers(5),
            'featuredDoctors' => $this->doctorsRepo()->featuredDoctors(3),
            'departmentLoad' => array_slice($departmentLoad, 0, 4),
            'audit' => $this->clinical()->listAuditEvents(6),
            'billings' => array_slice($this->clinical()->listBillings(), 0, 5),
        ], 'layouts/dashboard');
    }

    public function users(): void
    {
        $filters = [
            'q' => trim((string) $this->request()->query('q', '')),
            'role' => trim((string) $this->request()->query('role', '')),
        ];

        $total = $this->usersRepo()->countUsers($filters);
        $paginator = paginate($total, 15);
        $users = $this->usersRepo()->listUsers($filters, $paginator->limit(), $paginator->offset());
        $roleSummary = $this->usersRepo()->countUsersByRole($filters);

        $this->render('admin/users', [
            'users' => $users,
            'filters' => $filters,
            'roleSummary' => $roleSummary,
            'paginator' => $paginator,
        ], 'layouts/dashboard');
    }

    public function createUser(Request $request): void
    {
        $payload = array_map(
            static fn (mixed $value) => is_string($value) ? trim($value) : $value,
            $request->all(),
        );

        foreach (['role', 'username', 'email', 'password', 'full_name'] as $field) {
            if (($payload[$field] ?? '') === '') {
                $this->rememberInput($payload);
                $this->redirect('/admin/users', 'Plotesoni fushat baze per krijimin e perdoruesit.', 'danger');
            }
        }

        $errors = validate(
            ['email' => 'email', 'password' => 'min:6', 'role' => 'in:admin,doctor,reception'],
            ['email' => $payload['email'], 'password' => $payload['password'], 'role' => $payload['role']],
        );

        if ($errors !== []) {
            $this->rememberInput($payload);
            $this->redirect('/admin/users', 'Kontrolloni email-in, rolin dhe fjalkalimin e perdoruesit.', 'danger');
        }

        try {
            $userId = $this->usersRepo()->createUser([
                'role' => (string) $payload['role'],
                'username' => (string) $payload['username'],
                'email' => (string) $payload['email'],
                'password_hash' => password_hash((string) $payload['password'], PASSWORD_DEFAULT),
                'full_name' => (string) $payload['full_name'],
                'title' => (string) ($payload['title'] ?? ucfirst((string) $payload['role'])),
                'phone' => (string) ($payload['phone'] ?? ''),
                'avatar_path' => null,
            ]);
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                $this->rememberInput($payload);
                $this->redirect('/admin/users', 'Email ose username ekziston tashme. Zgjidhni nje tjeter.', 'danger');
            }

            throw $e;
        }

        if (($payload['role'] ?? '') === 'doctor') {
            $this->doctorsRepo()->createForUser($userId, [
                'department' => (string) ($payload['department'] ?? 'Mjekesi e Pergjithshme'),
                'specialization' => (string) ($payload['specialization'] ?? 'Doktor'),
                'experience_years' => (int) ($payload['experience_years'] ?? 5),
                'availability_text' => (string) ($payload['availability_text'] ?? 'E hene - e premte, 08:00 - 16:00'),
                'room' => (string) ($payload['room'] ?? 'A-01'),
                'bio' => (string) ($payload['bio'] ?? 'Konsulta klinike dhe kujdes afatgjate.'),
                'availability_notes' => (string) ($payload['availability_notes'] ?? 'Rezervimet procesohen nga recepsioni.'),
            ]);
        }

        $actor = $this->user();
        $this->clinical()->logAudit(
            $actor['id'] ?? null,
            $actor['full_name'] ?? 'Admin',
            'Krijoi perdorues te ri',
            (string) $payload['email'],
            'medium'
        );

        $this->redirect('/admin/users', 'Perdoruesi u krijua me sukses.');
    }

    public function reports(): void
    {
        $status = trim((string) $this->request()->query('status', ''));

        $this->render('admin/reports', [
            'metrics' => $this->clinical()->reportMetrics(),
            'billingSummary' => $this->clinical()->billingSummary(),
            'billings' => $this->clinical()->listBillings(null, ['status' => $status]),
            'selectedStatus' => $status,
            'audit' => $this->clinical()->listAuditEvents(10),
        ], 'layouts/dashboard');
    }

    public function updateBillingStatus(Request $request): void
    {
        $billingId = (int) $request->input('billing_id');
        $status = trim((string) $request->input('status'));
        $allowedStatuses = ['pending', 'paid', 'overdue'];

        if ($billingId <= 0 || !in_array($status, $allowedStatuses, true)) {
            $this->redirect('/admin/reports', 'Perditesimi i faturimit deshtoi.', 'danger');
        }

        $this->clinical()->updateBillingStatus($billingId, $status);

        $actor = $this->user();
        $this->clinical()->logAudit(
            $actor['id'] ?? null,
            $actor['full_name'] ?? 'Admin',
            'Perditesoi statusin e fatures',
            'Billing #' . $billingId . ' -> ' . $status,
            'medium'
        );

        $this->redirect('/admin/reports?status=' . urlencode($status), 'Statusi i fatures u perditesua.');
    }

    public function settings(): void
    {
        $this->render('admin/settings', [
            'settings' => $this->clinical()->getSettings(),
        ], 'layouts/dashboard');
    }

    public function updateSettings(Request $request): void
    {
        $clinicEmail = trim((string) $request->input('clinic_email'));

        $errors = validate(['clinic_email' => 'email'], ['clinic_email' => $clinicEmail]);

        if ($errors !== []) {
            $this->redirect('/admin/settings', 'Email-i i klinikes nuk eshte ne formatin e sakte.', 'danger');
        }

        $settings = [
            'clinic_email' => $clinicEmail,
            'support_phone' => trim((string) $request->input('support_phone')),
            'clinic_address' => trim((string) $request->input('clinic_address')),
            'session_timeout' => trim((string) $request->input('session_timeout')),
            'patient_portal_enabled' => trim((string) $request->input('patient_portal_enabled', 'true')),
            'notifications_enabled' => trim((string) $request->input('notifications_enabled', 'true')),
        ];

        $this->clinical()->updateSettings($settings);

        $actor = $this->user();
        $this->clinical()->logAudit(
            $actor['id'] ?? null,
            $actor['full_name'] ?? 'Admin',
            'Perditesoi settings',
            'Portal Settings',
            'info'
        );

        $this->redirect('/admin/settings', 'Settings u ruajten me sukses.');
    }

    public function audit(): void
    {
        $total = $this->clinical()->countAuditEvents();
        $paginator = paginate($total, 20);

        $this->render('admin/audit', [
            'audit' => $this->clinical()->listAuditEvents(20, $paginator->offset()),
            'paginator' => $paginator,
        ], 'layouts/dashboard');
    }
}
