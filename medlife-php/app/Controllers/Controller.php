<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\AppointmentRepository;
use App\Repositories\ClinicalRepository;
use App\Repositories\DoctorRepository;
use App\Repositories\PatientRepository;
use App\Repositories\UserRepository;

abstract class Controller
{
    protected function render(string $template, array $data = [], ?string $layout = 'layouts/public', int $status = 200): void
    {
        Response::view($template, $data, $layout, $status);
    }

    protected function redirect(string $path, ?string $message = null, string $type = 'success'): never
    {
        if ($message !== null) {
            Session::flash('flash', ['type' => $type, 'message' => $message]);
        }

        Response::redirect($path);
    }

    protected function rememberInput(array $input): void
    {
        unset($input['password'], $input['_token']);
        Session::flash('old_input', $input);
    }

    protected function request(): Request
    {
        /** @var Request $request */
        $request = app('request');

        return $request;
    }

    protected function auth(): Auth
    {
        /** @var Auth $auth */
        $auth = app('auth');

        return $auth;
    }

    protected function user(): ?array
    {
        return $this->auth()->user();
    }

    protected function dbAvailable(): bool
    {
        return db_available();
    }

    protected function setupRequired(string $message, int $status = 503): never
    {
        Response::setupRequired($message, $status);
    }

    protected function usersRepo(): UserRepository
    {
        /** @var UserRepository $repository */
        $repository = app('users');

        if (!$repository instanceof UserRepository) {
            Response::setupRequired('Databaza duhet te jete aktive per te perdorur portalin e brendshem.');
        }

        return $repository;
    }

    protected function doctorsRepo(): DoctorRepository
    {
        /** @var DoctorRepository $repository */
        $repository = app('doctors');

        if (!$repository instanceof DoctorRepository) {
            Response::setupRequired('Databaza duhet te jete aktive per te ngarkuar te dhenat klinike.');
        }

        return $repository;
    }

    protected function patientsRepo(): PatientRepository
    {
        /** @var PatientRepository $repository */
        $repository = app('patients');

        if (!$repository instanceof PatientRepository) {
            Response::setupRequired('Databaza duhet te jete aktive per te ngarkuar te dhenat klinike.');
        }

        return $repository;
    }

    protected function appointmentsRepo(): AppointmentRepository
    {
        /** @var AppointmentRepository $repository */
        $repository = app('appointments');

        if (!$repository instanceof AppointmentRepository) {
            Response::setupRequired('Databaza duhet te jete aktive per te ngarkuar terminet.');
        }

        return $repository;
    }

    protected function clinical(): ClinicalRepository
    {
        /** @var ClinicalRepository $repository */
        $repository = app('clinical');

        if (!$repository instanceof ClinicalRepository) {
            Response::setupRequired('Databaza duhet te jete aktive per te ngarkuar modulit klinik.');
        }

        return $repository;
    }
}
