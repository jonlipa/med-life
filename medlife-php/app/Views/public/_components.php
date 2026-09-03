<?php

declare(strict_types=1);

if (!function_exists('ml_icon')) {
    function ml_icon(string $name, string $class = ''): string
    {
        $paths = [
            'activity' => '<path d="M22 12h-4l-3 8L9 4l-3 8H2"></path>',
            'arrow' => '<path d="M5 12h14"></path><path d="m13 6 6 6-6 6"></path>',
            'baby' => '<circle cx="12" cy="12" r="9"></circle><path d="M9 10h.01M15 10h.01M8.5 14.5c2 2 5 2 7 0"></path>',
            'bell' => '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 8-3 8h18s-3-1-3-8"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path>',
            'bolt' => '<path d="m13 2-7 12h6l-1 8 7-12h-6z"></path>',
            'calendar' => '<rect x="3" y="5" width="18" height="16" rx="3"></rect><path d="M16 3v4M8 3v4M3 10h18"></path>',
            'clock' => '<circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path>',
            'document' => '<path d="M7 3h7l4 4v14H7z"></path><path d="M14 3v5h5M10 12h6M10 16h5"></path>',
            'doctor' => '<circle cx="12" cy="7" r="3"></circle><path d="M6 21v-2a6 6 0 0 1 12 0v2"></path><path d="M9 17h6M12 14v6"></path>',
            'eye' => '<path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6z"></path><circle cx="12" cy="12" r="3"></circle>',
            'family' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.8"></path><path d="M16 3.2a4 4 0 0 1 0 7.6"></path>',
            'folder' => '<path d="M3 7.5A2.5 2.5 0 0 1 5.5 5H10l2 2h6.5A2.5 2.5 0 0 1 21 9.5V17a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>',
            'headset' => '<path d="M4 13a8 8 0 0 1 16 0"></path><path d="M4 13v3a2 2 0 0 0 2 2h1v-7H6a2 2 0 0 0-2 2zM20 13v3a2 2 0 0 1-2 2h-1v-7h1a2 2 0 0 1 2 2zM14 20h2a4 4 0 0 0 4-4"></path>',
            'heart' => '<path d="M19 14c1.5-1.5 3-3.2 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.8 0-3 .5-4.5 2-1.5-1.5-2.7-2-4.5-2A5.5 5.5 0 0 0 2 8.5C2 10.8 3.5 12.5 5 14l7 7z"></path><path d="M8 12h3l1-2 2 5 1-3h2"></path>',
            'lab' => '<path d="M10 2v6.5l-5 8.5a2 2 0 0 0 1.7 3h10.6a2 2 0 0 0 1.7-3l-5-8.5V2"></path><path d="M8.5 2h7M7 16h10"></path>',
            'lock' => '<rect x="5" y="11" width="14" height="10" rx="2"></rect><path d="M8 11V8a4 4 0 0 1 8 0v3"></path>',
            'mail' => '<rect x="3" y="5" width="18" height="14" rx="3"></rect><path d="m3 7 9 7 9-7"></path>',
            'map' => '<path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0z"></path><circle cx="12" cy="10" r="3"></circle>',
            'monitor' => '<rect x="4" y="5" width="16" height="11" rx="2"></rect><path d="M9 20h6M12 16v4"></path>',
            'phone' => '<path d="M22 16.9v2.4a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.3 19.3 0 0 1-6-6 19.8 19.8 0 0 1-3.1-8.6 2 2 0 0 1 2-2.2h2.4a2 2 0 0 1 2 1.7c.1.9.3 1.7.6 2.5a2 2 0 0 1-.4 2.1l-1 1a16 16 0 0 0 6 6l1-1a2 2 0 0 1 2.1-.4c.8.3 1.6.5 2.5.6a2 2 0 0 1 1.7 2z"></path>',
            'scan' => '<rect x="4" y="4" width="16" height="16" rx="2"></rect><path d="M8 4v16M16 4v16M4 9h16M4 15h16"></path>',
            'search' => '<circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path>',
            'send' => '<path d="M22 2 11 13"></path><path d="m22 2-7 20-4-9-9-4z"></path>',
            'shield' => '<path d="M12 3 5 6v6c0 4.4 2.8 7.7 7 9 4.2-1.3 7-4.6 7-9V6z"></path><path d="m9 12 2 2 4-5"></path>',
            'smile' => '<circle cx="12" cy="12" r="9"></circle><path d="M8.5 10h.01M15.5 10h.01M8.5 14.5c2 2 5 2 7 0"></path>',
            'user' => '<circle cx="12" cy="7" r="4"></circle><path d="M5.5 21a6.5 6.5 0 0 1 13 0"></path>',
            'users' => '<path d="M17 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9.5" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.8M16 3.2a4 4 0 0 1 0 7.6"></path>',
            'xray' => '<rect x="3" y="3" width="18" height="18" rx="2"></rect><path d="M8 7c2 2.5 2 7.5 0 10M16 7c-2 2.5-2 7.5 0 10M12 8v8"></path>',
        ];

        $classAttr = trim('ml-icon ' . $class);
        return '<svg class="' . e($classAttr) . '" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">' . ($paths[$name] ?? $paths['activity']) . '</svg>';
    }
}

