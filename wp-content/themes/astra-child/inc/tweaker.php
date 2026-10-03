<?php
/**
 * Map tweaker — upload a map, pick some changes, get it back.
 *
 * HOW THIS WORKS, AND WHY IT IS SAFE
 *
 * An RA2 / Yuri's Revenge map file is an INI text file. Most of it is ordinary
 * key=value lines, but a handful of sections — IsoMapPack5, OverlayPack,
 * OverlayDataPack, PreviewPack, Digest — hold the terrain and preview as base64
 * blobs split across numbered lines. Those must survive untouched or the map is
 * ruined.
 *
 * So this file never parses the map into a data structure and writes it back
 * out. It edits the text in place, line by line: find the section, replace the
 * one line that sets the key, or add a line, or append a whole new section at
 * the end. Every other byte of the file — including every base64 blob, every
 * comment and the original line endings — comes out exactly as it went in.
 * The packed sections are also on a hard deny list, so a badly written tweak
 * cannot reach them.
 *
 * The changes themselves are rules.ini overrides. A map file may redeclare any
 * rules section, and the game merges it over the global rules for that map
 * only. That is the long-established way "mod maps" work.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sections that are never written to, whatever a tweak asks for.
 *
 * These hold packed binary as base64. Inserting a line into one corrupts the
 * map, so the writer refuses them outright rather than trusting the table below
 * to be correct.
 *
 * @return string[] Lowercase section names.
 */
function byrm_tweak_protected_sections() {
	return array( 'isomappack5', 'overlaypack', 'overlaydatapack', 'previewpack', 'digest' );
}

/* =========================================================================
 * THE TWEAKS
 *
 * Each entry is a set of rules overrides written into the uploaded map.
 *
 * Every section and key below was read out of a real Yuri's Revenge
 * rulesmd.ini rather than written from memory. Earlier versions of this file
 * guessed, and the guesses were silently wrong: OreGrows for TiberiumGrows,
 * "bias" keys that do not exist, crate settings in [General] instead of
 * [CrateRules], HarvesterImmune in [SpecialFlags] instead of [CombatDamage].
 * A tweak with a misspelt key does not fail — it just does nothing.
 *
 * Three different sections can govern the same switch, so where that is the
 * case all of them are written:
 *
 *   [General]                    the rule itself
 *   [MultiplayerDialogSettings]  what the skirmish and online lobby defaults to
 *   [SpecialFlags]               the map's own copy, written by the map editor
 *
 * [MultiplayerDialogSettings] is the one that was missing, and it is the only
 * place starting credits can be set — ore value and growth rate cannot raise
 * the money you begin a match with.
 *
 * Only the numbers are a matter of taste. Change one here and it takes effect
 * on the next upload; nothing else needs touching. Add your own with the
 * byrm_tweaks filter.
 * ====================================================================== */

/**
 * @return array<string, array<string, mixed>>
 */
