<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Support\FallbackData;

final class HomeController extends Controller
{
    public function home(): void
    {
        $this->render('public/home', [
            'featuredDoctors' => $this->dbAvailable()
                ? $this->doctorsRepo()->featuredDoctors(3)
                : FallbackData::featuredDoctors(3),
            'services' => $this->dbAvailable()
                ? array_slice($this->clinical()->listServices(), 0, 4)
                : array_slice(FallbackData::services(), 0, 4),
            'stats' => $this->dbAvailable()
                ? [
                    ['label' => 'Paciente aktive', 'value' => $this->patientsRepo()->countPatients()],
                    ['label' => 'Doktore aktiv', 'value' => $this->doctorsRepo()->countDoctors()],
                    ['label' => 'Termine ne sistem', 'value' => $this->appointmentsRepo()->countAll()],
                ]
                : FallbackData::stats(),
        ]);
    }

    public function about(): void
    {
        $this->render('public/about', [
            'hideSetupNotice' => true,
            'timeline' => [
                ['title' => 'Kujdes multidisiplinar', 'description' => 'Koordinim mes mjekesise familjare, laboratorit dhe specialisteve.'],
                ['title' => 'Portal i unifikuar', 'description' => 'Qasje e shpejte ne rezultate, billing dhe dashboards sipas rolit.'],
                ['title' => 'Fokus ne shpejtesi', 'description' => 'Rrjedha intake -> termin -> record -> rezultat -> fature ne nje sistem te vetem.'],
            ],
        ]);
    }

    public function services(): void
    {
        $this->render('public/services', [
            'services' => $this->dbAvailable()
                ? $this->clinical()->listServices()
                : FallbackData::services(),
        ]);
    }

    public function doctors(): void
    {
        $this->render('public/doctors', [
            'doctors' => $this->dbAvailable()
                ? $this->doctorsRepo()->listDoctors()
                : FallbackData::doctors(),
        ]);
    }

    public function contact(): void
    {
        $settings = $this->dbAvailable()
            ? $this->clinical()->getSettings()
            : FallbackData::contact();

        $this->render('public/contact', [
            'hideSetupNotice' => true,
            'contact' => [
                'clinic_email' => $settings['clinic_email'] ?? 'info@medlife-ks.com',
                'support_phone' => $settings['support_phone'] ?? '+383 38 555 100',
                'address' => $settings['clinic_address'] ?? 'Rr. Ilaz Kodra, Prishtine',
            ],
        ]);
    }
}
