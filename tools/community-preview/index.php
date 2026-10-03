<?php

/**
 * Local preview for the community page and its board.
 *
 *   php -S 127.0.0.1:8010 -t .
 *
 *   /tools/community-preview/              a board with threads on it
 *   /tools/community-preview/?empty=1      nothing posted yet
 *   /tools/community-preview/?closed=1     board closed to new messages
 *   /tools/community-preview/?nodiscord=1  no Discord invite configured
 *   /tools/community-preview/?noavatar=1   avatars off, monogram fallback
 *   /tools/community-preview/?extra=1      with content typed into the editor
 *   /tools/community-preview/?admin=1      signed in as an administrator
 *
 * The point of this file is that the template, the stylesheet and the script it
 * renders are the real ones — only WordPress is faked. Development tool; it is
 * never deployed.
 */

declare(strict_types=1);

define('ABSPATH', dirname(__DIR__, 2) . '/');
const THEME_URI = '/wp-content/themes/astra-child';

define('HOUR_IN_SECONDS', 3600);
define('MINUTE_IN_SECONDS', 60);
define('DAY_IN_SECONDS', 86400);
define('ARRAY_A', 'ARRAY_A');

$EMPTY   = isset($_GET['empty']);
$CLOSED  = isset($_GET['closed']);
$ADMIN   = isset($_GET['admin']);
$NOAV    = isset($_GET['noavatar']);
$DISCORD = isset($_GET['nodiscord']) ? '' : 'https://discord.gg/example';

/* ---------------------------------------------------------------- escaping */

