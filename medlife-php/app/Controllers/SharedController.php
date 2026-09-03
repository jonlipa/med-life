<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Session;
use App\Support\Totp;

final class SharedController extends Controller
{
    public function profile(): void
    {
        $user = $this->user();
        $patientProfile = $user['role'] === 'patient'
            ? $this->patientsRepo()->findByUserId((int) $user['id'])
            : null;
        $doctorProfile = $user['role'] === 'doctor'
            ? $this->doctorsRepo()->findByUserId((int) $user['id'])
            : null;

        $this->render('shared/profile', [
            'user' => $user,
            'patientProfile' => $patientProfile,
            'doctorProfile' => $doctorProfile,
        ], 'layouts/dashboard');
    }

    public function updateProfile(Request $request): void
    {
        $user = $this->user();
        $payload = [
            'full_name' => trim((string) $request->input('full_name')),
            'email' => trim((string) $request->input('email')),
            'phone' => trim((string) $request->input('phone')),
            'title' => trim((string) $request->input('title')),
        ];

        $errors = validate(
            ['full_name' => 'required', 'email' => 'required|email'],
            $payload,
        );

        if ($errors !== []) {
            $this->rememberInput($request->all());
            $this->redirect('/profile', 'Emri dhe email-i valid jane te detyrueshem.', 'danger');
        }

        try {
            $this->usersRepo()->updateUser((int) $user['id'], $payload);
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                $this->rememberInput($request->all());
                $this->redirect('/profile', 'Ky email perdoret nga nje llogari tjeter.', 'danger');
            }

            throw $e;
        }

        if (($user['role'] ?? '') === 'patient') {
            $this->patientsRepo()->updatePatientByUser((int) $user['id'], [
                'address' => trim((string) $request->input('address')),
                'emergency_contact' => trim((string) $request->input('emergency_contact')),
                'insurance_provider' => trim((string) $request->input('insurance_provider')),
                'blood_type' => trim((string) $request->input('blood_type')),
                'clinical_notes' => trim((string) $request->input('clinical_notes')),
            ]);
        }

        $this->clinical()->logAudit(
            (int) $user['id'],
            (string) $user['full_name'],
            'Perditesoi profilin',
            'Profile',
            'info'
        );