if (!function_exists('ml_logo')) {
    function ml_logo(string $href = '/', string $class = ''): string
    {
        $tag = $href === '#' || $href === '' ? 'span' : 'a';
        $hrefAttr = $tag === 'a' ? ' href="' . e($href) . '" aria-label="Med Life"' : ' aria-hidden="true"';

        return '<' . $tag . ' class="ml-logo ' . e($class) . '"' . $hrefAttr . '>' .
            '<span class="ml-logo__mark">' . ml_icon('activity') . '</span>' .
            '<span class="ml-logo__text">Med Life</span>' .
            '</' . $tag . '>';
    }
}

if (!function_exists('ml_key')) {
    function ml_key(string $value): string
    {
        $value = strtr($value, [
            'Ë' => 'E',
            'ë' => 'e',
            'Ç' => 'C',
            'ç' => 'c',
        ]);

        return strtolower($value);
    }
}

if (!function_exists('ml_public_services')) {
    function ml_public_services(array $source = []): array
    {
        $defaults = [
            ['name' => 'Kardiologji', 'department' => 'Kardiologji', 'icon' => 'heart', 'description' => 'Kontroll kardiologjik, EKG dhe plan trajtimi i personalizuar.', 'fee' => 60.00, 'duration_minutes' => 40],
            ['name' => 'Laborator', 'department' => 'Laborator', 'icon' => 'lab', 'description' => 'Analiza gjaku, urine dhe panele kontrolli të plota.', 'fee' => 25.00, 'duration_minutes' => 20],
            ['name' => 'Mjekësi Familjare', 'department' => 'Mjekësi Familjare', 'icon' => 'family', 'description' => 'Kontrolle të përgjithshme dhe ndjekje periodike e shëndetit.', 'fee' => 35.00, 'duration_minutes' => 25],
            ['name' => 'Pediatri', 'department' => 'Pediatri', 'icon' => 'baby', 'description' => 'Vizita pediatrike dhe ndjekje e zhvillimit të fëmijës.', 'fee' => 45.00, 'duration_minutes' => 30],
            ['name' => 'Radiologji', 'department' => 'Radiologji', 'icon' => 'xray', 'description' => 'Ekografi, radiografi dhe raporte diagnostike me pajisje moderne.', 'fee' => 80.00, 'duration_minutes' => 45],
        ];

        $byKey = [];
        foreach ($source as $item) {
            $key = ml_key((string) ($item['department'] ?? $item['name'] ?? ''));
            $byKey[$key] = $item;
        }

        foreach ($defaults as &$service) {
            $key = ml_key($service['department']);
            if (isset($byKey[$key])) {
                $service['id'] = $byKey[$key]['id'] ?? null;
            }
        }
        unset($service);

        return $defaults;
    }
}

