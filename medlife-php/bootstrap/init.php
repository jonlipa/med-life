<?php

declare(strict_types=1);

use App\Core\App;
use App\Core\Auth;
use App\Core\Config;
use App\Core\Database;
use App\Core\GuestAuth;
use App\Core\Security;
use App\Core\Session;
use App\Core\View;
use App\Repositories\AppointmentRepository;
use App\Repositories\ClinicalRepository;
use App\Repositories\DoctorRepository;
use App\Repositories\PatientRepository;
use App\Repositories\UserRepository;

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

require_once ROOT_PATH . '/app/Support/helpers.php';

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = ROOT_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

$config = Config::load(ROOT_PATH);
App::set('config', $config);

date_default_timezone_set((string) $config->get('APP_TIMEZONE', 'Europe/Berlin'));

Security::redirectToHttpsIfNeeded($config, $_SERVER);
Security::applyHeaders($config, $_SERVER);

Session::start([
    'name' => $config->get('SESSION_COOKIE_NAME', 'med_life_portal'),
    'secure' => Security::secureCookie($config, $_SERVER),
    'samesite' => $config->get('SESSION_COOKIE_SAMESITE', 'Lax'),
]);

$view = new View(ROOT_PATH . '/app/Views');
App::set('view', $view);
App::set('auth', new GuestAuth());
App::set('db', null);
App::set('setup_mode', true);
App::set('db_status', [
    'available' => false,
    'message' => 'Databaza nuk eshte inicializuar ende.',
    'host' => (string) $config->get('DB_HOST', '127.0.0.1'),
    'port' => (string) $config->get('DB_PORT', '3306'),
    'name' => (string) $config->get('DB_NAME', 'medlife'),
    'user' => (string) $config->get('DB_USER', 'root'),
]);

try {
    $db = Database::connect($config);
} catch (Throwable $exception) {
    App::set('db_error', $exception->getMessage());
    App::set('db_status', [
        'available' => false,
        'message' => $exception->getMessage(),
        'host' => (string) $config->get('DB_HOST', '127.0.0.1'),
        'port' => (string) $config->get('DB_PORT', '3306'),
        'name' => (string) $config->get('DB_NAME', 'medlife'),
        'user' => (string) $config->get('DB_USER', 'root'),
    ]);

    return;
}

App::set('db', $db);
App::set('setup_mode', false);
App::set('db_status', [
    'available' => true,
    'message' => 'Databaza eshte gati.',
    'host' => (string) $config->get('DB_HOST', '127.0.0.1'),
    'port' => (string) $config->get('DB_PORT', '3306'),
    'name' => (string) $config->get('DB_NAME', 'medlife'),
    'user' => (string) $config->get('DB_USER', 'root'),
]);

try {
    $offset = (new DateTimeImmutable)->format('P');
    $db->exec("SET time_zone = '" . $offset . "'");
} catch (Throwable) {
}

$users = new UserRepository($db);
$doctors = new DoctorRepository($db);
$patients = new PatientRepository($db);
$appointments = new AppointmentRepository($db);
$clinical = new ClinicalRepository($db);

App::set('users', $users);
App::set('doctors', $doctors);
App::set('patients', $patients);
App::set('appointments', $appointments);
App::set('clinical', $clinical);
App::set('auth', new Auth($users));
