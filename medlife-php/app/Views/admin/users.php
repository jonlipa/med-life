<?php $pageTitle = 'Perdoruesit'; ?>

<section class="panel-card">
    <div class="panel-toolbar">
        <div>
            <p class="eyebrow">Filtra</p>
            <h2>Kerko dhe segmentoje perdoruesit</h2>
        </div>
        <form action="/admin/users" class="filter-form" method="get">
            <input name="q" placeholder="Kerko emer, email ose profil" type="text" value="<?= e($filters['q'] ?? ''); ?>">
            <select name="role">
                <option value="">Te gjitha rolet</option>
                <option value="admin" <?= ($filters['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>admin</option>
                <option value="doctor" <?= ($filters['role'] ?? '') === 'doctor' ? 'selected' : ''; ?>>doctor</option>
                <option value="reception" <?= ($filters['role'] ?? '') === 'reception' ? 'selected' : ''; ?>>reception</option>
                <option value="patient" <?= ($filters['role'] ?? '') === 'patient' ? 'selected' : ''; ?>>patient</option>
            </select>
            <button class="button button-secondary" type="submit">Filtro</button>
        </form>
    </div>
    <div class="pill-list mt-24">
        <?php foreach ($roleSummary as $role => $count): ?>
            <span class="pill"><?= e($role); ?>: <?= e((string) $count); ?></span>
        <?php endforeach; ?>
    </div>
</section>

<section class="grid-2 mt-24">
    <article class="panel-card">
        <p class="eyebrow">Krijo perdorues</p>
        <h2>Shto llogari te re</h2>
        <form action="/admin/users" class="form-grid form-grid-2" method="post">
            <?= csrf_field(); ?>
            <label><span>Roli</span>
                <select name="role">
                    <option value="admin">admin</option>
                    <option value="doctor">doctor</option>
                    <option value="reception">reception</option>
                </select>
            </label>
            <label><span>Username</span><input name="username" type="text" value="<?= e(old('username')); ?>"></label>
            <label><span>Emri i plote</span><input name="full_name" type="text" value="<?= e(old('full_name')); ?>"></label>
            <label><span>Email</span><input name="email" type="email" value="<?= e(old('email')); ?>"></label>
            <label><span>Telefoni</span><input name="phone" type="text" value="<?= e(old('phone')); ?>"></label>
            <label><span>Password</span><input name="password" type="password"></label>
            <label><span>Titulli</span><input name="title" type="text" value="<?= e(old('title')); ?>"></label>
            <label><span>Departamenti</span><input name="department" type="text" value="<?= e(old('department')); ?>"></label>
            <label><span>Specializimi</span><input name="specialization" type="text" value="<?= e(old('specialization')); ?>"></label>
            <label><span>Dhoma</span><input name="room" type="text" value="<?= e(old('room')); ?>"></label>
            <label><span>Disponueshmeria</span><input name="availability_text" type="text" value="<?= e(old('availability_text', 'E hene - e premte, 08:00 - 16:00')); ?>"></label>
            <label><span>Vite pervoje</span><input name="experience_years" type="number" value="<?= e(old('experience_years', '5')); ?>"></label>
            <label class="span-2"><span>Bio</span><textarea name="bio" rows="3"><?= e(old('bio')); ?></textarea></label>
            <label class="span-2"><span>Shenime disponibiliteti</span><textarea name="availability_notes" rows="3"><?= e(old('availability_notes')); ?></textarea></label>
            <div class="span-2"><button class="button button-primary" type="submit">Ruaj perdoruesin</button></div>
        </form>
    </article>

    <article class="panel-card">
        <p class="eyebrow">Lista e roleve</p>
        <h2>Perdoruesit ekzistues</h2>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Emri</th><th>Roli</th><th>Email</th><th>Profili</th></tr></thead>
                <tbody>
                    <?php foreach ($users as $entry): ?>
                        <tr>
                            <td><?= e($entry['full_name']); ?></td>
                            <td><span class="<?= e(status_class($entry['role'])); ?>"><?= e($entry['role']); ?></span></td>
                            <td><?= e($entry['email']); ?></td>
                            <td><?= e($entry['specialization'] ?? $entry['medical_record_number'] ?? $entry['title']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>
<?php if (isset($paginator)): ?>
    <?php require base_path('app/Views/partials/pagination.php'); ?>
<?php endif; ?>