function byrm_tweaks() {
	$tweaks = array(

		'money' => array(
			'label' => __( 'Unlimited money', 'astra-child' ),
			'note'  => __( 'Everyone starts on a million credits, ore and gems are worth two hundred times normal, ore regrows and spreads constantly, and money crates pay 100,000.', 'astra-child' ),
			'ini'   => array(
				// This is the one that does the work. [MultiplayerDialogSettings]
				// is what fills the credits slider in the skirmish and online
				// lobby, so you can see it took effect before the match even
				// starts. Min and Max have to move with it or the slider clamps
				// straight back down to the stock 10,000.
				'MultiplayerDialogSettings' => array(
					'MinMoney'       => '1000000',
					'Money'          => '1000000',
					'MaxMoney'       => '1000000',
					'MoneyIncrement' => '1000',
				),
				// Red Alert 2 kept Tiberian Sun's internal names: the switches
				// are TiberiumGrows / TiberiumSpreads, never OreGrows. They live
				// in [General] in the rules, are defaulted for the lobby in
				// [MultiplayerDialogSettings], and the map keeps its own copy in
				// [SpecialFlags]. Write all three so nothing downstream can
				// quietly turn ore growth back off.
				'General' => array(
					'TiberiumGrows'   => 'yes',
					'TiberiumSpreads' => 'yes',
				),
				'SpecialFlags' => array(
					'TiberiumGrows'   => 'yes',
					'TiberiumSpreads' => 'yes',
				),
				// Credit value is per bail and is set per ore type, not globally.
				// Stock is 25 for ore and 50 for gems, and a harvester holds 20
				// bails, so a full load is normally about 500 credits. At 5,000 a
				// bail one load is 100,000. Growth and Spread are delays in
				// frames, so smaller is faster, and the percentages are the odds
				// it fires; stock is 2200 and .06.
				'Riparius' => array(
					'Value'            => '5000',
					'Growth'           => '10',
					'GrowthPercentage' => '1',
					'Spread'           => '10',
					'SpreadPercentage' => '1',
				),
				'Cruentus' => array(
					'Value'            => '10000',
					'Growth'           => '10',
					'GrowthPercentage' => '1',
					'Spread'           => '10',
					'SpreadPercentage' => '1',
				),
				'Vinifera' => array(
					'Value'            => '5000',
					'Growth'           => '10',
					'GrowthPercentage' => '1',
					'Spread'           => '10',
					'SpreadPercentage' => '1',
				),
				'Aboreus'  => array(
					'Value'            => '5000',
					'Growth'           => '10',
					'GrowthPercentage' => '1',
					'Spread'           => '10',
					'SpreadPercentage' => '1',
				),
				// Money crates. The fields are weight, animation, allowed, then
				// the payout — stock pays 2,000.
				'Powerups' => array(
					'Money' => '20,MONEY,yes,100000',
				),
			),
		),

		'brutal_ai' => array(
			'label' => __( 'Brutal AI', 'astra-child' ),
			'note'  => __( 'The lobby defaults to Brutal difficulty, and every army builds faster, shoots harder and takes more punishment. The computer gains the most from it because it never stops building.', 'astra-child' ),
			'ini'   => array(
				// The lobby difficulty default: 0 easy, 1 normal, 2 brutal.
				'MultiplayerDialogSettings' => array(
					'AIDifficulty' => '2',
				),
				// The real key names have no "Bias" suffix: Firepower, Armor,
				// ROF, Cost, BuildTime, Groundspeed. Stock is 1.0 across the
				// board, with ROF and Cost "smaller is better" and Armor "larger
				// is tougher". All three bands get the same numbers, because
				// which band a house ends up in depends on the lobby.
				'Easy'      => array(
					'Firepower'     => '2.0',
					'Armor'         => '2.0',
					'ROF'           => '.6',
					'Cost'          => '.5',
					'BuildTime'     => '.3',
					'Groundspeed'   => '1.2',
					'Airspeed'      => '1.2',
					'RepairDelay'   => '.01',
					'BuildDelay'    => '.01',
					'BuildSlowdown' => 'no',
					'DestroyWalls'  => 'yes',
					'ContentScan'   => 'yes',
				),
				'Normal'    => array(
					'Firepower'     => '2.0',
					'Armor'         => '2.0',
					'ROF'           => '.6',
					'Cost'          => '.5',
					'BuildTime'     => '.3',
					'Groundspeed'   => '1.2',
					'Airspeed'      => '1.2',
					'RepairDelay'   => '.01',
					'BuildDelay'    => '.01',
					'BuildSlowdown' => 'no',
					'DestroyWalls'  => 'yes',
					'ContentScan'   => 'yes',
				),
				'Difficult' => array(
					'Firepower'     => '2.0',
					'Armor'         => '2.0',
					'ROF'           => '.6',
					'Cost'          => '.5',
					'BuildTime'     => '.3',
					'Groundspeed'   => '1.2',
					'Airspeed'      => '1.2',
					'RepairDelay'   => '.01',
					'BuildDelay'    => '.01',
					'BuildSlowdown' => 'no',
					'DestroyWalls'  => 'yes',
					'ContentScan'   => 'yes',
				),
			),
		),

		'self_repair' => array(
			'label' => __( 'Auto-repair', 'astra-child' ),
			'note'  => __( 'Repairs run many times faster and cost almost nothing, and infantry and vehicles heal themselves quickly.', 'astra-child' ),
			'ini'   => array(
				// Rate keys are minutes between repair ticks, so smaller is
				// faster; Step keys are hit points per tick. Stock: RepairRate
				// .016, RepairStep 8, RepairPercent 15%. The SelfHeal keys need a
				// Tech Hospital or Machine Shop on the map to come into play, so
				// the Repair keys above them are what you will actually notice.
				'General' => array(
					'RepairPercent'           => '2%',
					'RepairRate'              => '.001',
					'RepairStep'              => '60',
					'URepairRate'             => '.001',
					'IRepairRate'             => '.001',
					'IRepairStep'             => '60',
					'SelfHealInfantryAmount'  => '60',
					'SelfHealInfantryFrames'  => '15',
					'SelfHealUnitAmount'      => '40',
					'SelfHealUnitFrames'      => '15',
				),
			),
		),

		'fast_build' => array(
			'label' => __( 'Fast building', 'astra-child' ),
			'note'  => __( 'Everything builds about seven times faster, and structures finish unpacking almost instantly.', 'astra-child' ),
			'ini'   => array(
				// BuildSpeed is minutes to produce a 1000 credit item, so smaller
				// is faster. Stock is .7. BuildupTime is how long the unpacking
				// animation runs; stock is .06.
				'General' => array(
					'BuildSpeed'  => '.1',
					'BuildupTime' => '.01',
				),
			),
		),

		'veteran' => array(
			'label' => __( 'Veteran units', 'astra-child' ),
			'note'  => __( 'Starting forces come out of the gate as veterans, and units rank up three times sooner.', 'astra-child' ),
			'ini'   => array(
				// InitialVeteran is a [General] rule; the map editor also writes
				// its own copy into [SpecialFlags]. VeteranRatio is how many
				// times its own value a unit must destroy to promote; stock 3.0.
				'General' => array(
					'InitialVeteran' => 'yes',
					'VeteranRatio'   => '1.0',
				),
				'SpecialFlags' => array(
					'InitialVeteran' => 'yes',
				),
			),
		),

		'harvesters' => array(
			'label' => __( 'Protected harvesters', 'astra-child' ),
			'note'  => __( 'Ore miners and slave miners cannot be shot, so mining never gets interrupted.', 'astra-child' ),
			'ini'   => array(
				// The rule itself lives in [CombatDamage]; the lobby calls the
				// same thing HarvesterTruce, and the map keeps its own copy.
				'CombatDamage' => array(
					'HarvesterImmune' => 'yes',
				),
				'MultiplayerDialogSettings' => array(
					'HarvesterTruce' => 'yes',
				),
				'SpecialFlags' => array(
					'HarvesterImmune' => 'yes',
				),
			),
		),

		'mcv_redeploy' => array(
			'label' => __( 'Redeployable MCV', 'astra-child' ),
			'note'  => __( 'Construction yards can pack back up into an MCV and move, so you can relocate a base.', 'astra-child' ),
			'ini'   => array(
				// Note the engine spells the lobby key MCVRedeploys while the map
				// section calls it MCVDeploy. Both are written.
				'MultiplayerDialogSettings' => array(
					'MCVRedeploys' => 'yes',
				),
				'SpecialFlags' => array(
					'MCVDeploy' => 'yes',
				),
			),
		),

		'crates' => array(
			'label' => __( 'Crates everywhere', 'astra-child' ),
			'note'  => __( 'Fifty bonus crates on the field instead of one, coming back every thirty seconds instead of every three minutes.', 'astra-child' ),
			'ini'   => array(
				// Crates live in [CrateRules], not [General]. Stock: minimum 1,
				// maximum 255, regen every 3 minutes. The lobby and the map both
				// carry their own on/off switch for crates.
				'CrateRules' => array(
					'CrateMinimum' => '50',
					'CrateMaximum' => '255',
					'CrateRegen'   => '.5',
				),
				'MultiplayerDialogSettings' => array(
					'Crates' => 'yes',
				),
				'SpecialFlags' => array(
					'Crates' => 'yes',
				),
			),
		),
	);

	return apply_filters( 'byrm_tweaks', $tweaks );
}

