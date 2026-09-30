<?php

/**
 * @var array<string, mixed>|null $featured
 * @var array<int, array<string, mixed>> $latest
 * @var int $mapCount
 * @var int $downloads
 */

use App\Core\Config;
use App\Core\View;
use App\Models\Map;

$discord = (string) Config::get('community.discord_invite', '');
?>
<section class="hero">
  <div class="shell hero__inner">
    <p class="hero__eyebrow">Red Alert 2 &middot; Yuri's Revenge</p>
    <h1>Maps built to be played, not just downloaded.</h1>
    <p class="hero__lead">
      Every map here is designed by hand, tested in real games, and published with
      honest notes on how it actually plays. No reskins, no filler.
    </p>

    <div class="hero__actions">
      <a class="btn btn--primary" href="/maps">Browse the maps</a>
      <a class="btn btn--ghost" href="/guides/install">How to install</a>
    </div>

    <?php if ($mapCount > 0): ?>
      <dl class="hero__stats">
        <div><dt>Maps published</dt><dd><?= e(format_count($mapCount)) ?></dd></div>
        <div><dt>Total downloads</dt><dd><?= e(format_count($downloads)) ?></dd></div>
        <div><dt>Price</dt><dd>Free</dd></div>
      </dl>
    <?php endif; ?>
  </div>
</section>

<?php if ($featured !== null): ?>
  <section class="section section--featured">
    <div class="shell">
      <div class="section__head">
        <h2>Featured map</h2>
        <a class="section__more" href="/maps">All maps</a>
      </div>

      <article class="featured">
        <a class="featured__media" href="/maps/<?= e($featured['slug']) ?>" tabindex="-1" aria-hidden="true">
          <img src="<?= e(preview_url($featured['preview_image'] ?? null)) ?>"
               alt="" width="640" height="640" loading="lazy" decoding="async">
        </a>

        <div class="featured__body">
          <h3><a href="/maps/<?= e($featured['slug']) ?>"><?= e($featured['title']) ?></a></h3>

          <ul class="pill-list">
            <li class="pill"><?= e($featured['players']) ?> players</li>
            <li class="pill"><?= e(Map::theaterLabel($featured)) ?></li>
            <?php if (Map::sizeLabel($featured) !== null): ?>
              <li class="pill"><?= e(Map::sizeLabel($featured)) ?></li>
            <?php endif; ?>
          </ul>

          <?php if (! empty($featured['summary'])): ?>
            <p class="featured__summary"><?= e($featured['summary']) ?></p>
          <?php endif; ?>

          <div class="featured__actions">
            <a class="btn btn--primary" href="/maps/<?= e($featured['slug']) ?>">Map details</a>
            <a class="btn btn--ghost" href="/download/<?= e($featured['slug']) ?>">Download</a>
          </div>
        </div>
      </article>
    </div>
  </section>
<?php endif; ?>

<section class="section">
  <div class="shell">
    <div class="section__head">
      <h2><?= $featured !== null ? 'Latest releases' : 'The maps' ?></h2>
      <a class="section__more" href="/maps">Browse all</a>
    </div>

    <?php if ($latest === []): ?>
      <div class="empty">
        <h3>No maps published yet</h3>
        <p>The first batch is on the way. Check back shortly, or join the Discord
           to hear the moment a map goes live.</p>
        <?php if ($discord !== ''): ?>
          <a class="btn btn--primary" href="<?= e($discord) ?>" rel="noopener noreferrer" target="_blank">Join Discord</a>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="map-grid">
        <?php foreach ($latest as $map): ?>
          <?= View::partial('partials/map-card', ['map' => $map]) ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="section section--split">
  <div class="shell split">
    <div class="split__panel">
      <h2>New to custom maps?</h2>
      <p>
        Installing a map takes about thirty seconds once you know where the folder is.
        The path differs between CnCNet, Steam, Origin and the original CD release —
        the guide covers all four.
      </p>
      <a class="btn btn--ghost" href="/guides/install">Read the install guide</a>
    </div>

    <div class="split__panel">
      <h2>Play with other people</h2>
      <p>
        Maps are better with opponents. The community Discord is where games get
        organised, feedback gets given, and new releases get announced first.
      </p>
      <?php if ($discord !== ''): ?>
        <a class="btn btn--discord" href="<?= e($discord) ?>" rel="noopener noreferrer" target="_blank">Join the Discord</a>
      <?php else: ?>
        <a class="btn btn--ghost" href="/community">See the community page</a>
      <?php endif; ?>
    </div>
  </div>
</section>
