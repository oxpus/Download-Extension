<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/**
 * Language pack for Extension permissions [German]
 */

/**
 * DO NOT CHANGE
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

// Download Extension Permissions
$lang = array_merge($lang, [
	'ACP_DOWNLOADS'			=> 'Download Bereich',

	'ACL_A_DL_OVERVIEW'		=> 'Kann die Startseite ansehen',
	'ACL_A_DL_CONFIG'		=> 'Kann die allgemeinen Einstellungen verwalten',
	'ACL_A_DL_TRAFFIC'		=> 'Kann den Traffic verwalten',
	'ACL_A_DL_CATEGORIES'	=> 'Kann die Kategorien verwalten',
	'ACL_A_DL_FILES'		=> 'Kann die Downloads verwalten',
	'ACL_A_DL_PERMISSIONS'	=> 'Kann die Berechtigungen verwalten',
	'ACL_A_DL_STATS'		=> 'Kann die Statistiken einsehen und verwalten',
	'ACL_A_DL_BLACKLIST'	=> 'Kann die Blackliste der Dateiendungen verwalten',
	'ACL_A_DL_TOOLBOX'		=> 'Kann die Toolbox verwenden',
	'ACL_A_DL_FIELDS'		=> 'Kann benutzerdefinierte Felder verwalten',
	'ACL_A_DL_PERM_CHECK'	=> 'Kann Benutzerberechtigungen überprüfen',
	'ACL_A_DL_ASSISTANT'	=> 'Kann den Einrichtungsassistenten ausführen',
]);