if (!function_exists('ml_public_doctors')) {
    function ml_public_doctors(array $source = []): array
    {
        $defaults = [
            ['id' => 1, 'full_name' => 'Dr. Arben Hoxha', 'department' => 'Kardiologji', 'specialization' => 'Kardiolog', 'room' => 'B-03', 'availability_text' => '08:00 - 17:00', 'bio' => 'Ekspert në diagnostikimin dhe trajtimin e sëmundjeve kardiovaskulare. Përkushtim për parandalim, kujdes dhe shëndet të zemrës.', 'short_bio' => 'Konsulta kardiake, EKG dhe ndjekje e pacientëve me risk kardiovaskular.', 'image_path' => 'assets/images/doctors/doctor-1.png', 'patients' => '3,287+'],
            ['id' => 2, 'full_name' => 'Dr. Elona Berisha', 'department' => 'Pediatri', 'specialization' => 'Pediatre', 'room' => 'A-11', 'availability_text' => '08:00 - 16:00', 'bio' => 'Specialiste në kujdesin shëndetësor të fëmijëve, nga lindja deri në adoleshencë. Kujdes i butë, profesional dhe i personalizuar.', 'short_bio' => 'Vizita pediatrike, imunizim dhe ndjekje e zhvillimit të fëmijës.', 'image_path' => 'assets/images/doctors/doctor-3.png', 'patients' => '2,145+'],
            ['id' => 3, 'full_name' => 'Dr. Gent Basha', 'department' => 'Neurologji', 'specialization' => 'Neurolog', 'room' => 'C-04', 'availability_text' => '09:00 - 17:00', 'bio' => 'Ekspert në diagnostikimin dhe trajtimin e çrregullimeve neurologjike. Përkushtim për cilësi, saktësi dhe mirëqenie të pacientit.', 'short_bio' => 'Diagnostikim neurologjik, migrenë dhe ndjekje e pacientëve kronikë.', 'image_path' => 'assets/images/doctors/doctor-2.png', 'patients' => '1,892+'],
            ['id' => 4, 'full_name' => 'Dr. Mira Dervishi', 'department' => 'Radiologji', 'specialization' => 'Radiologe', 'room' => 'R-02', 'availability_text' => '08:30 - 15:30', 'bio' => 'Ekografi, interpretim imazhesh dhe raporte diagnostike të sakta me teknologji moderne për diagnostikim të besueshëm.', 'short_bio' => 'Ekografi, interpretim imazhesh dhe raporte diagnostike të sakta.', 'image_path' => 'assets/images/doctors/doctor-4.png', 'patients' => '1,430+'],
        ];

        $sourceIds = [];
        foreach ($source as $item) {
            $sourceIds[(string) ($item['full_name'] ?? '')] = $item['id'] ?? null;
        }

        foreach ($defaults as &$doctor) {
            if (isset($sourceIds[$doctor['full_name']]) && $sourceIds[$doctor['full_name']] !== null) {
                $doctor['id'] = $sourceIds[$doctor['full_name']];
            }
        }
        unset($doctor);

        return $defaults;
    }
}

if (!function_exists('ml_doctor_image')) {
    function ml_doctor_image(string $path): string
    {
        $normalized = preg_replace('#^assets/#', '', ltrim($path, '/')) ?? $path;
        $optimized = 'images/optimized/' . pathinfo($normalized, PATHINFO_FILENAME) . '.jpg';
        if (is_file(base_path('public/assets/' . $optimized))) {
            return asset($optimized);
        }

        return asset($normalized);
    }
}

if (!function_exists('ml_button')) {
    function ml_button(string $label, string $href, string $variant = 'primary', string $icon = '', string $class = ''): string
    {
        $iconHtml = $icon !== '' ? ml_icon($icon) : '';
        return '<a class="ml-btn ml-btn--' . e($variant) . ' ' . e($class) . '" href="' . e($href) . '">' . $iconHtml . '<span>' . e($label) . '</span>' . ($icon === 'arrow' ? '' : '') . '</a>';
    }
}

if (!function_exists('ml_stat_card')) {
    function ml_stat_card(string $value, string $label, string $description, string $icon): string
    {
        return '<article class="ml-stat-card" data-reveal>' .
            '<span class="ml-card-icon ml-card-icon--solid">' . ml_icon($icon) . '</span>' .
            '<div><strong>' . e($value) . '</strong><span>' . e($label) . '</span><p>' . e($description) . '</p></div>' .
            '</article>';
    }
}

