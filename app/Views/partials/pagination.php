<?php

/**
 * @var array{page: int, pages: int} $results
 * @var array<string, string|int|null> $filters
 * @var string $basePath
 */

if ($results['pages'] <= 1) {
    return;
}

$page  = $results['page'];
$pages = $results['pages'];

// Show a compact window around the current page.
$from = max(1, $page - 2);
$to   = min($pages, $page + 2);
?>
<nav class="pagination" aria-label="Pagination">
  <?php if ($page > 1): ?>
    <a class="pagination__step" href="<?= e(query_url($basePath, $filters + ['page' => $page - 1 > 1 ? $page - 1 : null])) ?>"
       rel="prev">Previous</a>
  <?php else: ?>
    <span class="pagination__step is-disabled">Previous</span>
  <?php endif; ?>

  <ol class="pagination__pages">
    <?php if ($from > 1): ?>
      <li><a href="<?= e(query_url($basePath, $filters)) ?>">1</a></li>
      <?php if ($from > 2): ?><li aria-hidden="true" class="pagination__gap">…</li><?php endif; ?>
    <?php endif; ?>

    <?php for ($i = $from; $i <= $to; $i++): ?>
      <li>
        <?php if ($i === $page): ?>
          <span aria-current="page"><?= $i ?></span>
        <?php else: ?>
          <a href="<?= e(query_url($basePath, $filters + ['page' => $i > 1 ? $i : null])) ?>"><?= $i ?></a>
        <?php endif; ?>
      </li>
    <?php endfor; ?>

    <?php if ($to < $pages): ?>
      <?php if ($to < $pages - 1): ?><li aria-hidden="true" class="pagination__gap">…</li><?php endif; ?>
      <li><a href="<?= e(query_url($basePath, $filters + ['page' => $pages])) ?>"><?= $pages ?></a></li>
    <?php endif; ?>
  </ol>

  <?php if ($page < $pages): ?>
    <a class="pagination__step" href="<?= e(query_url($basePath, $filters + ['page' => $page + 1])) ?>"
       rel="next">Next</a>
  <?php else: ?>
    <span class="pagination__step is-disabled">Next</span>
  <?php endif; ?>
</nav>
