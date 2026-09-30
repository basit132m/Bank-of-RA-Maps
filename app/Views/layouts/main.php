<?php

/**
 * @var string      $content
 * @var string|null $title
 * @var string|null $description
 * @var string|null $ogImage
 */

use App\Core\Config;
use App\Core\Request;

$siteName    = (string) Config::get('app.name', 'Bank of YR Maps');
$tagline     = (string) Config::get('app.tagline', '');
$discord     = (string) Config::get('community.discord_invite', '');
$currentPath = (new Request())->path();

$pageTitle = isset($title) && $title !== null && $title !== ''
    ? $title . ' — ' . $siteName
    : $siteName . ' — ' . $tagline;

$metaDescription = $description ?? $tagline;

$nav = [
    '/maps'      => 'Maps',
    '/guides'    => 'Guides',
    '/community' => 'Community',
    '/about'     => 'About',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e($metaDescription) ?>">
<link rel="canonical" href="<?= e(url($currentPath)) ?>">

<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e($siteName) ?>">
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e($metaDescription) ?>">
<meta property="og:url" content="<?= e(url($currentPath)) ?>">
<?php if (! empty($ogImage)): ?>
<meta property="og:image" content="<?= e(url($ogImage)) ?>">
<meta name="twitter:card" content="summary_large_image">
<?php else: ?>
<meta name="twitter:card" content="summary">
<?php endif; ?>

<link rel="icon" href="<?= e(asset('img/favicon.svg')) ?>" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@600;700&display=swap">
<link rel="stylesheet" href="<?= e(asset('css/site.css')) ?>">
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>

<header class="site-header">
  <div class="shell site-header__inner">
    <a class="brand" href="/">
      <span class="brand__mark" aria-hidden="true">
        <svg viewBox="0 0 32 32" width="30" height="30" fill="none" aria-hidden="true">
          <path d="M16 2 3 8v9c0 6.2 5.3 11.6 13 13 7.7-1.4 13-6.8 13-13V8L16 2Z"
                stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
          <path d="M10 15h12M16 10v11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </span>
      <span class="brand__text">
        <strong>Bank of YR Maps</strong>
        <small>Yuri's Revenge map archive</small>
      </span>
    </a>

    <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav">
      <span class="sr-only">Menu</span>
      <span class="nav-toggle__bars" aria-hidden="true"></span>
    </button>

    <nav class="site-nav" id="site-nav" aria-label="Main">
      <ul>
        <?php foreach ($nav as $href => $label): ?>
          <li>
            <a href="<?= e($href) ?>"
               <?= str_starts_with($currentPath, $href) ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
          </li>
        <?php endforeach; ?>
      </ul>
      <?php if ($discord !== ''): ?>
        <a class="btn btn--discord" href="<?= e($discord) ?>" rel="noopener noreferrer" target="_blank">
          Join Discord
        </a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main id="main"><?= $content ?></main>

<footer class="site-footer">
  <div class="shell site-footer__inner">
    <div class="site-footer__about">
      <strong>Bank of YR Maps</strong>
      <p>Original multiplayer maps for Command &amp; Conquer: Red Alert 2 — Yuri's Revenge,
         designed and published by hand. Free to download, forever.</p>
    </div>

    <nav class="site-footer__links" aria-label="Footer">
      <div>
        <h2>Maps</h2>
        <ul>
          <li><a href="/maps">Browse all</a></li>
          <li><a href="<?= e(query_url('/maps', ['players' => 2])) ?>">1v1 maps</a></li>
          <li><a href="<?= e(query_url('/maps', ['sort' => 'popular'])) ?>">Most downloaded</a></li>
        </ul>
      </div>
      <div>
        <h2>Help</h2>
        <ul>
          <li><a href="/guides/install">Installing maps</a></li>
          <li><a href="/guides">All guides</a></li>
        </ul>
      </div>
      <div>
        <h2>Site</h2>
        <ul>
          <li><a href="/about">About</a></li>
          <li><a href="/community">Community</a></li>
        </ul>
      </div>
    </nav>
  </div>

  <div class="shell site-footer__legal">
    <p>&copy; <?= date('Y') ?> Bank of YR Maps. Maps are the work of their credited designers.</p>
    <p>Command &amp; Conquer and Red Alert are trademarks of Electronic Arts Inc.
       This is an unofficial fan site with no affiliation to EA.</p>
  </div>
</footer>

<script src="<?= e(asset('js/site.js')) ?>" defer></script>
</body>
</html>
