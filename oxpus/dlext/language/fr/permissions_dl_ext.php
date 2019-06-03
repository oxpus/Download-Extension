<?php
/**
 *
 * Hotschi's Downloads. An extension for the phpBB Forum Software package.
 * French translation by phpBB-fr.com (Darky et EnYgma), panteror (http://www.dowfrance.com) & Galixte (http://www.galixte.com)
 *
 * @copyright (c) 2014-2019 OXPUS <https://www.oxpus.net>
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 *
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
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ « » “ ” …
//

// Download MOD Permissions
$lang = array_merge($lang, array(
	'ACP_DOWNLOADS'			=> 'Page des téléchargements',

	'ACL_A_DL_OVERVIEW'		=> 'Peut voir la page d’accueil des téléchargements.',
	'ACL_A_DL_CONFIG'		=> 'Peut gérer les paramètres généraux.',
	'ACL_A_DL_TRAFFIC'		=> 'Peut gérer le trafic.',
	'ACL_A_DL_CATEGORIES'	=> 'Peut gérer les catégories.',
	'ACL_A_DL_FILES'		=> 'Peut gérer les téléchargements.',
	'ACL_A_DL_PERMISSIONS'	=> 'Peut gérer les permissions.',
	'ACL_A_DL_STATS'		=> 'Peut voir et gérer les statistiques.',
	'ACL_A_DL_BANLIST'		=> 'Peut gérer les bannissements.',
	'ACL_A_DL_BLACKLIST'	=> 'Peut gérer la liste noire des extensions de fichier.',
	'ACL_A_DL_TOOLBOX'		=> 'Peut utiliser la boite à outils.',
	'ACL_A_DL_FIELDS'		=> 'Peut gérer les champs définis par l’utilisateur.',
	'ACL_A_DL_BROWSER'		=> 'Peut gérer les « user agents ».',
	'ACL_A_DL_PERM_CHECK'	=> 'Peut vérifier les permissions utilisateur.',
));
