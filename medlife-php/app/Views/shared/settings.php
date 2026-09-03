<?php
$pageTitle = 'Settings';
$twoFactorState = $twoFactor ?? [];
$twoFactorEnabled = (bool) ($twoFactorState['enabled'] ?? false);
$emailOtpEnabled = (bool) ($twoFactorState['email_otp_enabled'] ?? false);
$pendingSecret = (string) ($twoFactorState['pending_secret'] ?? '');
$otpauthUri = (string) ($twoFactorState['otpauth_uri'] ?? '');
?>

<section class="grid-2">
    <article class="panel-card">
        <p class="eyebrow">Siguria e llogarise</p>
        <h2>Verifikim me dy hapa (Authenticator)</h2>

        <div class="stack-cards mt-24">
            <div class="line-item">
                <span>Statusi 2FA</span>
                <strong><?= e($twoFactorEnabled ? 'Aktive' : 'Jo aktive'); ?></strong>
            </div>
            <div class="line-item">
                <span>Verifikim me email</span>
                <strong><?= e($emailOtpEnabled ? 'Aktive' : 'Jo aktive'); ?></strong>
            </div>
            <div class="line-item">
                <span>Llogaria</span>
                <strong><?= e((string) (($user['email'] ?? '') !== '' ? $user['email'] : ($user['username'] ?? 'user'))); ?></strong>
            </div>
        </div>

        <?php if ($twoFactorEnabled): ?>
            <div class="panel-subtle mt-24">
                <strong>2FA eshte aktive.</strong>
                <p class="muted">Per caktivizim, konfirmoni fjalkalimin aktual.</p>
            </div>

            <form action="/settings/two-factor" class="form-grid mt-24" method="post">
                <?= csrf_field(); ?>
                <input name="action" type="hidden" value="disable">
                <label>
                    <span>Fjalkalimi aktual</span>
                    <input autocomplete="current-password" name="current_password" type="password">
                </label>
                <button class="button button-secondary" type="submit">Caktivizo 2FA</button>
            </form>
        <?php else: ?>
            <?php if ($pendingSecret === ''): ?>
                <div class="panel-subtle mt-24">
                    <strong>Aktivizo 2FA</strong>
                    <p class="muted">Gjenero sekretin, shtoje ne Google/Microsoft Authenticator dhe konfirmo me kodin 6-shifror.</p>
                </div>

                <form action="/settings/two-factor" class="mt-24" method="post">
                    <?= csrf_field(); ?>
                    <input name="action" type="hidden" value="generate">
                    <button class="button button-primary" type="submit">Gjenero sekretin 2FA</button>
                </form>
            <?php else: ?>
                <div class="panel-subtle mt-24">
                    <strong>Hapi 1: Shto sekretin ne Authenticator</strong>
                    <p class="muted">Mund te vendosesh manualisht kete key ose URI ne aplikacionin Authenticator.</p>
                </div>

                <form class="form-grid mt-24" onsubmit="return false;">
                    <label>
                        <span>Secret key</span>
                        <input readonly type="text" value="<?= e($pendingSecret); ?>">
                    </label>
                    <label>
                        <span>Provisioning URI</span>
                        <textarea readonly><?= e($otpauthUri); ?></textarea>
                    </label>
                </form>

                <form action="/settings/two-factor" class="form-grid mt-24" method="post">
                    <?= csrf_field(); ?>
                    <input name="action" type="hidden" value="confirm">
                    <label>
                        <span>Hapi 2: Kodi 6-shifror</span>
                        <input autocomplete="one-time-code" inputmode="numeric" maxlength="6" name="code" pattern="[0-9]{6}" placeholder="000000" type="text">
                    </label>
                    <button class="button button-primary" type="submit">Konfirmo dhe aktivizo 2FA</button>
                </form>

                <form action="/settings/two-factor" class="mt-24" method="post">
                    <?= csrf_field(); ?>
                    <input name="action" type="hidden" value="generate">
                    <button class="button button-secondary" type="submit">Rigjenero sekretin</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>

        <div class="panel-subtle mt-24">
            <strong>Verifikim me email (OTP)</strong>
            <p class="muted">Nese aktivizohet, gjate login-it do te marresh kod 6-shifror ne email te ruajtur ne profil.</p>
        </div>

        <?php if ($emailOtpEnabled): ?>
            <form action="/settings/two-factor" class="form-grid mt-24" method="post">
                <?= csrf_field(); ?>
                <input name="action" type="hidden" value="disable_email_otp">
                <label>
                    <span>Fjalkalimi aktual</span>
                    <input autocomplete="current-password" name="current_password" type="password">
                </label>
                <button class="button button-secondary" type="submit">Caktivizo verifikimin me email</button>
            </form>
        <?php else: ?>
            <form action="/settings/two-factor" class="form-grid mt-24" method="post">
                <?= csrf_field(); ?>
                <input name="action" type="hidden" value="enable_email_otp">
                <label>
                    <span>Fjalkalimi aktual</span>
                    <input autocomplete="current-password" name="current_password" type="password">
                </label>
                <button class="button button-primary" type="submit">Aktivizo verifikimin me email</button>
            </form>
        <?php endif; ?>
    </article>

    <article class="panel-card">
        <p class="eyebrow">Aplikacioni</p>
        <h2>Konfigurimi aktiv</h2>
        <div class="stack-cards mt-24">
            <?php foreach ($environment as $key => $value): ?>
                <div class="line-item">
                    <span><?= e($key); ?></span>
                    <strong><?= e((string) $value); ?></strong>
                </div>
            <?php endforeach; ?>
        </div>
    </article>
</section>

<section class="grid-2 mt-24">
    <article class="panel-card">
        <p class="eyebrow">Clinic settings</p>
        <h2>Snapshot databaze</h2>
        <div class="stack-cards mt-24">
            <?php foreach ($settings as $key => $value): ?>
                <div class="line-item">
                    <span><?= e($key); ?></span>
                    <strong><?= e((string) $value); ?></strong>
                </div>
            <?php endforeach; ?>
        </div>
    </article>
</section>
