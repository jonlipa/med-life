<?php $pageTitle = 'Settings'; ?>

<section class="panel-card">
    <p class="eyebrow">Settings</p>
    <h2>Konfigurimi bazik i klinikes</h2>
    <form action="/admin/settings" class="form-grid form-grid-2 mt-24" method="post">
        <?= csrf_field(); ?>
        <label><span>Email klinike</span><input name="clinic_email" type="email" value="<?= e($settings['clinic_email'] ?? 'info@medlife-ks.com'); ?>"></label>
        <label><span>Telefoni support</span><input name="support_phone" type="text" value="<?= e($settings['support_phone'] ?? '+383 38 555 100'); ?>"></label>
        <label class="span-2"><span>Adresa</span><input name="clinic_address" type="text" value="<?= e($settings['clinic_address'] ?? 'Rr. Ilaz Kodra, Prishtine'); ?>"></label>
        <label><span>Session timeout</span><input name="session_timeout" type="text" value="<?= e($settings['session_timeout'] ?? '30 minuta'); ?>"></label>
        <label><span>Portali i pacienteve aktiv</span><input name="patient_portal_enabled" type="text" value="<?= e($settings['patient_portal_enabled'] ?? 'true'); ?>"></label>
        <label><span>Njoftimet aktive</span><input name="notifications_enabled" type="text" value="<?= e($settings['notifications_enabled'] ?? 'true'); ?>"></label>
        <div class="span-2"><button class="button button-primary" type="submit">Ruaj settings</button></div>
    </form>
</section>
