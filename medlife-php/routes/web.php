<?php

declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\DoctorController;
use App\Controllers\HomeController;
use App\Controllers\PatientController;
use App\Controllers\ReceptionController;
use App\Controllers\SharedController;
use App\Core\Response;

$home = new HomeController();
$auth = new AuthController();
$admin = new AdminController();
$doctor = new DoctorController();
$reception = new ReceptionController();
$patient = new PatientController();
$shared = new SharedController();

$router->get('/', [$home, 'home']);
$router->get('/about', [$home, 'about']);
$router->get('/services', [$home, 'services']);
$router->get('/doctors', [$home, 'doctors']);
$router->get('/contact', [$home, 'contact']);

$router->get('/login', [$auth, 'showLogin'], ['guest']);
$router->post('/login', [$auth, 'login'], ['guest']);
$router->get('/register', [$auth, 'showRegister'], ['guest']);
$router->post('/register', [$auth, 'register'], ['guest']);
$router->get('/forgot-password', [$auth, 'showForgotPassword'], ['guest']);
$router->post('/forgot-password', [$auth, 'sendReset'], ['guest']);
$router->get('/reset-password', [$auth, 'showResetPassword'], ['guest']);
$router->post('/reset-password', [$auth, 'resetPassword'], ['guest']);
$router->get('/two-factor', [$auth, 'showTwoFactor'], ['guest']);
$router->post('/two-factor', [$auth, 'verifyTwoFactor'], ['guest']);
$router->post('/two-factor/resend', [$auth, 'resendTwoFactor'], ['guest']);
$router->post('/two-factor/cancel', [$auth, 'cancelTwoFactor'], ['guest']);
$router->post('/logout', [$auth, 'logout'], ['auth']);

$router->get('/admin', [$admin, 'dashboard'], ['auth', 'role:admin']);
$router->get('/admin/users', [$admin, 'users'], ['auth', 'role:admin']);
$router->post('/admin/users', [$admin, 'createUser'], ['auth', 'role:admin']);
$router->get('/admin/reports', [$admin, 'reports'], ['auth', 'role:admin']);
$router->post('/admin/reports', [$admin, 'updateBillingStatus'], ['auth', 'role:admin']);
$router->get('/admin/settings', [$admin, 'settings'], ['auth', 'role:admin']);
$router->post('/admin/settings', [$admin, 'updateSettings'], ['auth', 'role:admin']);
$router->get('/admin/audit', [$admin, 'audit'], ['auth', 'role:admin']);

$router->get('/doctor', [$doctor, 'dashboard'], ['auth', 'role:doctor']);
$router->get('/doctor/patients', [$doctor, 'patients'], ['auth', 'role:doctor']);
$router->get('/doctor/records', [$doctor, 'records'], ['auth', 'role:doctor']);
$router->post('/doctor/records', [$doctor, 'updateRecords'], ['auth', 'role:doctor']);
$router->get('/doctor/availability', [$doctor, 'availability'], ['auth', 'role:doctor']);
$router->post('/doctor/availability', [$doctor, 'saveAvailability'], ['auth', 'role:doctor']);
$router->post('/doctor/appointments/status', [$doctor, 'updateAppointmentStatus'], ['auth', 'role:doctor']);

$router->get('/reception', [$reception, 'dashboard'], ['auth', 'role:reception']);
$router->get('/reception/intake', [$reception, 'intake'], ['auth', 'role:reception']);
$router->post('/reception/intake', [$reception, 'storeIntake'], ['auth', 'role:reception']);
$router->get('/reception/queue', [$reception, 'queue'], ['auth', 'role:reception']);
$router->post('/reception/queue', [$reception, 'updateQueueStatus'], ['auth', 'role:reception']);
$router->get('/reception/appointments', [$reception, 'appointments'], ['auth', 'role:reception']);
$router->post('/reception/appointments', [$reception, 'storeAppointment'], ['auth', 'role:reception']);
$router->post('/reception/appointments/status', [$reception, 'updateAppointmentStatus'], ['auth', 'role:reception']);

$router->get('/patient', [$patient, 'dashboard'], ['auth', 'role:patient']);
$router->get('/patient/appointments', [$patient, 'appointments'], ['auth', 'role:patient']);
$router->post('/patient/appointments', [$patient, 'storeAppointment'], ['auth', 'role:patient']);
$router->post('/patient/appointments/status', [$patient, 'updateAppointmentStatus'], ['auth', 'role:patient']);
$router->get('/patient/results', [$patient, 'results'], ['auth', 'role:patient']);
$router->get('/patient/billing', [$patient, 'billing'], ['auth', 'role:patient']);
$router->get('/patient/notifications', [$patient, 'notifications'], ['auth', 'role:patient']);
$router->post('/patient/notifications', [$patient, 'markNotificationRead'], ['auth', 'role:patient']);

$router->get('/profile', [$shared, 'profile'], ['auth']);
$router->post('/profile', [$shared, 'updateProfile'], ['auth']);
$router->get('/reports', [$shared, 'reports'], ['auth']);
$router->get('/settings', [$shared, 'settings'], ['auth']);
$router->post('/settings/two-factor', [$shared, 'updateTwoFactor'], ['auth']);

$router->get('/dashboard', static fn () => Response::redirect(role_home(app('auth')->role() ?? 'patient')), ['auth']);
$router->get('/dashboard/admin', static fn () => Response::redirect('/admin'));
$router->get('/dashboard/admin/reports', static fn () => Response::redirect('/admin/reports'));
$router->get('/dashboard/admin/settings', static fn () => Response::redirect('/admin/settings'));
$router->get('/dashboard/admin/audit', static fn () => Response::redirect('/admin/audit'));
$router->get('/dashboard/doctor', static fn () => Response::redirect('/doctor'));
$router->get('/dashboard/reception', static fn () => Response::redirect('/reception'));
$router->get('/dashboard/patient', static fn () => Response::redirect('/patient'));