if (!function_exists('ml_service_card')) {
    function ml_service_card(array $service, string $variant = '', string $href = '/register'): string
    {
        $class = trim('ml-service-card ' . ($variant !== '' ? 'ml-service-card--' . $variant : ''));
        return '<article class="' . e($class) . '" data-reveal>' .
            '<span class="ml-card-icon">' . ml_icon((string) ($service['icon'] ?? 'heart')) . '</span>' .
            '<h3>' . e((string) $service['name']) . '</h3>' .
            '<p>' . e((string) $service['description']) . '</p>' .
            '<div class="ml-service-card__meta"><strong>' . e(money($service['fee'])) . '</strong><span>' . ml_icon('clock') . e((string) $service['duration_minutes']) . ' min</span></div>' .
            '<a class="ml-card-action" href="' . e($href) . '"><span>Rezervo termin</span>' . ml_icon('arrow') . '</a>' .
            '</article>';
    }
}

if (!function_exists('ml_doctor_card')) {
    function ml_doctor_card(array $doctor, string $variant = 'compact'): string
    {
        $image = ml_doctor_image((string) $doctor['image_path']);
        $class = 'ml-doctor-card ml-doctor-card--' . $variant;
        $bio = $variant === 'directory' ? $doctor['short_bio'] : $doctor['bio'];

        return '<article class="' . e($class) . '" data-reveal data-department="' . e((string) $doctor['department']) . '" data-name="' . e(ml_key((string) $doctor['full_name'])) . '" data-spec="' . e(ml_key((string) $doctor['specialization'])) . '">' .
            '<div class="ml-doctor-card__media"><img class="ml-doctor-card__backdrop" src="' . e($image) . '" alt="" loading="lazy" aria-hidden="true"><img class="ml-doctor-card__photo" src="' . e($image) . '" alt="' . e((string) $doctor['full_name']) . '" loading="lazy"></div>' .
            '<div class="ml-doctor-card__body"><span class="ml-specialty">' . e((string) $doctor['department']) . '</span>' .
            '<h3>' . e((string) $doctor['full_name']) . '</h3>' .
            '<p class="ml-doctor-card__spec">' . e((string) $doctor['specialization']) . '</p>' .
            '<p class="ml-doctor-card__bio">' . e((string) $bio) . '</p>' .
            '<div class="ml-doctor-meta"><span>' . ml_icon('map') . 'Dhoma: ' . e((string) $doctor['room']) . '</span><span>' . ml_icon('clock') . e((string) $doctor['availability_text']) . '</span></div>' .
            '<a class="ml-card-action" href="/contact"><span>Lexo më shumë</span>' . ml_icon('arrow') . '</a>' .
            '</div></article>';
    }
}

if (!function_exists('ml_cta_section')) {
    function ml_cta_section(string $title = "Gati për t'u kujdesur për shëndetin tuaj?", string $button = 'Rezervo termin tani', string $href = '/register', string $class = ''): string
    {
        return '<section class="ml-cta-band ' . e($class) . '" aria-label="Rezervo termin">' .
            '<div class="ml-container ml-cta-band__inner"><span class="ml-card-icon ml-card-icon--solid">' . ml_icon('calendar') . '</span>' .
            '<div><h2>' . e($title) . '</h2><p>Rezervoni termin tuaj online për pak minuta dhe kurseni kohë.</p></div>' .
            '<a class="ml-btn ml-btn--light" href="' . e($href) . '"><span>' . e($button) . '</span>' . ml_icon('arrow') . '</a><span class="ml-pulse-line" aria-hidden="true"></span></div></section>';
    }
}

if (!function_exists('ml_contact_info_card')) {
    function ml_contact_info_card(string $icon, string $title, string $value, string $description, string $class = ''): string
    {
        return '<article class="ml-contact-info-card ' . e($class) . '">' .
            '<span class="ml-card-icon ml-card-icon--solid">' . ml_icon($icon) . '</span>' .
            '<div><h2>' . e($title) . '</h2><strong>' . e($value) . '</strong><p>' . e($description) . '</p></div>' .
            '</article>';
    }
}