/* =========================================================================
 * The INI writer
 * ====================================================================== */

/**
 * Which line ending the file uses.
 *
 * Map files written on Windows use CRLF. Rewriting them with LF would still
 * load, but it needlessly changes every line of the file; keeping the original
 * means the only bytes that differ are the ones a tweak asked to change.
 *
 * @param  string $ini Whole file.
 * @return string "\r\n" or "\n".
 */
function byrm_ini_eol( $ini ) {
	return ( false !== strpos( $ini, "\r\n" ) ) ? "\r\n" : "\n";
}

/**
 * Set one key inside one section, adding either if missing.
 *
 * @param  string[] $lines   File split into lines, edited in place.
 * @param  string   $section Section name without brackets.
 * @param  string   $key     Key to set.
 * @param  string   $value   Value to set.
 * @return bool True when something actually changed.
 */
function byrm_ini_set( &$lines, $section, $key, $value ) {
	if ( in_array( strtolower( $section ), byrm_tweak_protected_sections(), true ) ) {
		return false;
	}

	$start = -1;
	$end   = -1;

	foreach ( $lines as $i => $line ) {
		if ( ! preg_match( '/^\s*\[([^\]]+)\]/', $line, $m ) ) {
			continue;
		}

		if ( -1 === $start ) {
			if ( 0 === strcasecmp( trim( $m[1] ), $section ) ) {
				$start = $i;
			}

			continue;
		}

		// First header after ours closes the section.
		$end = $i;
		break;
	}

	// No such section: add it.
	//
	// Not at the end of the file, though. Every map ever written by the editor
	// finishes with [Digest], and a file with eight unfamiliar sections sitting
	// after it is the one structurally odd thing this tool could produce. New
	// sections therefore go in just before [Digest], leaving the file shaped
	// exactly like one the map editor itself wrote.
	if ( -1 === $start ) {
		$at = count( $lines );

		foreach ( $lines as $i => $line ) {
			if ( preg_match( '/^\s*\[Digest\]/i', $line ) ) {
				$at = $i;
				break;
			}
		}

		$block = array( '[' . $section . ']', $key . '=' . $value, '' );

		// A blank line before the new section, unless there already is one.
		if ( $at > 0 && '' !== trim( (string) $lines[ $at - 1 ] ) ) {
			array_unshift( $block, '' );
		}

		array_splice( $lines, $at, 0, $block );

		return true;
	}

	if ( -1 === $end ) {
		$end = count( $lines );
	}

	// Replace the key if the section already sets it.
	for ( $i = $start + 1; $i < $end; $i++ ) {
		if ( preg_match( '/^\s*' . preg_quote( $key, '/' ) . '\s*=/i', $lines[ $i ] ) ) {
			if ( $lines[ $i ] === $key . '=' . $value ) {
				return false;
			}

			$lines[ $i ] = $key . '=' . $value;

			return true;
		}
	}

	// Otherwise insert after the section's last non-blank line, so the file does
	// not grow a run of stray blank lines every time a key is added.
	$insert = $start + 1;

	for ( $i = $start + 1; $i < $end; $i++ ) {
		if ( '' !== trim( $lines[ $i ] ) ) {
			$insert = $i + 1;
		}
	}

	array_splice( $lines, $insert, 0, array( $key . '=' . $value ) );

	return true;
}

