<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Support\FallbackData;
use App\Support\Mailer;
use App\Support\Totp;

final class AuthController extends Controller
{
    private const EMAIL_OTP_PURPOSE = 'login_email_otp';

    public function showLogin(): void
    {
        if ($this->hasPendingTwoFactor()) {
            $this->clearPendingTwoFactor();
        }

        $this->render('public/login');
    }

    public function login(Request $request): void
    {
        $identifier = trim((string) $request->input('identifier'));
        $password = (string) $request->input('password');

        $errors = validate(
            ['identifier' => 'required', 'password' => 'required'],
            ['identifier' => $identifier, 'password' => $password],
        );

        if ($errors !== []) {
            $this->rememberInput($request->all());
            $this->redirect('/login', 'Plotesoni email/username dhe fjalkalimin.', 'danger');
        }

        $clientIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (!RateLimiter::attempt("login:{$clientIp}", 10, 300)) {
            $this->redirect('/login', 'Shume perpjekje. Provoni perseri pas 5 minutash.', 'danger');
        }

        $user = $this->auth()->validateCredentials($identifier, $password);
        if (!$user) {
            $this->rememberInput($request->all());
            $this->redirect('/login', 'Kredencialet nuk u gjeten. Perdorni nje llogari demo ose databazen seed.', 'danger');
        }

        $twoFactorEnabled = (bool) ($user['two_factor_enabled'] ?? false);
        $twoFactorSecret = trim((string) ($user['two_factor_secret'] ?? ''));
        $emailOtpEnabled = (bool) ($user['email_otp_enabled'] ?? false);
        $email = trim((string) ($user['email'] ?? ''));

        if ($emailOtpEnabled && ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL))) {
            $this->redirect('/login', 'Llogaria ka verifikim me email aktiv, por email-i nuk eshte valid. Perditeso profilin ose kontakto admin-in.', 'danger');
        }

        if ($emailOtpEnabled) {
            $this->beginEmailTwoFactorChallenge($user);
        }

        if ($twoFactorEnabled && $twoFactorSecret !== '') {
            Session::put('pending_2fa_user_id', (int) $user['id']);
            Session::put('pending_2fa_method', 'authenticator');
            Session::put('pending_2fa_started_at', time());
            $this->redirect('/two-factor', 'Verifikoni hyrjen me kodin 6-shifror nga aplikacioni Authenticator.', 'warning');
        }

        $this->completeLogin($user, 'Hyrja u realizua me sukses.');
    }

    public function showTwoFactor(): void
    {
        $pendingUserId = (int) Session::get('pending_2fa_user_id', 0);
        $pendingMethod = (string) Session::get('pending_2fa_method', 'authenticator');

        if ($pendingUserId <= 0) {
            $this->redirect('/login', 'Sesioni i verifikimit ka perfunduar. Hyni perseri.', 'warning');
        }

        $user = $this->usersRepo()->findById($pendingUserId);
        if (!$user) {
            $this->clearPendingTwoFactor();
            $this->redirect('/login', 'Verifikimi me dy hapa nuk eshte i disponueshem per kete llogari.', 'danger');
        }

        if ($pendingMethod === 'email') {
            $email = trim((string) ($user['email'] ?? ''));
            if (!(bool) ($user['email_otp_enabled'] ?? false) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->clearPendingTwoFactor();
                $this->redirect('/login', 'Verifikimi me email nuk eshte i disponueshem per kete llogari.', 'danger');
            }

            $this->render('public/two_factor', [
                'identifierHint' => $this->maskIdentifier($email),
                'method' => 'email',
                'canResend' => true,
            ]);
            return;
        }

        if ($pendingMethod !== 'authenticator' || !(bool) ($user['two_factor_enabled'] ?? false) || trim((string) ($user['two_factor_secret'] ?? '')) === '') {
            $this->clearPendingTwoFactor();
            $this->redirect('/login', 'Verifikimi me dy hapa nuk eshte i disponueshem per kete llogari.', 'danger');
        }

        $this->render('public/two_factor', [
            'identifierHint' => $this->maskIdentifier((string) ($user['email'] ?? $user['username'] ?? '')),
            'method' => 'authenticator',
            'canResend' => false,
        ]);
    }

    public function verifyTwoFactor(Request $request): void
    {
        $pendingUserId = (int) Session::get('pending_2fa_user_id', 0);
        $pendingMethod = (string) Session::get('pending_2fa_method', 'authenticator');

        if ($pendingUserId <= 0) {
            $this->redirect('/login', 'Sesioni i verifikimit ka perfunduar. Hyni perseri.', 'warning');
        }

        $clientIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (!RateLimiter::attempt("two_factor:{$pendingMethod}:{$pendingUserId}:{$clientIp}", 10, 300)) {
            $this->redirect('/two-factor', 'Shume perpjekje per kodin 2FA. Prisni 5 minuta.', 'danger');
        }

        $code = preg_replace('/\s+/', '', (string) $request->input('code')) ?? '';
        if (!preg_match('/^\d{6}$/', $code)) {
            $this->redirect('/two-factor', 'Kodi duhet te kete saktesisht 6 shifra.', 'danger');
        }

        $user = $this->usersRepo()->findById($pendingUserId);
        if (!$user) {
            $this->clearPendingTwoFactor();
            $this->redirect('/login', 'Llogaria nuk ka verifikim aktiv. Hyni perseri.', 'danger');
        }

        if ($pendingMethod === 'email') {
            if (!(bool) ($user['email_otp_enabled'] ?? false)) {
                $this->clearPendingTwoFactor();
                $this->redirect('/login', 'Verifikimi me email nuk eshte aktiv per kete llogari.', 'danger');
            }

            if (!$this->verifyEmailOtpCode($user, $code)) {
                $this->redirect('/two-factor', 'Kodi i email-it nuk eshte valid ose ka skaduar.', 'danger');
            }

            $this->clearPendingTwoFactor();
            $this->completeLogin($user, 'Verifikimi me email u realizua me sukses.');
        }

        if ($pendingMethod !== 'authenticator' || !(bool) ($user['two_factor_enabled'] ?? false) || trim((string) ($user['two_factor_secret'] ?? '')) === '') {
            $this->clearPendingTwoFactor();
            $this->redirect('/login', 'Llogaria nuk ka 2FA aktive. Hyni perseri.', 'danger');
        }

        if (!Totp::verifyCode((string) $user['two_factor_secret'], $code, 1)) {
            $this->redirect('/two-factor', 'Kodi i autentikatorit nuk eshte valid.', 'danger');
        }

        $this->clearPendingTwoFactor();
        $this->completeLogin($user, 'Verifikimi me dy hapa u realizua me sukses.');
    }

    public function cancelTwoFactor(): void
    {
        $this->clearPendingTwoFactor();
        $this->redirect('/login', 'Verifikimi me dy hapa u anulua.', 'info');
    }

    public function resendTwoFactor(): void
    {
        $pendingUserId = (int) Session::get('pending_2fa_user_id', 0);
        $pendingMethod = (string) Session::get('pending_2fa_method', '');

        if ($pendingUserId <= 0 || $pendingMethod !== 'email') {
            $this->redirect('/login', 'Nuk ka sesion aktiv per ridergim kodi.', 'warning');
        }

        $clientIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (!RateLimiter::attempt("two_factor_resend:{$pendingUserId}:{$clientIp}", 5, 300)) {
            $this->redirect('/two-factor', 'Shume kerkesa per ridergim. Provoni pas pak minutash.', 'danger');
        }

        $user = $this->usersRepo()->findById($pendingUserId);
        if (!$user || !(bool) ($user['email_otp_enabled'] ?? false)) {
            $this->clearPendingTwoFactor();
            $this->redirect('/login', 'Verifikimi me email nuk eshte aktiv per kete llogari.', 'danger');
        }

        $this->beginEmailTwoFactorChallenge($user, true);
    }

    public function showRegister(): void
    {
        $this->render('public/register', [
            'doctors' => $this->dbAvailable()
                ? $this->doctorsRepo()->listDoctors()
                : FallbackData::doctors(),
        ]);
    }

    public function register(Request $request): void
    {
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (!RateLimiter::attempt("register:{$clientIp}", 5, 600)) {
            $this->redirect('/register', 'Shume perpjekje. Provoni perseri pas 10 minutash.', 'danger');
        }

        $payload = array_map(
            static fn (mixed $value) => is_string($value) ? trim($value) : $value,
            $request->all(),
        );

        $requiredFields = ['full_name', 'email', 'phone', 'password', 'date_of_birth', 'address', 'emergency_contact', 'current_doctor_id'];
        $errors = validate(
            array_combine($requiredFields, array_fill(0, count($requiredFields), 'required')) + [
                'email' => 'required|email',
                'password' => 'required|min:6',
            ],
            $payload,
        );

        if ($errors !== []) {
            $this->rememberInput($payload);
            foreach ($errors as $fieldErrors) {
                foreach ($fieldErrors as $message) {
                    $this->redirect('/register', $message, 'danger');
                }
            }
        }

        $patientId = $this->patientsRepo()->registerPatient(
            [
                'username' => strtok((string) $payload['email'], '@') ?: (string) $payload['email'],
                'email' => (string) $payload['email'],
                'password_hash' => password_hash((string) $payload['password'], PASSWORD_DEFAULT),
                'full_name' => (string) $payload['full_name'],
                'phone' => (string) $payload['phone'],
            ],
            [
                'current_doctor_id' => (int) $payload['current_doctor_id'],
                'date_of_birth' => (string) $payload['date_of_birth'],
                'phone' => (string) $payload['phone'],
                'email' => (string) $payload['email'],
                'address' => (string) $payload['address'],
                'emergency_contact' => (string) $payload['emergency_contact'],
                'insurance_provider' => (string) ($payload['insurance_provider'] ?? 'Pa Sigurim'),
                'blood_type' => (string) ($payload['blood_type'] ?? 'N/A'),
                'summary' => 'Pacient i regjistruar se fundi ne portal.',
                'allergies' => (string) ($payload['allergies'] ?? ''),
                'clinical_notes' => (string) ($payload['notes'] ?? ''),
            ],
        );

        $user = $this->usersRepo()->findByIdentifier((string) $payload['email']);
        if ($user) {
            $this->auth()->login($user);
            $this->clinical()->logAudit(
                $user['id'],
                $user['full_name'],
                'Regjistrim i pacientit',
                'Patient #' . $patientId,
                'medium'
            );
        }

        $this->redirect('/patient', 'Llogaria e pacientit u krijua me sukses.');
    }

    public function showForgotPassword(): void
    {
        $this->render('public/forgot_password');
    }

    public function sendReset(Request $request): void
    {
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (!RateLimiter::attempt("reset:{$clientIp}", 5, 600)) {
            $this->redirect('/forgot-password', 'Shume perpjekje. Provoni perseri pas 10 minutash.', 'danger');
        }

        $email = trim((string) $request->input('email'));

        $errors = validate(['email' => 'required|email'], ['email' => $email]);

        if ($errors !== []) {
            if ($email === '') {
                $this->redirect('/forgot-password', 'Vendosni email-in per te vazhduar.', 'danger');
            }
            $this->redirect('/forgot-password', 'Vendosni nje email te vlefshem.', 'danger');
        }

        $token = null;
        $resetUrl = null;
        $emailSent = false;
        $user = $this->usersRepo()->findByIdentifier($email);
        if ($user) {
            $token = bin2hex(random_bytes(16));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+30 minutes'));
            $this->usersRepo()->createPasswordResetToken((int) $user['id'], $email, $token, $expiresAt);
            $resetUrl = url('/reset-password?token=' . rawurlencode($token));
            $emailSent = Mailer::sendPasswordReset(
                $email,
                (string) ($user['full_name'] ?? 'Perdorues'),
                $resetUrl,
            );

            $this->clinical()->logAudit(
                (int) $user['id'],
                $user['full_name'],
                'Kerkese per reset password',
                $emailSent ? 'Email reset u dergua' : 'Email reset nuk u dergua',
                'info'
            );
        }

        $message = 'Nese email-i ekziston, kerkesa u regjistrua ne sistem.';
        if ($token !== null && $resetUrl !== null && !$emailSent && $this->isDebugEnabled()) {
            $message .= ' Link debug: ' . $resetUrl;
        }

        $this->redirect('/forgot-password', $message);
    }

    public function showResetPassword(): void
    {
        if (!$this->dbAvailable()) {
            $this->render('public/reset_password', [
                'token' => '',
                'email' => '',
                'setupRequired' => true,
            ]);
            return;
        }

        $token = trim((string) $_GET['token'] ?? '');
        if ($token === '') {
            $this->redirect('/forgot-password', 'Token-i per reset nuk eshte valid.', 'danger');
        }

        $resetRow = $this->usersRepo()->findPasswordResetToken($token);
        if (!$resetRow || strtotime($resetRow['expires_at']) < time()) {
            $this->redirect('/forgot-password', 'Token-i ka skaduar ose nuk ekziston.', 'danger');
        }

        $this->render('public/reset_password', [
            'token' => $token,
            'email' => $resetRow['email'],
            'setupRequired' => false,
        ]);
    }

    public function resetPassword(Request $request): void
    {
        $token = trim((string) $request->input('token'));
        $password = (string) $request->input('password');
        $passwordConfirmation = (string) $request->input('password_confirmation');

        $errors = validate(
            ['token' => 'required', 'password' => 'required|min:6', 'password_confirmation' => 'required'],
            ['token' => $token, 'password' => $password, 'password_confirmation' => $passwordConfirmation],
        );

        if ($errors !== []) {
            $this->redirect('/reset-password?token=' . urlencode($token), 'Te gjitha fushat jane te detyrueshme.', 'danger');
        }

        if ($password !== $passwordConfirmation) {
            $this->redirect('/reset-password?token=' . urlencode($token), 'Fjalkalimet nuk perputhen.', 'danger');
        }

        $resetRow = $this->usersRepo()->findPasswordResetToken($token);
        if (!$resetRow || strtotime($resetRow['expires_at']) < time()) {
            $this->redirect('/forgot-password', 'Token-i ka skaduar ose nuk ekziston.', 'danger');
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $this->usersRepo()->resetPassword((int) $resetRow['user_id'], $hashedPassword, $token);

        $this->redirect('/login', 'Fjalkalimi u ndryshua me sukses. Ky tani mund te identifikoheni.');
    }

    public function logout(): void
    {
        $user = $this->auth()->user();
        if ($user) {
            $this->clinical()->logAudit(
                (int) $user['id'],
                $user['full_name'],
                'Logout nga portali',
                'Auth',
                'info'
            );
        }

        $this->clearPendingTwoFactor();
        $this->auth()->logout();
        Response::redirect('/login');
    }

    private function completeLogin(array $user, string $message): never
    {
        $this->clearPendingTwoFactor();
        $this->auth()->login($user);

        $hasAuthenticator2fa = (bool) ($user['two_factor_enabled'] ?? false) && trim((string) ($user['two_factor_secret'] ?? '')) !== '';
        $hasEmailOtp = (bool) ($user['email_otp_enabled'] ?? false);

        $this->clinical()->logAudit(
            (int) ($user['id'] ?? 0),
            (string) ($user['full_name'] ?? 'User'),
            ($hasAuthenticator2fa || $hasEmailOtp) ? 'Login ne portal (2FA)' : 'Login ne portal',
            'Auth',
            'info'
        );

        $this->redirect(role_home((string) ($user['role'] ?? 'patient')), $message);
    }

    private function hasPendingTwoFactor(): bool
    {
        return (int) Session::get('pending_2fa_user_id', 0) > 0;
    }

    private function clearPendingTwoFactor(): void
    {
        $pendingUserId = (int) Session::get('pending_2fa_user_id', 0);
        $pendingMethod = (string) Session::get('pending_2fa_method', '');

        if ($pendingUserId > 0 && $pendingMethod === 'email' && $this->dbAvailable()) {
            $this->usersRepo()->clearEmailVerificationCodes($pendingUserId, self::EMAIL_OTP_PURPOSE);
        }

        Session::forget('pending_2fa_user_id');
        Session::forget('pending_2fa_method');
        Session::forget('pending_2fa_started_at');
    }

    private function beginEmailTwoFactorChallenge(array $user, bool $isResend = false): never
    {
        $userId = (int) ($user['id'] ?? 0);
        $email = trim((string) ($user['email'] ?? ''));

        if ($userId <= 0 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirect('/login', 'Llogaria nuk ka email valid per verifikim.', 'danger');
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        $this->usersRepo()->createEmailVerificationCode(
            $userId,
            $email,
            self::EMAIL_OTP_PURPOSE,
            $code,
            $expiresAt,
        );

        $sent = Mailer::sendLoginOtp($email, (string) ($user['full_name'] ?? 'Perdorues'), $code);
        if (!$sent) {
            $this->usersRepo()->clearEmailVerificationCodes($userId, self::EMAIL_OTP_PURPOSE);

            $message = 'Dergimi i kodit ne email deshtoi. Kontrollo konfigurimin MAIL_* ne .env.';
            if ($this->isDebugEnabled()) {
                $message .= ' [DEBUG CODE: ' . $code . ']';
            }

            $this->redirect('/login', $message, 'danger');
        }

        Session::put('pending_2fa_user_id', $userId);
        Session::put('pending_2fa_method', 'email');
        Session::put('pending_2fa_started_at', time());

        $message = $isResend
            ? 'Kodi i verifikimit u ridergua ne email.'
            : 'Kodi i verifikimit u dergua ne email. Kontrolloni inbox/spam.';
        if ($this->isDebugEnabled()) {
            $message .= ' [DEBUG CODE: ' . $code . ']';
        }

        $this->redirect('/two-factor', $message, $isResend ? 'info' : 'warning');
    }

    private function verifyEmailOtpCode(array $user, string $code): bool
    {
        $row = $this->usersRepo()->findActiveEmailVerificationCode((int) ($user['id'] ?? 0), self::EMAIL_OTP_PURPOSE);
        if (!$row) {
            return false;
        }

        $isValid = password_verify($code, (string) ($row['code_hash'] ?? ''));
        if (!$isValid) {
            return false;
        }

        $this->usersRepo()->consumeEmailVerificationCode((int) $row['id']);

        return true;
    }

    private function isDebugEnabled(): bool
    {
        return filter_var((string) config('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOL);
    }

    private function maskIdentifier(string $identifier): string
    {
        $identifier = trim($identifier);
        if ($identifier === '') {
            return 'llogaria juaj';
        }

        if (str_contains($identifier, '@')) {
            [$left, $right] = explode('@', $identifier, 2);
            $left = strlen($left) > 2
                ? substr($left, 0, 2) . str_repeat('*', max(1, strlen($left) - 2))
                : str_repeat('*', strlen($left));
            return $left . '@' . $right;
        }

        if (strlen($identifier) <= 2) {
            return str_repeat('*', strlen($identifier));
        }

        return substr($identifier, 0, 2) . str_repeat('*', max(1, strlen($identifier) - 2));
    }
}
