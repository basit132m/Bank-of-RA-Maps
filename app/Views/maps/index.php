<?php

/**
 * @var string $heading
 * @var array<string, string|int|null> $filters
 * @var array{items: array<int, array<string, mixed>>, total: int, page: int, pages: int} $results
 * @var array<int, int> $playerCounts
 * @var array<int, array<string, mixed>> $modes
 * @var array<int, array<string, mixed>> $tags
 */

use App\Core\View;
use App\Models\Map;

$hasFilters = ($filters['q'] ?? '') !== ''
    || (int) ($filters['players'] ?? 0) > 0
    || ($filters['theater'] ?? '') !== ''
    || ($filters['game'] ?? '') !== ''
    || ($filters['mode'] ?? '') !== ''
    || ($filters['tag'] ?? '') !== '';
?>
<div class="page-head">
  <div class="shell">
    <h1><?= e($heading) ?></h1>
    <p class="page-head__count">
      <?= e(format_count($results['total'])) ?>
      <?= $results['total'] === 1 ? 'map' : 'maps' ?><?= $hasFilters ? ' match your filters' : ' in the catalogue' ?>
    </p>
  </div>
</div>

<div class="shell browse">
  <form class="filters" method="get" action="/maps">
    <div class="filters__row">
      <label class="filters__field filters__field--search">
        <span>Search</span>
        <input type="search" name="q" value="<?= e($filters['q'] ?? '') ?>"
               placeholder="Map name…" autocomplete="off">
      </label>

      <label class="filters__field">
        <span>Players</span>
        <select name="players">
          <option value="">Any</option>
          <?php foreach ($playerCounts as $count): ?>
            <option value="<?= e($count) ?>" <?= (int) ($filters['players'] ?? 0) === $count ? 'selected' : '' ?>>
              <?= e($count) ?> players
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <label class="filters__field">
        <span>Theater</span>
        <select name="theater">
          <option value="">Any</option>
          <?php foreach (Map::THEATERS as $value => $label): ?>
            <option value="<?= e($value) ?>" <?= ($filters['theater'] ?? '') === $value ? 'selected' : '' ?>>
              <?= e($label) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <label class="filters__field">
        <span>Game</span>
        <select name="game">
          <option value="">Any</option>
          <?php foreach (Map::GAMES as $value => $label): ?>
            <?php if ($value === 'both') { continue; } ?>
            <option value="<?= e($value) ?>" <?= ($filters['game'] ?? '') === $value ? 'selected' : '' ?>>
              <?= e($label) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <?php if ($modes !== []): ?>
        <label class="filters__field">
          <span>Mode</span>
          <select name="mode">
            <option value="">Any</option>
            <?php foreach ($modes as $mode): ?>
              <option value="<?= e($mode['slug']) ?>" <?= ($filters['mode'] ?? '') === $mode['slug'] ? 'selected' : '' ?>>
                <?= e($mode['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </label>
      <?php endif; ?>

      <label class="filters__field">
        <span>Sort</span>
        <select name="sort">
          <?php foreach (Map::SORTS as $value => $label): ?>
            <option value="<?= e($value) ?>" <?= ($filters['sort'] ?? 'newest') === $value ? 'selected' : '' ?>>
              <?= e($label) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <?php if (! empty($filters['tag'])): ?>
        <input type="hidden" name="tag" value="<?= e($filters['tag']) ?>">
      <?php endif; ?>

      <div class="filters__actions">
        <button class="btn btn--primary" type="submit">Apply</button>
        <?php if ($hasFilters): ?>
          <a class="btn btn--quiet" href="/maps">Clear</a>
        <?php endif; ?>
      </div>
    </div>
  </form>

  <?php if ($tags !== []): ?>
    <nav class="tag-cloud" aria-label="Popular tags">
      <span class="tag-cloud__label">Popular:</span>
      <?php foreach (array_slice($tags, 0, 10) as $tag): ?>
        <a class="tag<?= ($filters['tag'] ?? '') === $tag['slug'] ? ' is-active' : '' ?>"
           href="/maps/tag/<?= e($tag['slug']) ?>"><?= e($tag['name']) ?></a>
      <?php endforeach; ?>
    </nav>
  <?php endif; ?>

  <?php if ($results['items'] === []): ?>
    <div class="empty">
      <h2>No maps found</h2>
      <p>Nothing matches that combination yet. Try widening the filters, or
         <a href="/maps">browse everything</a>.</p>
    </div>
  <?php else: ?>
    <div class="map-grid">
      <?php foreach ($results['items'] as $map): ?>
        <?= View::partial('partials/map-card', ['map' => $map]) ?>
      <?php endforeach; ?>
    </div>

    <?= View::partial('partials/pagination', [
        'results'  => $results,
        'filters'  => $filters,
        'basePath' => '/maps',
    ]) ?>
  <?php endif; ?>
</div>
