<?php

/**
 * @var array<string, mixed> $map
 * @var array<int, array<string, mixed>> $files
 * @var array<int, array<string, mixed>> $images
 * @var array<int, array<string, mixed>> $versions
 * @var array<int, array<string, mixed>> $tags
 * @var array<int, array<string, mixed>> $modes
 * @var array<int, array<string, mixed>> $related
 */

use App\Core\View;
use App\Models\Map;

$size        = Map::sizeLabel($map);
$primaryFile = $files[0] ?? null;
$totalBytes  = array_sum(array_map(static fn (array $f): int => (int) $f['bytes'], $files));
$designer    = $map['author_name'] ?? null;

$structuredData = [
    '@context'      => 'https://schema.org',
    '@type'         => 'CreativeWork',
    'name'          => $map['title'],
    'description'   => $map['summary'] ?? '',
    'url'           => url('/maps/' . $map['slug']),
    'genre'         => 'Video game map',
    'about'         => "Command & Conquer: Red Alert 2 — Yuri's Revenge",
    'version'       => $map['version'],
    'isAccessibleForFree' => true,
];

if ($map['published_at'] !== null) {
    $structuredData['datePublished'] = date('Y-m-d', (int) strtotime((string) $map['published_at']));
}

if ($designer !== null && $designer !== '') {
    $structuredData['author'] = ['@type' => 'Person', 'name' => $designer];
}

if (! empty($map['preview_image'])) {
    $structuredData['image'] = url(preview_url((string) $map['preview_image']));
}
?>
<script type="application/ld+json">
<?= json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) ?>
</script>

<nav class="breadcrumb" aria-label="Breadcrumb">
  <div class="shell">
    <ol>
      <li><a href="/">Home</a></li>
      <li><a href="/maps">Maps</a></li>
      <li><span aria-current="page"><?= e($map['title']) ?></span></li>
    </ol>
  </div>
</nav>

