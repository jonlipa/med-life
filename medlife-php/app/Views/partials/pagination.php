<?php $queryParams = $_GET; unset($queryParams['page']); ?>
<nav class="pagination-nav" style="display:flex;align-items:center;gap:12px;justify-content:center;margin-top:24px;">
    <?php if ($paginator->hasPrev()): ?>
        <a class="button button-secondary" href="?<?= http_build_query(array_merge($queryParams, ['page' => $paginator->currentPage() - 1])); ?>">&laquo; Paraprake</a>
    <?php else: ?>
        <span class="button button-secondary" style="opacity:0.5;pointer-events:none;">&laquo; Paraprake</span>
    <?php endif; ?>

    <span style="color:var(--muted, #888);">Faqja <?= e((string) $paginator->currentPage()); ?> nga <?= e((string) $paginator->totalPages()); ?></span>

    <?php if ($paginator->hasNext()): ?>
        <a class="button button-secondary" href="?<?= http_build_query(array_merge($queryParams, ['page' => $paginator->currentPage() + 1])); ?>">Tjetra &raquo;</a>
    <?php else: ?>
        <span class="button button-secondary" style="opacity:0.5;pointer-events:none;">Tjetra &raquo;</span>
    <?php endif; ?>
</nav>