function esc_url(string $u): string { return htmlspecialchars($u, ENT_QUOTES, 'UTF-8'); }
function esc_attr($t): string { return htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8'); }
function esc_html($t): string { return htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8'); }
function esc_html_e(string $t, string $d = ''): void { echo esc_html($t); }
function esc_attr_e(string $t, string $d = ''): void { echo esc_attr($t); }
function esc_html__(string $t, string $d = ''): string { return esc_html($t); }
function esc_attr__(string $t, string $d = ''): string { return esc_attr($t); }
function __(string $t, string $d = ''): string { return $t; }
function _n(string $s, string $p, int $n, string $d = ''): string { return $n === 1 ? $s : $p; }
function wp_strip_all_tags($t, bool $b = false): string { return trim(strip_tags((string) $t)); }
function antispambot(string $e, int $h = 0): string { return $e; }
function number_format_i18n($n): string { return number_format((int) $n); }

function wp_trim_words(string $text, int $n = 55, ?string $more = null): string
{
    $words = preg_split('/\s+/', trim($text)) ?: [];

    if (count($words) <= $n) {
        return implode(' ', $words);
    }

    return implode(' ', array_slice($words, 0, $n)) . ($more ?? '…');
}

function human_time_diff(int $from, int $to = 0): string
{
    $to   = $to ?: time();
    $diff = abs($to - $from);

    if ($diff < 3600)  { return max(1, (int) round($diff / 60)) . ' mins'; }
    if ($diff < 86400) { return max(1, (int) round($diff / 3600)) . ' hours'; }

    return max(1, (int) round($diff / 86400)) . ' days';
}

/* ------------------------------------------------------------ hook registry */

$GLOBALS['byrm_actions'] = [];

function add_action(string $hook, $cb, int $p = 10, int $n = 1): void { $GLOBALS['byrm_actions'][$hook][] = $cb; }
function do_action(string $hook, ...$args): void
{
    foreach ($GLOBALS['byrm_actions'][$hook] ?? [] as $cb) { $cb(...$args); }
}
function did_action(string $hook): int { return 1; }
function add_filter(...$a): void {}

function apply_filters(string $hook, $value, ...$rest)
{
    if ($hook === 'byrm_header_config' && is_array($value)) {
        $value['discord_url'] = $GLOBALS['BYRM_DISCORD'];
    }

    if ($hook === 'byrm_community_config' && is_array($value)) {
        $value['forum_url']     = 'https://forums.cncnet.org/';
        $value['contact_email'] = 'hello@bankofyrmaps.com';
    }

    return $value;
}

/* ------------------------------------------------------------- environment */

function home_url(string $p = '/'): string { return '/' . ltrim($p, '/'); }
function get_stylesheet_directory_uri(): string { return THEME_URI; }
function get_bloginfo(string $s = '', string $f = ''): string { return $s === 'charset' ? 'UTF-8' : 'Bank of YR Maps'; }
function get_search_query(): string { return ''; }
function has_custom_logo(): bool { return false; }
function the_custom_logo(): void {}
function has_nav_menu(string $l): bool { return false; }
function wp_nav_menu(array $a = []): void {}
function is_singular(string $t = ''): bool { return false; }
function is_front_page(): bool { return false; }
function is_page($p = ''): bool { return true; }
function is_wp_error($t): bool { return false; }
function is_user_logged_in(): bool { return (bool) $GLOBALS['BYRM_ADMIN']; }
function current_user_can(string $c): bool { return (bool) $GLOBALS['BYRM_ADMIN']; }
function user_can($u, string $c): bool { return (int) $u === 1; }
function post_type_exists(string $t): bool { return true; }
function get_post_type_archive_link(string $t) { return '/maps/'; }
function get_transient(string $k) { return false; }
function set_transient(string $k, $v, int $t = 0): bool { return true; }
function delete_transient(string $k): bool { return true; }
function wp_count_posts(string $t) { return (object) ['publish' => 7]; }
function get_option(string $k, $d = false) { return $k === 'require_name_email' ? 1 : $d; }
function get_page_by_path(string $p) { return (object) ['ID' => 12]; }
function post_password_required($p = null): bool { return false; }
function wp_die($m, $t = '', $a = []): void { echo $m; exit; }
function wp_get_current_commenter(): array
{
    return ['comment_author' => '', 'comment_author_email' => '', 'comment_author_url' => ''];
}

final class ByrmWpdb
{
    public string $postmeta = 'wp_postmeta';
    public string $posts    = 'wp_posts';
    public string $comments = 'wp_comments';

    public function prepare(string $s, ...$a): string { return $s; }

    public function get_var(string $s)
    {
        return str_contains($s, 'comment_author') ? ($GLOBALS['BYRM_EMPTY'] ? 0 : 5) : 18432;
    }

    public function get_results(string $s, $out = null): array
    {
        if ($GLOBALS['BYRM_EMPTY']) {
            return [];
        }

        return [
            ['name' => 'TanyaFan88',      'total' => '31', 'comment_id' => '101'],
            ['name' => 'Yuri_Prime',      'total' => '24', 'comment_id' => '102'],
            ['name' => 'RedSquareDancer', 'total' => '17', 'comment_id' => '103'],
            ['name' => 'OreMiner',        'total' => '12', 'comment_id' => '104'],
            ['name' => 'Kirov_Reporting', 'total' => '9',  'comment_id' => '105'],
            ['name' => 'desolator_main',  'total' => '6',  'comment_id' => '106'],
        ];
    }
}
$GLOBALS['wpdb'] = new ByrmWpdb();

function wp_count_comments($p = 0) { return (object) ['approved' => $GLOBALS['BYRM_EMPTY'] ? 0 : 128]; }

/* ---------------------------------------------------------------- comments */

/**
 * A stand-in for WP_Comment, with just the properties our code reads.
 */
final class ByrmComment
{
    public function __construct(
        public int $comment_ID,
        public string $comment_author,
        public string $comment_content,
        public int $comment_parent,
        public int $minutes_ago,
        public int $user_id = 0,
        public string $comment_approved = '1',
        public int $comment_post_ID = 12
    ) {}
}

$GLOBALS['BYRM_COMMENTS'] = $EMPTY ? [] : [
    new ByrmComment(201, 'Yuri_Prime', '<p>Just played the new eight-player naval map three times tonight. The middle island is a kill box — whoever takes it first basically wins. Needs another route in from the south.</p>', 0, 14, 0),
    new ByrmComment(202, 'Basit', '<p>Fair point. I left the south shelf shallow on purpose so hovercraft could cross, but if nobody uses it then it is not really a route. I will widen it and repost.</p>', 201, 9, 1),
    new ByrmComment(203, 'TanyaFan88', '<p>Please do. Also the ore in the north-east runs dry about twelve minutes in, which makes the top two spots rough in a long game.</p>', 201, 6, 0),
    new ByrmComment(204, 'Kirov_Reporting', '<p>Looking for three more for a 4v4 on the urban maps tonight, around 9pm UTC. First time with custom maps so be gentle.</p>', 0, 95, 0),
    new ByrmComment(205, 'RedSquareDancer', "<p>I'm in. Do I need the .yrm or the RA2 version if I'm on CnCNet?</p>", 204, 71, 0),
    new ByrmComment(206, 'Kirov_Reporting', '<p>.yrm, renamed to .map — there is a guide on this site that walks through it in four steps.</p>', 205, 64, 0),
    new ByrmComment(207, 'OreMiner', '<p>Map request: something snowy and tight, four players, no naval at all. Every winter map I can find is enormous.</p>', 0, 1480, 0),
    new ByrmComment(208, 'desolator_main', '<p>Seconding this. A small snow map with choke points would be excellent.</p>', 207, 1390, 0),
    new ByrmComment(209, 'Guest', '<p>Is this one waiting for approval? Testing how that looks.</p>', 0, 3, 0, '0'),
];

function get_comment($c = 0)
{
    if ($c instanceof ByrmComment) { return $c; }

    foreach ($GLOBALS['BYRM_COMMENTS'] as $comment) {
        if ($comment->comment_ID === (int) $c) { return $comment; }
    }

    // The voices panel references comment IDs 101-106, which are not on this page.
    return new ByrmComment((int) $c, 'Someone', '', 0, 100);
}

function get_comment_author($c = 0): string { return get_comment($c)->comment_author; }
function get_comment_link($c = 0, array $a = []): string { return '#comment-' . get_comment($c)->comment_ID; }
function get_comment_date(string $f = '', $c = 0): string
{
    $stamp = time() - (get_comment($c)->minutes_ago * 60);

    return $f === 'U' ? (string) $stamp : date($f ?: 'j F Y', $stamp);
}
function comment_ID(): void { echo (string) $GLOBALS['comment']->comment_ID; }
function comment_text($c = 0, array $a = []): void { echo get_comment($c)->comment_content; }
function comment_class($class = '', $c = null, $p = null, bool $echo = true): void
{
    $list = is_array($class) ? $class : [$class];
    echo 'class="' . esc_attr(implode(' ', array_filter($list))) . '"';
}
function comments_open($p = 0): bool { return ! $GLOBALS['BYRM_CLOSED']; }
function get_comments_number($p = 0): int { return count($GLOBALS['BYRM_COMMENTS']); }
function have_comments(): bool { return (bool) $GLOBALS['BYRM_COMMENTS']; }
function paginate_comments_links(array $a = [])
{
    // Two pages, so the control is exercised.
    if (! empty($GLOBALS['BYRM_COMMENTS'])) {
        return '<ul><li><span class="current">1</span></li><li><a href="?cpage=2">2</a></li>'
            . '<li><a class="next" href="?cpage=2">Older</a></li></ul>';
    }

    return '';
}
function edit_comment_link(?string $t = null, string $b = '', string $a = ''): void
{
    if ($GLOBALS['BYRM_ADMIN']) { echo $b . '<a href="#edit">' . esc_html((string) $t) . '</a>' . $a; }
}
function comment_reply_link(array $args = [], $c = null): void
{
    $depth = (int) ($args['depth'] ?? 1);
    $max   = (int) ($args['max_depth'] ?? 4);

    if ($depth >= $max) { return; }

    printf(
        '%s<a class="comment-reply-link %s" href="#respond">%s</a>%s',
        $args['before'] ?? '',
        esc_attr($args['class'] ?? ''),
        esc_html($args['reply_text'] ?? 'Reply'),
        $args['after'] ?? ''
    );
}

function get_avatar($id, int $size = 96, string $d = '', string $alt = '', array $args = [])
{
    if ($GLOBALS['BYRM_NOAV']) { return false; }

    $name  = $id instanceof ByrmComment ? $id->comment_author : 'x';
    $hue   = crc32($name) % 360;
    $svg   = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '">'
        . '<rect width="100%" height="100%" fill="hsl(' . $hue . ',38%,34%)"/></svg>';

    return '<img class="' . esc_attr($args['class'] ?? '') . '" width="' . $size . '" height="' . $size
        . '" alt="" src="data:image/svg+xml;base64,' . base64_encode($svg) . '">';
}

function get_comments(array $args = []): array
{
    $all = $GLOBALS['BYRM_COMMENTS'];

    usort($all, static fn ($a, $b) => $a->minutes_ago <=> $b->minutes_ago);

    return array_slice(
        array_values(array_filter($all, static fn ($c) => $c->comment_approved === '1')),
        0,
        (int) ($args['number'] ?? 10)
    );
}

/**
 * Stands in for wp_list_comments(), calling our callback exactly the way
 * Walker_Comment does: the item callback opens the <li>, the walker prints any
 * replies inside <ol class="children">, then the end callback closes it.
 */
function wp_list_comments(array $args = [], $comments = null): void
{
    $all = $comments ?? $GLOBALS['BYRM_COMMENTS'];

    $children = [];

    foreach ($all as $comment) { $children[$comment->comment_parent][] = $comment; }

    $walk = static function (int $parent, int $depth) use (&$walk, $children, $args): void {
        foreach ($children[$parent] ?? [] as $comment) {
            $GLOBALS['comment']       = $comment;
            $GLOBALS['comment_depth'] = $depth;

            ($args['callback'])($comment, $args, $depth);

            if (! empty($children[$comment->comment_ID]) && $depth < (int) $args['max_depth']) {
                echo '<ol class="children">';
                $walk($comment->comment_ID, $depth + 1);
                echo '</ol>';
            }

            ($args['end-callback'])($comment, $args, $depth);
        }
    };

    $walk(0, 1);
}

/**
 * Stands in for comment_form(). Mirrors the real markup closely enough to test
 * the layout: the #respond wrapper, the #reply-title heading, the fields, the
 * submit paragraph, the hidden id fields, and the comment_form action our
 * spam-guard fields hang off.
 */
function comment_form(array $args = [], $post_id = null): void
{
    if (! comments_open()) { return; }

    echo '<div id="respond" class="' . esc_attr($args['class_container']) . '">';
    echo $args['title_reply_before'] . esc_html($args['title_reply'])
        . ($args['cancel_reply_before'] ?? '')
        . '<a rel="nofollow" id="cancel-comment-reply-link" href="#respond" style="display:none">'
        . esc_html($args['cancel_reply_link']) . '</a>'
        . ($args['cancel_reply_after'] ?? '')
        . $args['title_reply_after'];

    echo '<form action="#" method="post" class="' . esc_attr($args['class_form']) . '" novalidate>';

    if ($GLOBALS['BYRM_ADMIN']) {
        echo '<p class="logged-in-as">Signed in as <strong>Basit</strong>. <a href="#">Sign out?</a></p>';
    } else {
        foreach ($args['fields'] as $field) { echo $field; }
    }

    echo $args['comment_field'];

    echo '<p class="form-submit"><input name="submit" type="submit" class="'
        . esc_attr($args['class_submit']) . '" value="' . esc_attr($args['label_submit']) . '">'
        . '<input type="hidden" name="comment_post_ID" value="12">'
        . '<input type="hidden" name="comment_parent" id="comment_parent" value="0"></p>';

    do_action('comment_form', 12);

    echo '</form></div>';
}

function comments_template(string $file = '/comments.php', bool $separate = false): void
{
    require ABSPATH . 'wp-content/themes/astra-child' . $file;
}

/* ------------------------------------------------------------- the page --- */

$GLOBALS['byrm_loop'] = true;
function have_posts(): bool { return $GLOBALS['byrm_loop']; }
function the_post(): void { $GLOBALS['byrm_loop'] = false; }
function get_the_ID(): int { return 12; }
function the_title(): void { echo esc_html('Community'); }
function get_the_title($p = null): string
{
    return (int) $p === 12 ? 'Community' : 'Arctic Crossroads (4)';
}
function get_permalink($p = null): string { return (int) $p === 12 ? '/community/' : '/maps/arctic-crossroads/'; }
function get_the_content(): string
{
    return isset($_GET['extra'])
        ? "A note typed into the page editor.\n\nIt renders underneath the designed sections."
        : '';
}
function the_content(): void { echo '<p>' . str_replace("\n\n", '</p><p>', esc_html(get_the_content())) . '</p>'; }

/* ---------------------------------------------------------- document shell */

function language_attributes(): void { echo 'lang="en"'; }
function bloginfo(string $s = ''): void { echo $s === 'charset' ? 'UTF-8' : ''; }
function body_class(): void { echo 'class="page byrm-fullwidth"'; }
function wp_body_open(): void {}
function wp_head(): void
{
    echo '<title>Community — Bank of YR Maps</title>' . "\n";

    foreach (['header', 'footer', 'home', 'community'] as $s) {
        echo '<link rel="stylesheet" href="' . THEME_URI . '/assets/css/' . $s . '.css">' . "\n";
    }

    echo '<style>body{margin:0;background:#0d1117;}</style>' . "\n";
}
function wp_footer(): void
{
    foreach (['header', 'footer', 'community'] as $s) {
        echo '<script src="' . THEME_URI . '/assets/js/' . $s . '.js" defer></script>' . "\n";
    }
}

$GLOBALS['BYRM_EMPTY']   = $EMPTY;
$GLOBALS['BYRM_CLOSED']  = $CLOSED;
$GLOBALS['BYRM_ADMIN']   = $ADMIN;
$GLOBALS['BYRM_NOAV']    = $NOAV;
$GLOBALS['BYRM_DISCORD'] = $DISCORD;

require ABSPATH . 'wp-content/themes/astra-child/inc/site-header.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/site-footer.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/home-helpers.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/page-shell.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/community-helpers.php';
require ABSPATH . 'wp-content/themes/astra-child/page-community.php';
