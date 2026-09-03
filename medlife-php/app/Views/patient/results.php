<?php $pageTitle = 'Rezultatet'; ?>

<section class="grid-2">
    <article class="panel-card">
        <p class="eyebrow">Laboratori</p>
        <h2>Rezultatet e publikuara</h2>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Testi</th><th>Doktori</th><th>Statusi</th><th>Rezultati</th></tr></thead>
                <tbody>
                    <?php foreach ($results as $result): ?>
                        <tr>
                            <td><?= e($result['name']); ?></td>
                            <td><?= e($result['doctor_name']); ?></td>
                            <td><span class="<?= e(status_class($result['status'])); ?>"><?= e($result['status']); ?></span></td>
                            <td><?= e($result['result_summary']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
    <article class="panel-card">
        <p class="eyebrow">Recetat</p>
        <h2>Medikamentet aktive</h2>
        <div class="stack-cards">
            <?php foreach ($prescriptions as $prescription): ?>
                <div class="line-item line-item-start">
                    <div>
                        <strong><?= e($prescription['medication_name']); ?></strong>
                        <p><?= e($prescription['instructions']); ?></p>
                    </div>
                    <span><?= e($prescription['doctor_name']); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </article>
</section>