/**
 * Apply a set of tweaks to a map's text.
 *
 * @param  string   $ini  Whole map file.
 * @param  string[] $keys Tweak keys chosen.
 * @return array{ini: string, applied: string[], changes: int}
 */
function byrm_tweak_apply( $ini, $keys ) {
	$table   = byrm_tweaks();
	$eol     = byrm_ini_eol( $ini );
	$lines   = preg_split( "/\r\n|\n|\r/", $ini );
	$applied = array();
	$changes = 0;

	foreach ( $keys as $key ) {
		if ( ! isset( $table[ $key ]['ini'] ) ) {
			continue;
		}

		$did = 0;

		foreach ( $table[ $key ]['ini'] as $section => $pairs ) {
			foreach ( $pairs as $name => $value ) {
				if ( byrm_ini_set( $lines, $section, $name, (string) $value ) ) {
					++$did;
				}
			}
		}

		if ( $did > 0 ) {
			$applied[] = $key;
			$changes  += $did;
		}
	}

	$ini = implode( $eol, $lines );

	// Appending a section at the end of a file can leave the last line without
	// a newline. Nothing in the game minds, but some INI readers do, and the
	// original files all end cleanly — so this one should too.
	if ( '' !== $ini && substr( $ini, -strlen( $eol ) ) !== $eol ) {
		$ini .= $eol;
	}

	return array(
		'ini'     => $ini,
		'applied' => $applied,
		'changes' => $changes,
	);
}

