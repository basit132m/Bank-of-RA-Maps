<?php /** @var int $mapCount */ ?>
<div class="page-head">
  <div class="shell">
    <h1>About Bank of YR Maps</h1>
    <p class="page-head__lead">
      A small, deliberately hand-made archive of multiplayer maps for
      Command &amp; Conquer: Red Alert 2 — Yuri's Revenge.
    </p>
  </div>
</div>

<div class="shell prose prose--page">
  <h2>What this site is</h2>
  <p>
    Every map published here is designed from scratch, played before it ships, and
    written up with real notes on how it behaves: where the pressure points are, which
    starting position has the harder opening, whether it suits a quick game or a long one.
  </p>
  <p>
    That write-up matters as much as the file. Plenty of places will hand you a map
    download. Very few will tell you whether it is any good, or why.
  </p>

  <h2>Why Yuri's Revenge, in <?= e(date('Y')) ?></h2>
  <p>
    Because people are still playing it. CnCNet keeps the lobbies alive, the balance
    still rewards skill, and a good map still decides a good game. The community never
    left — it just got quieter and more scattered. This site is one small attempt to
    gather a corner of it back together.
  </p>

  <h2>Everything here is free</h2>
  <p>
    No accounts required to download, no paywalls, no download timers, no bundled
    installers. Take the maps, play them, share them.
  </p>

  <h2>Using these maps</h2>
  <p>
    Play them, host them, run tournaments on them. If you republish a map somewhere
    else, credit the designer and link back — that is the only ask. Do not repackage
    them into a "map pack" with the authorship stripped off.
  </p>

  <h2>Get in touch</h2>
  <p>
    Found a balance problem, a broken starting position, or a typo in a map's notes?
    That is genuinely useful feedback. The
    <a href="/community">community page</a> has the best ways to reach us.
  </p>

  <div class="cta-panel">
    <h2><?= $mapCount > 0 ? e(format_count($mapCount)) . ' maps and counting' : 'The first maps are on the way' ?></h2>
    <p>Have a look at what is published so far.</p>
    <a class="btn btn--primary" href="/maps">Browse the maps</a>
  </div>
</div>
