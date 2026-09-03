<?php $pageTitle = 'Profili'; ?>

<section class="panel-card">
    <p class="eyebrow">Profili</p>
    <h2>Te dhenat personale</h2>
    <form action="/profile" class="form-grid form-grid-2 mt-24" method="post">
        <?= csrf_field(); ?>
        <label><span>Emri i plote</span><input name="full_name" type="text" value="<?= e($user['full_name']); ?>"></label>
        <label><span>Email</span><input name="email" type="email" value="<?= e($user['email']); ?>"></label>
        <label><span>Telefoni</span><input name="phone" type="text" value="<?= e($user['phone']); ?>"></label>
        <label><span>Titulli</span><input name="title" type="text" value="<?= e($user['title']); ?>"></label>

        <?php if ($patientProfile): ?>
            <label class="span-2"><span>Adresa</span><input name="address" type="text" value="<?= e($patientProfile['address']); ?>"></label>
            <label class="span-2"><span>Kontakt emergjent</span><input name="emergency_contact" type="text" value="<?= e($patientProfile['emergency_contact']); ?>"></label>
            <label><span>Sigurimi</span><input name="insurance_provider" type="text" value="<?= e($patientProfile['insurance_provider']); ?>"></label>
            <label><span>Grupi i gjakut</span><input name="blood_type" type="text" value="<?= e($patientProfile['blood_type']); ?>"></label>
            <label class="span-2"><span>Shenime klinike</span><textarea name="clinical_notes" rows="4"><?= e($patientProfile['clinical_notes']); ?></textarea></label>
        <?php endif; ?>

        <?php if ($doctorProfile): ?>
            <div class="span-2 panel-subtle">
                <p><strong>Departamenti:</strong> <?= e($doctorProfile['department']); ?></p>
                <p><strong>Specializimi:</strong> <?= e($doctorProfile['specialization']); ?></p>
                <p><strong>Disponueshmeria:</strong> <?= e($doctorProfile['availability_text']); ?></p>
            </div>
        <?php endif; ?>

        <div class="span-2"><button class="button button-primary" type="submit">Ruaj profilin</button></div>
    </form>
</section>