/**
 * Mark the map's in-game name so a tweaked copy is not mistaken for the original.
 *
 * @param string[] $lines Map lines, edited in place.
 */
function byrm_tweak_rename( &$lines ) {
	$current = '';

	$in_basic = false;

	foreach ( $lines as $line ) {
		if ( preg_match( '/^\s*\[([^\]]+)\]/', $line, $m ) ) {
			$in_basic = ( 0 === strcasecmp( trim( $m[1] ), 'Basic' ) );

			continue;
		}

		if ( $in_basic && preg_match( '/^\s*Name\s*=\s*(.*)$/i', $line, $m ) ) {
			$current = trim( $m[1] );

			break;
		}
	}

	if ( '' === $current ) {
		$current = __( 'Map', 'astra-child' );
	}

	// Parentheses, not square brackets. A bracket is what starts a section
	// header, and there is no sense putting one inside a value and hoping a
	// twenty-five year old parser reads it the way a modern one would.
	if ( false === stripos( $current, '(Tweaked)' ) ) {
		byrm_ini_set( $lines, 'Basic', 'Name', $current . ' (Tweaked)' );
	}
}

/* =========================================================================
 * The page, the upload, and the download
 * ====================================================================== */

/**
 * Is this the map tweaker page?
 *
 * @return bool
 */
function byrm_is_tweaker_page() {
	return did_action( 'wp' ) && is_page( array( 'map-editor' ) );
}

/**
 * Limits. Filterable, because what a shared host will take varies.
 *
 * @return array{bytes: int, per_hour: int, extensions: string[]}
 */
function byrm_tweak_limits() {
	return apply_filters(
		'byrm_tweak_limits',
		array(
			// Real maps are tens to a few hundred kilobytes. Four megabytes is
			// far more than any of them and still small enough that nobody can
			// use this as free file processing.
			'bytes'      => 4 * MB_IN_BYTES,
			'per_hour'   => 20,
			'extensions' => array( 'map', 'mpr', 'yrm' ),
		)
	);
}

/**
 * Messages for everything that can go wrong, keyed by the code put in the URL.
 *
 * @return array<string, string>
 */
