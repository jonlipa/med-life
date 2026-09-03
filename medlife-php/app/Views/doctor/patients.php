<?php $pageTitle = 'Pacientet e Doktorit'; ?>

<section class="panel-card">
    <div class="panel-toolbar">
        <div>
            <p class="eyebrow">Pacientet</p>
            <h2>Lista klinike e caktuar</h2>
        </div>
        <form action="/doctor/patients" class="filter-form" method="get">
            <input name="q" placeholder="Kerko MRN, emer ose numer" type="text" value="<?= e($filters['q'] ?? ''); ?>">
            <button class="button button-secondary" type="submit">Kerko</button>
        </form>
    </div>
    <div class="table-wrap mt-24">
        <table class="data-table">
            <thead><tr><th>Pacienti</th><th>MRN</th><th>Kontakti</th><th>Doktori</th><th>Veprimi</th></tr></thead>
            <tbody>
                <?php foreach ($patients as $patient): ?>
                    <tr>
                        <td><?= e($patient['full_name'] ?? $patient['medical_record_number']); ?></td>
                        <td><?= e($patient['medical_record_number']); ?></td>
                        <td><?= e($patient['phone']); ?></td>
                        <td><?= e($patient['doctor_name'] ?? 'Pa doktor'); ?></td>
                        <td><a class="button button-secondary" href="/doctor/records?patient=<?= e((string) $patient['id']); ?>">Hap kartelen</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if (isset($paginator)): ?>
        <?php require base_path('app/Views/partials/pagination.php'); ?>
    <?php endif; ?>
</section>