        $this->redirect('/profile', 'Profili u perditesua.');
    }

    public function reports(): void
    {
        $this->render('shared/reports', [
            'metrics' => $this->clinical()->reportMetrics(),
            'audit' => $this->clinical()->listAuditEvents(8),
            'billings' => array_slice($this->clinical()->listBillings(), 0, 8),
        ], 'layouts/dashboard');
    }

    public function settings(): void
    {
        $user = $this->user();
        $pendingSecret = trim((string) Session::get('two_factor_pending_secret', ''));
        $accountName = (string) ($user['email'] ?? $user['username'] ?? 'user');

        $this->render('shared/settings', [
            'user' => $user,
            'settings' => $this->clinical()->getSettings(),
            'environment' => [
                'app_env' => config('APP_ENV', 'production'),
                'app_url' => config('APP_URL', ''),
                'db_host' => config('DB_HOST', ''),
                'session_cookie' => config('SESSION_COOKIE_NAME', ''),
            ],
            'twoFactor' => [
                'enabled' => (bool) ($user['two_factor_enabled'] ?? false),
                'pending_secret' => $pendingSecret,
                'otpauth_uri' => $pendingSecret !== ''
                    ? Totp::provisioningUri((string) config('APP_NAME', 'Med Life'), $accountName, $pendingSecret)
                    : '',
                'email_otp_enabled' => (bool) ($user['email_otp_enabled'] ?? false),
            ],
        ], 'layouts/dashboard');
    }

    public function updateTwoFactor(Request $request): void
    {
        $user = $this->user();
        $freshUser = $this->usersRepo()->findById((int) $user['id']) ?? $user;
        $action = trim((string) $request->input('action'));
        $isEnabled = (bool) ($freshUser['two_factor_enabled'] ?? false);
        $isEmailOtpEnabled = (bool) ($freshUser['email_otp_enabled'] ?? false);

        if ($action === 'generate') {
            if ($isEnabled) {
                $this->redirect('/settings', '2FA eshte tashme aktive. Caktivizoje nese deshiron ta rigjenerosh.', 'warning');
            }

            Session::put('two_factor_pending_secret', Totp::generateSecret());
            Session::put('two_factor_pending_created_at', time());
            $this->redirect('/settings', 'Sekreti 2FA u gjenerua. Shtoje ne Authenticator dhe konfirmo me kodin 6-shifror.');
        }

        if ($action === 'confirm') {
            if ($isEnabled) {
                $this->redirect('/settings', '2FA eshte tashme aktive.', 'info');
            }

            $secret = trim((string) Session::get('two_factor_pending_secret', ''));
            if ($secret === '') {
                $this->redirect('/settings', 'Fillimisht gjenero sekretin per Authenticator.', 'danger');
            }

            $code = preg_replace('/\s+/', '', (string) $request->input('code')) ?? '';
            if (!preg_match('/^\d{6}$/', $code)) {
                $this->redirect('/settings', 'Kodi i verifikimit duhet te kete 6 shifra.', 'danger');
            }

            if (!Totp::verifyCode($secret, $code, 1)) {
                $this->redirect('/settings', 'Kodi i autentikatorit nuk eshte valid. Kontrollo oren e telefonit dhe provo perseri.', 'danger');
            }

            $this->usersRepo()->enableTwoFactor((int) $user['id'], $secret);
            $this->clearTwoFactorSetupState();

            $this->clinical()->logAudit(
                (int) $user['id'],
                (string) $user['full_name'],
                'Aktivizoi autentikimin me dy hapa',
                'Security/2FA',
                'medium'
            );

            $this->redirect('/settings', 'Autentikimi me dy hapa u aktivizua me sukses.');
        }

        if ($action === 'disable') {
            if (!$isEnabled) {
                $this->redirect('/settings', '2FA nuk eshte aktive ne kete llogari.', 'warning');
            }

            $currentPassword = (string) $request->input('current_password');
            if (trim($currentPassword) === '') {
                $this->redirect('/settings', 'Shkruani fjalkalimin aktual per caktivizim te 2FA.', 'danger');
            }

            if (!password_verify($currentPassword, (string) ($freshUser['password_hash'] ?? ''))) {
                $this->redirect('/settings', 'Fjalkalimi aktual nuk eshte i sakte.', 'danger');
            }

            $this->usersRepo()->disableTwoFactor((int) $user['id']);
            $this->clearTwoFactorSetupState();

            $this->clinical()->logAudit(
                (int) $user['id'],
                (string) $user['full_name'],
                'Caktivizoi autentikimin me dy hapa',
                'Security/2FA',
                'warning'
            );

            $this->redirect('/settings', 'Autentikimi me dy hapa u caktivizua.');
        }

        if ($action === 'enable_email_otp') {
            if ($isEmailOtpEnabled) {
                $this->redirect('/settings', 'Verifikimi me email eshte tashme aktiv.', 'info');
            }

            $currentPassword = (string) $request->input('current_password');
            if (trim($currentPassword) === '') {
                $this->redirect('/settings', 'Shkruani fjalkalimin aktual per aktivizim te verifikimit me email.', 'danger');
            }

            if (!password_verify($currentPassword, (string) ($freshUser['password_hash'] ?? ''))) {
                $this->redirect('/settings', 'Fjalkalimi aktual nuk eshte i sakte.', 'danger');
            }

            $email = trim((string) ($freshUser['email'] ?? ''));
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->redirect('/settings', 'Llogaria nuk ka email valid per verifikim.', 'danger');
            }

            $this->usersRepo()->setEmailOtpEnabled((int) $user['id'], true);

            $this->clinical()->logAudit(
                (int) $user['id'],
                (string) $user['full_name'],
                'Aktivizoi verifikimin me email',
                'Security/Email OTP',
                'medium'
            );

            $this->redirect('/settings', 'Verifikimi me email u aktivizua. Kodi do te dergohet ne email gjate login-it.');
        }

        if ($action === 'disable_email_otp') {
            if (!$isEmailOtpEnabled) {
                $this->redirect('/settings', 'Verifikimi me email nuk eshte aktiv.', 'warning');
            }

            $currentPassword = (string) $request->input('current_password');
            if (trim($currentPassword) === '') {
                $this->redirect('/settings', 'Shkruani fjalkalimin aktual per caktivizim te verifikimit me email.', 'danger');
            }

            if (!password_verify($currentPassword, (string) ($freshUser['password_hash'] ?? ''))) {
                $this->redirect('/settings', 'Fjalkalimi aktual nuk eshte i sakte.', 'danger');
            }

            $this->usersRepo()->setEmailOtpEnabled((int) $user['id'], false);

            $this->clinical()->logAudit(
                (int) $user['id'],
                (string) $user['full_name'],
                'Caktivizoi verifikimin me email',
                'Security/Email OTP',
                'warning'
            );

            $this->redirect('/settings', 'Verifikimi me email u caktivizua.');
        }

        $this->redirect('/settings', 'Veprim i panjohur per 2FA.', 'danger');
    }

    private function clearTwoFactorSetupState(): void
    {
        Session::forget('two_factor_pending_secret');
        Session::forget('two_factor_pending_created_at');
    }
}
