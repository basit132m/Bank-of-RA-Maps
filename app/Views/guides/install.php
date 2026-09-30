<?php

$structuredData = [
    '@context' => 'https://schema.org',
    '@type'    => 'HowTo',
    'name'     => "How to install Yuri's Revenge maps",
    'step'     => [
        ['@type' => 'HowToStep', 'name' => 'Download the map file'],
        ['@type' => 'HowToStep', 'name' => 'Unzip it if needed'],
        ['@type' => 'HowToStep', 'name' => 'Find your Yuri\'s Revenge folder'],
        ['@type' => 'HowToStep', 'name' => 'Copy the map in'],
        ['@type' => 'HowToStep', 'name' => 'Pick the map in game'],
    ],
];
?>
<script type="application/ld+json">
<?= json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) ?>
</script>

<div class="page-head">
  <div class="shell">
    <p class="page-head__eyebrow"><a href="/guides">Guides</a></p>
    <h1>How to install Yuri's Revenge maps</h1>
    <p class="page-head__lead">
      About thirty seconds, once you know where the folder is. The path is different
      for CnCNet, Steam, Origin and the original CD release — all four are below.
    </p>
  </div>
</div>

<div class="shell prose prose--page">
  <h2>The short version</h2>
  <ol class="steps">
    <li><strong>Download</strong> the map from its page on this site.</li>
    <li><strong>Unzip</strong> it if it arrived as a <code>.zip</code>. You want the
        <code>.map</code>, <code>.mpr</code> or <code>.yrm</code> file inside.</li>
    <li><strong>Copy</strong> that file into your Yuri's Revenge game folder (see below).</li>
    <li><strong>Launch</strong> the game and pick the map from the skirmish or
        multiplayer map list.</li>
  </ol>

  <h2>Where the folder is</h2>

  <h3>CnCNet (the usual way to play online)</h3>
  <p>
    Put the file in the root of your CnCNet Yuri's Revenge folder — the same folder that
    contains <code>gamemd.exe</code> and <code>cncnet5.dll</code>. CnCNet reads maps from
    there directly, and also from a <code>Maps\Custom</code> subfolder if one exists.
  </p>
  <pre><code>...\CnCNet\Yuri's Revenge\</code></pre>
  <p class="callout">
    <strong>Playing online?</strong> Everyone in the lobby needs the same map file.
    CnCNet transfers custom maps to other players automatically in most cases, but if
    someone cannot see the map, have them download it from this site too.
  </p>

  <h3>Steam</h3>
  <pre><code>C:\Program Files (x86)\Steam\steamapps\common\Command &amp; Conquer Red Alert II\</code></pre>
  <p>
    If you own the Ultimate Collection or the Remastered bundle, the Yuri's Revenge files
    sit in a subfolder of that directory. Drop the map next to <code>gamemd.exe</code>.
  </p>

  <h3>Origin / EA App</h3>
  <pre><code>C:\Program Files (x86)\Origin Games\Command and Conquer Red Alert II\</code></pre>

  <h3>Original CD install</h3>
  <pre><code>C:\Westwood\RA2\</code></pre>
  <p>
    On the original release the game folder is wherever you installed it — commonly
    <code>C:\Westwood\RA2</code>. The map goes in the root of that folder.
  </p>

  <h2>Which file extension do I need?</h2>
  <table class="data-table">
    <thead>
      <tr><th>Extension</th><th>What it is</th></tr>
    </thead>
    <tbody>
      <tr><td><code>.map</code></td><td>A multiplayer map. The most common format.</td></tr>
      <tr><td><code>.mpr</code></td><td>Multiplayer map, the original Red Alert 2 format. Works in YR.</td></tr>
      <tr><td><code>.yrm</code></td><td>A Yuri's Revenge map. Will not load in vanilla RA2.</td></tr>
      <tr><td><code>.zip</code></td><td>An archive. Unzip it first and use the file inside.</td></tr>
    </tbody>
  </table>

  <h2>The map is not showing up</h2>
  <ul>
    <li><strong>Still zipped.</strong> The game cannot read a <code>.zip</code>. Extract it.</li>
    <li><strong>Wrong folder.</strong> The file must sit beside <code>gamemd.exe</code>, or
        in <code>Maps\Custom</code>. A random Documents folder will not work.</li>
    <li><strong>Wrong game.</strong> A <code>.yrm</code> map only appears in Yuri's Revenge,
        not in plain Red Alert 2.</li>
    <li><strong>Hidden extension.</strong> If Windows hides known extensions you may have
        saved <code>mymap.map.txt</code>. Turn extensions on in File Explorer and check.</li>
    <li><strong>Game was already running.</strong> Restart it — the map list is read at launch.</li>
  </ul>

  <h2>Removing a map</h2>
  <p>
    Delete the file from the game folder. Nothing else is written anywhere, so there is
    no uninstall step and no leftovers.
  </p>

  <div class="cta-panel">
    <h2>Ready to play?</h2>
    <p>Pick up a map and go.</p>
    <a class="btn btn--primary" href="/maps">Browse the maps</a>
  </div>
</div>