<article class="map-detail">
  <header class="map-detail__head">
    <div class="shell">
      <h1><?= e($map['title']) ?></h1>

      <ul class="pill-list">
        <li class="pill pill--accent"><?= e($map['players']) ?> players</li>
        <li class="pill"><?= e(Map::theaterLabel($map)) ?></li>
        <?php if ($size !== null): ?><li class="pill"><?= e($size) ?></li><?php endif; ?>
        <li class="pill"><?= e(Map::gameLabel($map)) ?></li>
        <?php foreach ($modes as $mode): ?>
          <li class="pill"><?= e($mode['name']) ?></li>
        <?php endforeach; ?>
      </ul>

      <p class="map-detail__byline">
        Version <?= e($map['version']) ?>
        <?php if ($designer !== null && $designer !== ''): ?>
          &middot; by <?= e($designer) ?>
        <?php endif; ?>
        <?php if (! empty($map['published_at'])): ?>
          &middot; published <?= e(format_date((string) $map['published_at'])) ?>
        <?php endif; ?>
      </p>
    </div>
  </header>

  <div class="shell map-detail__layout">
    <div class="map-detail__main">
      <figure class="map-detail__preview">
        <img src="<?= e(preview_url($map['preview_image'] ?? null)) ?>"
             alt="Minimap preview of <?= e($map['title']) ?>"
             width="900" height="900" decoding="async">
        <figcaption>Minimap preview</figcaption>
      </figure>

      <?php if ($images !== []): ?>
        <section class="map-detail__section">
          <h2>Screenshots</h2>
          <div class="gallery">
            <?php foreach ($images as $image): ?>
              <a class="gallery__item" href="<?= e(preview_url((string) $image['path'])) ?>" target="_blank" rel="noopener">
                <img src="<?= e(preview_url((string) $image['path'])) ?>"
                     alt="<?= e($image['alt'] ?? $map['title'] . ' screenshot') ?>"
                     width="400" height="300" loading="lazy" decoding="async">
              </a>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>

      <?php if (! empty($map['designer_notes'])): ?>
        <section class="map-detail__section prose">
          <h2>Designer's notes</h2>
          <?= paragraphs((string) $map['designer_notes']) ?>
        </section>
      <?php endif; ?>

      <section class="map-detail__section prose">
        <h2>Installing this map</h2>
        <?php if (! empty($map['install_notes'])): ?>
          <?= paragraphs((string) $map['install_notes']) ?>
        <?php else: ?>
          <p>
            Drop the downloaded file into your Yuri's Revenge maps folder, then pick
            the map from the skirmish or multiplayer map list. The exact folder depends
            on how you installed the game.
          </p>
        <?php endif; ?>
        <p><a class="link-arrow" href="/guides/install">Full install guide for every version of the game</a></p>
      </section>

      <?php if ($versions !== []): ?>
        <section class="map-detail__section">
          <h2>Version history</h2>
          <ol class="changelog">
            <?php foreach ($versions as $version): ?>
              <li>
                <h3>
                  Version <?= e($version['version']) ?>
                  <?php if (! empty($version['released_at'])): ?>
                    <span class="changelog__date"><?= e(format_date((string) $version['released_at'])) ?></span>
                  <?php endif; ?>
                </h3>
                <?php if (! empty($version['notes'])): ?>
                  <div class="prose"><?= paragraphs((string) $version['notes']) ?></div>
                <?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ol>
        </section>
      <?php endif; ?>

      <?php if ($tags !== []): ?>
        <section class="map-detail__section">
          <h2>Tags</h2>
          <nav class="tag-cloud" aria-label="Map tags">
            <?php foreach ($tags as $tag): ?>
              <a class="tag" href="/maps/tag/<?= e($tag['slug']) ?>"><?= e($tag['name']) ?></a>
            <?php endforeach; ?>
          </nav>
        </section>
      <?php endif; ?>
    </div>

    <aside class="map-detail__aside">
      <div class="download-card">
        <?php if ($primaryFile !== null): ?>
          <a class="btn btn--primary btn--block" href="/download/<?= e($map['slug']) ?>">
            Download map
          </a>
          <p class="download-card__meta">
            <?= e(basename((string) $primaryFile['original_name'])) ?>
            &middot; <?= e(format_bytes($totalBytes)) ?>
          </p>

          <?php if (count($files) > 1): ?>
            <ul class="download-card__files">
              <?php foreach ($files as $file): ?>
                <li>
                  <a href="/download/<?= e($map['slug']) ?>/<?= e($file['id']) ?>">
                    <?= e(basename((string) $file['original_name'])) ?>
                  </a>
                  <span><?= e(format_bytes((int) $file['bytes'])) ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        <?php else: ?>
          <p class="download-card__meta">This map has no file attached yet.</p>
        <?php endif; ?>

        <p class="download-card__count"><?= e(format_count($map['download_count'])) ?> downloads</p>
      </div>

      <div class="spec-card">
        <h2>Specifications</h2>
        <dl class="spec-list">
          <div><dt>Players</dt><dd><?= e($map['players']) ?></dd></div>
          <?php if ($size !== null): ?>
            <div><dt>Map size</dt><dd><?= e($size) ?></dd></div>
          <?php endif; ?>
          <div><dt>Theater</dt><dd><?= e(Map::theaterLabel($map)) ?></dd></div>
          <div><dt>Game</dt><dd><?= e(Map::gameLabel($map)) ?></dd></div>
          <?php if ($modes !== []): ?>
            <div>
              <dt>Modes</dt>
              <dd><?= e(implode(', ', array_column($modes, 'name'))) ?></dd>
            </div>
          <?php endif; ?>
          <div><dt>Tech structures</dt><dd><?= e($map['tech_structures']) ?></dd></div>
          <div><dt>Oil derricks</dt><dd><?= e($map['oil_derricks']) ?></dd></div>
          <?php if (! empty($map['ore_density'])): ?>
            <div>
              <dt>Ore density</dt>
              <dd><?= e(Map::ORE_DENSITIES[$map['ore_density']] ?? $map['ore_density']) ?></dd>
            </div>
          <?php endif; ?>
          <div><dt>Gem patches</dt><dd><?= e($map['gem_count']) ?></dd></div>
          <div><dt>CnCNet ready</dt><dd><?= $map['cncnet_ready'] === 1 ? 'Yes' : 'No' ?></dd></div>
          <div><dt>Version</dt><dd><?= e($map['version']) ?></dd></div>
        </dl>
      </div>
    </aside>
  </div>

  <?php if ($related !== []): ?>
    <section class="section">
      <div class="shell">
        <div class="section__head">
          <h2>More maps</h2>
          <a class="section__more" href="/maps">Browse all</a>
        </div>
        <div class="map-grid map-grid--compact">
          <?php foreach ($related as $other): ?>
            <?= View::partial('partials/map-card', ['map' => $other]) ?>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>
</article>