function byrm_tweak_errors() {
	$limits = byrm_tweak_limits();

	return array(
		'nofile'  => __( 'No file arrived. Choose a map file and try again.', 'astra-child' ),
		'upload'  => __( 'The upload did not finish. That is usually a dropped connection — try once more.', 'astra-child' ),
		'big'     => sprintf(
			/* translators: %s: maximum file size, e.g. "4 MB". */
			__( 'That file is larger than %s. Map files are nowhere near that big, so this is probably not a map.', 'astra-child' ),
			size_format( $limits['bytes'] )
		),
		'ext'     => sprintf(
			/* translators: %s: list of allowed file extensions. */
			__( 'Only %s files can be edited.', 'astra-child' ),
			'.' . implode( ', .', $limits['extensions'] )
		),
		'notmap'  => __( 'That does not look like a Red Alert 2 map. The file needs a [Basic] section and map data — if you zipped it, unzip it first and upload the map itself.', 'astra-child' ),
		'binary'  => __( 'That file is not readable as a map. If it is inside a .zip or .rar, extract it and upload the map file on its own.', 'astra-child' ),
		'notweak' => __( 'Pick at least one change to make.', 'astra-child' ),
		'nochange' => __( 'Nothing changed — that map already has every change you picked.', 'astra-child' ),
		'rate'    => sprintf(
			/* translators: %d: number of maps per hour. */
			__( 'That is %d maps in an hour, which is the limit. Try again a little later.', 'astra-child' ),
			(int) $limits['per_hour']
		),
		'nonce'   => __( 'That form had gone stale. Reload the page and try again.', 'astra-child' ),
	);
}

/**
 * Has this visitor had their allowance this hour?
 *
 * The IP is salted and hashed, never stored, exactly as the download counter
 * does it.
 *
 * @param  bool $count Increment as well as check.
 * @return bool True when still under the limit.
 */
function byrm_tweak_rate_ok( $count = false ) {
	$limits = byrm_tweak_limits();
	$ip     = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) wp_unslash( $_SERVER['REMOTE_ADDR'] ) : '';
	$key    = 'byrm_tw_' . md5( wp_salt() . '|tweak|' . $ip );
	$used   = (int) get_transient( $key );

	if ( $used >= (int) $limits['per_hour'] ) {
		return false;
	}

	if ( $count ) {
		set_transient( $key, $used + 1, HOUR_IN_SECONDS );
	}

	return true;
}

/**
 * Send the visitor back to the form with something to read.
 *
 * @param string $code Key from byrm_tweak_errors().
 */
function byrm_tweak_fail( $code ) {
	$url = get_permalink();

	if ( ! $url ) {
		$url = home_url( '/map-editor/' );
	}

	wp_safe_redirect( add_query_arg( 'byrm_err', rawurlencode( $code ), $url ) . '#byrm-tweak-form' );
	exit;
}

/**
 * Take the upload, apply the chosen changes, hand the file straight back.
 *
 * Nothing is written to disk at any point: the map is read from the temporary
 * upload, edited in memory and streamed to the browser. There is no directory
 * of other people's maps to leak, and nothing to clean up afterwards.
 */
