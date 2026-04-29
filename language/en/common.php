<?php
/**
 *
 * Multiavatar extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Vinny
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'ALLOW_MULTIAVATAR' => 'Allow Multiavatar avatars',
	'ALLOW_MULTIAVATAR_EXPLAIN' => 'Allows users to generate deterministic SVG avatars with Multiavatar.',

	'AVATAR_DRIVER_MULTIAVATAR_TITLE' => 'Multiavatar',
	'AVATAR_DRIVER_MULTIAVATAR_EXPLAIN' => 'Generate a deterministic avatar from a seed.',

	'MULTIAVATAR_SEED' => 'Seed',
	'MULTIAVATAR_SEED_EXPLAIN' => 'This seed identifies the generated avatar. Use the button to generate another avatar.',
	'MULTIAVATAR_GENERATE' => 'Generate another',
	'MULTIAVATAR_PREVIEW' => 'Preview',

	'MULTIAVATAR_LIBRARY_MISSING' => 'The Multiavatar PHP library is not installed. Run composer install in ext/vinny/multiavatar.',
	'MULTIAVATAR_SEED_TOO_LONG' => 'The Multiavatar seed must be %d characters or fewer.',
	'MULTIAVATAR_WRONG_SIZE' => 'The avatar must be at least %1$d pixels wide, %2$d pixels high and at most %3$d pixels wide, %4$d pixels high. The submitted avatar is %5$d pixels wide and %6$d pixels high.',
));
