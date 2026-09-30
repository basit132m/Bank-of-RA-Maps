<?php

/** @var array<string, mixed> $map */

use App\Models\Map;

$size = Map::sizeLabel($map);
?>
<article class="map-card">
  <a class="map-card__media" href="/maps/<?= e($map['slug']) ?>" tabindex="-1" aria-hidden="true">
    <img src="<?= e(preview_url($map['preview_image'] ?? null)) ?>"
         alt="" width="480" height="480" loading="lazy" decoding="async">
  </a>

  <div class="map-card__body">
    <h3 class="map-card__title">
      <a href="/maps/<?= e($map['slug']) ?>"><?= e($map['title']) ?></a>
    </h3>

    <ul class="map-card__meta">
      <li><?= e($map['players']) ?> players</li>
      <li><?= e(Map::theaterLabel($map)) ?></li>
      <?php if ($size !== null): ?><li><?= e($size) ?></li><?php endif; ?>
    </ul>

    <?php if (! empty($map['summary'])): ?>
      <p class="map-card__summary"><?= e(excerpt((string) $map['summary'], 110)) ?></p>
    <?php endif; ?>

    <footer class="map-card__footer">
      <span class="map-card__downloads"><?= e(format_count($map['download_count'])) ?> downloads</span>
      <a class="btn btn--small" href="/maps/<?= e($map['slug']) ?>">View map</a>
    </footer>
  </div>
</article>
