<?php
require_once base_path('app/Views/public/_components.php');

$clinicEmail = $contact['clinic_email'] ?? 'info@medlife-ks.com';
$supportPhone = $contact['support_phone'] ?? '+383 38 555 100';
$address = $contact['address'] ?? 'Rr. Ilaz Kodra, Prishtinë';
?>

<section class="ml-contact-page" aria-labelledby="contact-title">
    <div class="ml-container ml-contact-layout">
        <div class="ml-contact-left" data-reveal>
            <span class="ml-label">KONTAKT</span>
            <h1 id="contact-title">Qendra juaj e besimit, kujdesi ynë i përditshëm.</h1>
            <p class="ml-lead">Për çdo pyetje, koordinim apo informacion, ekipi i Med Life është këtu për ju. Na kontaktoni në mënyrën që ju përshtatet më së miri.</p>

            <div class="ml-contact-info-list">
                <?= ml_contact_info_card('mail', 'Email klinike', $clinicEmail, 'Për pyetje të përgjithshme dhe koordinime.'); ?>
                <?= ml_contact_info_card('phone', 'Linjë mbështetjeje', $supportPhone, 'E hënë - e premte, 08:00 - 18:00'); ?>
                <?= ml_contact_info_card('map', 'Adresa', $address, '10000, Republika e Kosovës'); ?>
                <?= ml_contact_info_card('clock', 'Orari i punës', 'E hënë - e premte: 08:00 - 18:00', 'E shtunë: 09:00 - 14:00'); ?>
                <?= ml_contact_info_card('shield', 'Support emergjent', '+383 44 123 456', '24/7 për raste urgjente jashtë orarit të punës.', 'is-emergency'); ?>
            </div>
        </div>

        <div class="ml-contact-right" data-reveal>
            <div class="ml-contact-hub">
                <div class="ml-contact-hub__content">
                    <h2>Med Life <span>Hub</span></h2>
                    <p>Portali ynë i integruar për menaxhimin e kujdesit shëndetësor. Rezervoni termin, kontaktoni supportin ose menaxhoni të dhënat tuaja në një vend të vetëm.</p>
                    <div class="ml-contact-hub__chips">
                        <span><?= ml_icon('calendar'); ?> Termine</span>
                        <span><?= ml_icon('headset'); ?> Support</span>
                        <span><?= ml_icon('monitor'); ?> Portal</span>
                    </div>
                </div>
                <div class="ml-contact-hub__scene">
                    <?= ml_logo('#'); ?>
                </div>
            </div>

            <div class="ml-contact-form-card">
                <h2>Na shkruani</h2>
                <p>Plotësoni formularin dhe ne do t'ju kontaktojmë sa më shpejt.</p>

                <form class="ml-form ml-form--contact" action="/contact" method="get">
                    <label><span>Emri</span><input name="first_name" type="text" placeholder="Shkruani emrin tuaj" autocomplete="given-name"></label>
                    <label><span>Mbiemri</span><input name="last_name" type="text" placeholder="Shkruani mbiemrin tuaj" autocomplete="family-name"></label>
                    <label><span>Email</span><input name="email" type="email" placeholder="email@example.com" autocomplete="email"></label>
                    <label><span>Telefoni</span><input name="phone" type="tel" placeholder="+383 XX XXX XXX" autocomplete="tel"></label>
                    <label class="ml-span-2"><span>Mesazhi</span><textarea name="message" rows="4" placeholder="Shkruani mesazhin tuaj këtu..."></textarea></label>
                    <button class="ml-btn ml-btn--primary ml-span-2" type="submit"><?= ml_icon('send'); ?><span>Dërgo mesazhin</span></button>
                </form>

                <p class="ml-privacy-note"><?= ml_icon('lock'); ?> Të dhënat tuaja janë të sigurta dhe nuk ndahen me palë të treta.</p>
            </div>
        </div>
    </div>
</section>
