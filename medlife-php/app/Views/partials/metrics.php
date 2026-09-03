<?php if (!empty($metrics ?? [])): ?>
    <section class="metrics-grid">
        <?php foreach ($metrics as $index => $metric): ?>
            <article class="metric-card metric-card-<?= e((string) ($index + 1)); ?>">
                <p><?= e($metric['label']); ?></p>
                <strong><?= e((string) $metric['value']); ?></strong>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>
