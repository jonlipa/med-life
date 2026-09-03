<?php
$method = (string) ($method ?? 'authenticator');
$isEmailMethod = $method === 'email';
?>

<section class="section">
    <div class="container auth-grid">
        <div class="panel-card">
            <p class="eyebrow">Verifikim me dy hapa</p>
            <h1><?= e($isEmailMethod ? 'Shkruani kodin e derguar ne email.' : 'Shkruani kodin nga Authenticator.'); ?></h1>
            <p class="lead">
                <?php if ($isEmailMethod): ?>
                    Per llogarine <?= e($identifierHint ?? 'tuaj'); ?> vendosni kodin 6-shifror qe ju erdhi ne email.
                <?php else: ?>
                    Per llogarine <?= e($identifierHint ?? 'tuaj'); ?> vendosni kodin 6-shifror qe gjeneron aplikacioni Authenticator.
                <?php endif; ?>
            </p>

            <form action="/two-factor" class="form-grid" method="post">
                <?= csrf_field(); ?>
                <label>
                    <span>Kodi i verifikimit</span>
                    <input autocomplete="one-time-code" inputmode="numeric" maxlength="6" name="code" pattern="[0-9]{6}" placeholder="000000" type="text">
                </label>
                <button class="button button-primary" type="submit">Verifiko hyrjen</button>
            </form>

            <form action="/two-factor/cancel" class="mt-24" method="post">
                <?= csrf_field(); ?>
                <button class="button button-secondary" type="submit">Anulo dhe kthehu te login</button>
            </form>

            <?php if ($isEmailMethod && ($canResend ?? false)): ?>
                <form action="/two-factor/resend" class="mt-24" method="post">
                    <?= csrf_field(); ?>
                    <button class="button button-secondary" type="submit">Ridergo kodin ne email</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="hero-card hero-card-clinic">
            <img alt="Authenticator" src="<?= e(asset('images/login-medical.jpg')); ?>">
        </div>
    </div>
</section>
