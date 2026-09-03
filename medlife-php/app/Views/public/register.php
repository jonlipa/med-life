<?php
require_once base_path('app/Views/public/_components.php');
$doctorOptions = $doctors ?? [];
?>

<section class="ml-register-page" aria-labelledby="register-title">
    <div class="ml-register-hero">
        <div class="ml-container ml-register-hero__inner">
            <div data-reveal>
                <span class="ml-label">REGJISTRIM PACIENTI</span>
                <h1 id="register-title">Krijo llogarinë tënde dhe lidhu direkt me ekipin klinik.</h1>
                <p class="ml-lead">Plotëso të dhënat e tua për të krijuar llogarinë dhe për të marrë kujdes shëndetësor të personalizuar, të sigurt dhe të besueshëm.</p>
            </div>
            <div class="ml-register-hero__visual" data-reveal>
                <img class="ml-register-doctor-backdrop" src="<?= e(asset('images/optimized/doctor-hero.jpg')); ?>" alt="" aria-hidden="true" width="420" height="360">
                <img class="ml-register-doctor-photo" src="<?= e(asset('images/optimized/doctor-hero.jpg')); ?>" alt="Doktor Med Life" width="420" height="360">
                <div class="ml-floating-card ml-register-benefits-mini">
                    <h2>Pse të regjistroheni?</h2>
                    <p><?= ml_icon('users'); ?> Qasje e shpejtë te mjekët tanë</p>
                    <p><?= ml_icon('calendar'); ?> Rezervim termini online</p>
                    <p><?= ml_icon('document'); ?> Historik mjekësor i sigurt</p>
                    <p><?= ml_icon('bell'); ?> Njoftime dhe kujtesa automatike</p>
                    <strong>Kujdes modern. <span>Shëndet për jetën.</span></strong>
                </div>
            </div>
        </div>
    </div>

    <div class="ml-container ml-register-layout">
        <div class="ml-register-card" data-reveal>
            <div class="ml-form-title">
                <span class="ml-card-icon"><?= ml_icon('user'); ?></span>
                <div>
                    <h2>Të dhënat personale</h2>
                    <p>Plotëso të dhënat e tua bazë me kujdes.</p>
                </div>
            </div>

            <form action="/register" class="ml-form ml-form--register" method="post">
                <?= csrf_field(); ?>
                <label><span>Emri i plotë</span><div class="ml-input-shell"><?= ml_icon('user'); ?><input name="full_name" type="text" placeholder="Shkruani emrin dhe mbiemrin tuaj" value="<?= e(old('full_name')); ?>" autocomplete="name"></div></label>
                <label><span>Email</span><div class="ml-input-shell"><?= ml_icon('mail'); ?><input name="email" type="email" placeholder="email@example.com" value="<?= e(old('email')); ?>" autocomplete="email"></div></label>
                <label><span>Telefoni</span><div class="ml-input-shell"><?= ml_icon('phone'); ?><input name="phone" type="text" placeholder="+383 44 123 456" value="<?= e(old('phone')); ?>" autocomplete="tel"></div></label>
                <label><span>Fjalëkalimi</span><div class="ml-input-shell"><?= ml_icon('lock'); ?><input name="password" type="password" placeholder="Krijo një fjalëkalim të sigurt" autocomplete="new-password"><?= ml_icon('eye', 'ml-input-trailing'); ?></div></label>
                <label><span>Data e lindjes</span><div class="ml-input-shell"><?= ml_icon('calendar'); ?><input name="date_of_birth" type="date" value="<?= e(old('date_of_birth')); ?>"></div></label>
                <label><span>Grupi i gjakut</span><div class="ml-input-shell"><?= ml_icon('activity'); ?><input name="blood_type" type="text" placeholder="Zgjidh grupin e gjakut" value="<?= e(old('blood_type')); ?>"></div></label>
                <label class="ml-span-2"><span>Adresa</span><div class="ml-input-shell"><?= ml_icon('map'); ?><input name="address" type="text" placeholder="Shkruani adresën tuaj të plotë" value="<?= e(old('address')); ?>" autocomplete="street-address"></div></label>
                <label><span>Kontakt emergjent</span><div class="ml-input-shell"><?= ml_icon('phone'); ?><input name="emergency_contact" type="text" placeholder="Emri dhe numri i telefonit të kontaktit emergjent" value="<?= e(old('emergency_contact')); ?>"></div></label>
                <label><span>Sigurimi</span><div class="ml-input-shell"><?= ml_icon('shield'); ?><input name="insurance_provider" type="text" placeholder="Emri i sigurimit shëndetësor" value="<?= e(old('insurance_provider')); ?>"></div></label>
                <label>
                    <span>Doktori kryesor</span>
                    <div class="ml-input-shell"><?= ml_icon('doctor'); ?><select name="current_doctor_id">
                        <?php foreach ($doctorOptions as $doctor): ?>
                            <?php $selected = (string) old('current_doctor_id') === (string) $doctor['id']; ?>
                            <option value="<?= e((string) $doctor['id']); ?>"<?= $selected ? ' selected' : ''; ?>><?= e($doctor['full_name'] . ' / ' . $doctor['specialization']); ?></option>
                        <?php endforeach; ?>
                    </select></div>
                </label>
                <label><span>Alergji</span><div class="ml-input-shell"><?= ml_icon('activity'); ?><input name="allergies" type="text" placeholder="Shkruani nëse keni alergji ndaj ilaçeve, ushqimeve, etj." value="<?= e(old('allergies')); ?>"></div></label>
                <label class="ml-span-2"><span>Shënime shtesë</span><textarea name="notes" rows="4" placeholder="Çdo informacion tjetër që dëshironi të ndani me ekipin tonë mjekësor"><?= e(old('notes')); ?></textarea></label>
                <p class="ml-privacy-note ml-span-2"><?= ml_icon('lock'); ?> Të dhënat tuaja janë të sigurta dhe të mbrojtura sipas standardeve më të larta të privatësisë.</p>
                <button class="ml-btn ml-btn--primary ml-register-submit" type="submit"><?= ml_icon('lock'); ?><span>Krijo llogarinë</span><?= ml_icon('arrow'); ?></button>
            </form>
        </div>

        <aside class="ml-register-sidebar" data-reveal>
            <div class="ml-benefit-card">
                <h2>Me Med Life, gjithmonë një hap para.</h2>
                <p>Pasi të krijoni llogarinë, do të mund të përdorni të gjitha shërbimet tona online në mënyrë të thjeshtë dhe të sigurt.</p>
                <ul>
                    <li><?= ml_icon('calendar'); ?><span><strong>Rezervo termin</strong>Zgjidhni orarin që ju përshtatet më shumë.</span></li>
                    <li><?= ml_icon('headset'); ?><span><strong>Konsultohu online</strong>Bisedo me mjekët tanë nga komoditeti i shtëpisë.</span></li>
                    <li><?= ml_icon('document'); ?><span><strong>Historiku mjekësor</strong>Ruani dhe aksesoni të gjitha të dhënat tuaja shëndetësore.</span></li>
                    <li><?= ml_icon('bell'); ?><span><strong>Njoftime të personalizuara</strong>Merrni kujtesa për kontrolle, analiza dhe medikamente.</span></li>
                </ul>
                <div class="ml-privacy-badge"><?= ml_icon('shield'); ?><span><strong>Privatësia juaj është prioriteti ynë.</strong>Të dhënat tuaja nuk ndahen me të tretë.</span></div>
            </div>
        </aside>
    </div>
</section>
