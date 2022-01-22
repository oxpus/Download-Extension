<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/**
 * Language pack for Extension permissions [French]
 * French translation by phpBB-fr.com (Darky et EnYgma), panteror (http://www.dowfrance.com) & Galixte (http://www.galixte.com)
 * Correction by TazDevilBZH
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
	'ACP_DOWNLOADS'			=> 'Page des téléchargements',

	'ACL_A_DL_OVERVIEW'		=> 'Peut voir la page d’accueil des téléchargements.',
	'ACL_A_DL_CONFIG'		=> 'Peut gérer les paramètres généraux.',
	'ACL_A_DL_TRAFFIC'		=> 'Peut gérer le trafic.',
	'ACL_A_DL_CATEGORIES'	=> 'Peut gérer les catégories.',
	'ACL_A_DL_FILES'		=> 'Peut gérer les téléchargements.',
	'ACL_A_DL_PERMISSIONS'	=> 'Peut gérer les permissions.',
	'ACL_A_DL_STATS'		=> 'Peut voir et gérer les statistiques.',
	'ACL_A_DL_BLACKLIST'	=> 'Peut gérer la liste noire des extensions de fichier.',
	'ACL_A_DL_TOOLBOX'		=> 'Peut utiliser la boite à outils.',
	'ACL_A_DL_FIELDS'		=> 'Peut gérer les champs définis par l’utilisateur.',
	'ACL_A_DL_PERM_CHECK'	=> 'Peut vérifier les permissions utilisateurs.',
	'ACL_A_DL_ASSISTANT'	=> 'Peut exécuter l’assistant de configuration',
]);