function byrm_tweak_handle() {
	if ( ! byrm_is_tweaker_page() || 'POST' !== ( isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : '' ) ) {
		return;
	}

	$limits = byrm_tweak_limits();

	// A file bigger than PHP's own post_max_size arrives as an empty POST with
	// no nonce at all. Without this check that shows up as "the form went
	// stale", which sends people hunting for the wrong problem.
	$posted = isset( $_SERVER['CONTENT_LENGTH'] ) ? (int) $_SERVER['CONTENT_LENGTH'] : 0;

	if ( empty( $_POST ) && $posted > 0 ) {
		byrm_tweak_fail( 'big' );
	}

	if ( ! isset( $_POST['byrm_tweak_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['byrm_tweak_nonce'] ) ), 'byrm_tweak' ) ) {
		byrm_tweak_fail( 'nonce' );
	}

	if ( ! byrm_tweak_rate_ok() ) {
		byrm_tweak_fail( 'rate' );
	}

	$chosen = isset( $_POST['byrm_tweak'] ) && is_array( $_POST['byrm_tweak'] )
		? array_map( 'sanitize_key', wp_unslash( $_POST['byrm_tweak'] ) )
		: array();

	$chosen = array_values( array_intersect( $chosen, array_keys( byrm_tweaks() ) ) );

	if ( ! $chosen ) {
		byrm_tweak_fail( 'notweak' );
	}

	if ( ! isset( $_FILES['byrm_map'] ) || ! is_array( $_FILES['byrm_map'] ) ) {
		byrm_tweak_fail( 'nofile' );
	}

	$file = $_FILES['byrm_map']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- each member is validated below.

	if ( ! isset( $file['error'] ) || UPLOAD_ERR_NO_FILE === (int) $file['error'] ) {
		byrm_tweak_fail( 'nofile' );
	}

	if ( UPLOAD_ERR_INI_SIZE === (int) $file['error'] || UPLOAD_ERR_FORM_SIZE === (int) $file['error'] ) {
		byrm_tweak_fail( 'big' );
	}

	if ( UPLOAD_ERR_OK !== (int) $file['error'] ) {
		byrm_tweak_fail( 'upload' );
	}

	$tmp = isset( $file['tmp_name'] ) ? (string) $file['tmp_name'] : '';

	// The one check that proves this really came through an HTTP upload.
	if ( '' === $tmp || ! is_uploaded_file( $tmp ) ) {
		byrm_tweak_fail( 'nofile' );
	}

	if ( (int) $file['size'] > (int) $limits['bytes'] ) {
		byrm_tweak_fail( 'big' );
	}

	$name      = isset( $file['name'] ) ? sanitize_file_name( (string) wp_unslash( $file['name'] ) ) : 'map';
	$extension = strtolower( (string) pathinfo( $name, PATHINFO_EXTENSION ) );

	if ( ! in_array( $extension, $limits['extensions'], true ) ) {
		byrm_tweak_fail( 'ext' );
	}

	$ini = file_get_contents( $tmp ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- a temporary upload, not a remote URL.

	if ( false === $ini || '' === $ini ) {
		byrm_tweak_fail( 'nofile' );
	}

	// Map files are text. A null byte means an archive, an image, or something
	// else entirely — and whatever it is, editing it as INI would be nonsense.
	if ( false !== strpos( $ini, "\0" ) ) {
		byrm_tweak_fail( 'binary' );
	}

	// Signature check: every real map has [Basic], plus either the readable map
	// header or the packed terrain.
	$has_basic = (bool) preg_match( '/^\s*\[Basic\]/mi', $ini );
	$has_body  = (bool) preg_match( '/^\s*\[(Map|IsoMapPack5)\]/mi', $ini );

	if ( ! $has_basic || ! $has_body ) {
		byrm_tweak_fail( 'notmap' );
	}

	$result = byrm_tweak_apply( $ini, $chosen );

	if ( 0 === $result['changes'] ) {
		byrm_tweak_fail( 'nochange' );
	}

	$lines = preg_split( "/\r\n|\n|\r/", $result['ini'] );
	byrm_tweak_rename( $lines );
	$out = implode( byrm_ini_eol( $result['ini'] ), $lines );

	// Count it only now that it has actually worked.
	byrm_tweak_rate_ok( true );

	$base = (string) pathinfo( $name, PATHINFO_FILENAME );
	$base = '' !== $base ? $base : 'map';

	// Dots anywhere but the extension are asking for trouble: a map called
	// "Outpost 1.1 (bankofyrmaps.com)" sanitises to a name with two of them in
	// the middle, and an old game scanning for a file type can read the
	// extension as everything after the first dot. Flatten them, then collapse
	// the runs of dashes that stripping brackets and dots leaves behind.
	$base = str_replace( '.', '-', $base );
	$base = trim( (string) preg_replace( '/-+/', '-', $base ), '-' );
	$base = '' !== $base ? $base : 'map';

	// Same extension it arrived as. Upload a .yrm and a .yrm comes back; the
	// tool's job is to edit the map, not to decide what you call it.
	$filename = sanitize_file_name( $base . '-tweaked.' . $extension );

	nocache_headers();
	header( 'Content-Type: application/octet-stream' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	header( 'Content-Length: ' . strlen( $out ) );
	header( 'X-Content-Type-Options: nosniff' );

	echo $out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- a file download, not HTML.
	exit;
}
add_action( 'template_redirect', 'byrm_tweak_handle', 5 );
