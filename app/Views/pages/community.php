<?php
/**
 * @var string $discordInvite
 * @var string $contactEmail
 */
?>
<div class="page-head">
  <div class="shell">
    <h1>Community</h1>
    <p class="page-head__lead">
      Maps are better with opponents. This is where Red Alert 2 and Yuri's Revenge
      players gather, organise games, and pick apart each other's builds.
    </p>
  </div>
</div>

<div class="shell">
  <div class="card-grid card-grid--two">
    <article class="link-card link-card--feature">
      <h2>Discord</h2>
      <p>
        The main hangout. Game organising, map feedback, balance arguments, and the
        first place new releases get announced.
      </p>
      <?php if ($discordInvite !== ''): ?>
        <a class="btn btn--discord" href="<?= e($discordInvite) ?>" rel="noopener noreferrer" target="_blank">
          Join the Discord
        </a>
      <?php else: ?>
        <p class="link-card__meta">Invite link coming shortly — the server is being set up.</p>
      <?php endif; ?>
    </article>

    <article class="link-card">
      <h2>Map feedback</h2>
      <p>
        Played one of these maps and found a problem — a starting position that is
        clearly stronger, ore that runs dry too fast, a chokepoint that makes the game
        a stalemate? Say so. Maps here get revised, and the version history on each
        map page shows it.
      </p>
      <?php if ($contactEmail !== ''): ?>
        <p class="link-card__meta">
          Or email <a href="mailto:<?= e($contactEmail) ?>"><?= e($contactEmail) ?></a>.
        </p>
      <?php endif; ?>
    </article>
  </div>

  <div class="prose prose--page">
    <h2>House rules</h2>
    <p>Short list, and it is not complicated.</p>
    <ul>
      <li><strong>Argue about maps, not about people.</strong> Strong opinions on balance
          are the point. Personal attacks are not.</li>
      <li><strong>Credit designers.</strong> If you share someone's map, say whose it is.</li>
      <li><strong>No cheats, no hacks, no piracy links.</strong> Keep the place clean.</li>
      <li><strong>English in the main channels</strong>, so everyone can follow — any
          language you like elsewhere.</li>
    </ul>

    <h2>Where else the RA2 community lives</h2>
    <p>
      This site is one corner of a much larger scene. If you are getting back into the
      game, the CnCNet client is how most people play online these days, and there are
      long-running communities around mods like Mental Omega. We are not trying to
      replace any of that — just add good maps to it.
    </p>

    <div class="cta-panel">
      <h2>New here?</h2>
      <p>Grab a map, install it, and come tell us how the game went.</p>
      <a class="btn btn--primary" href="/maps">Browse the maps</a>
      <a class="btn btn--ghost" href="/guides/install">Install guide</a>
    </div>
  </div>
</div>
