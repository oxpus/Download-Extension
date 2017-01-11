<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* Language pack for Extension permissions [English]
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

// Adding new category
$lang = array_merge($lang, array(
));

// Download MOD Permissions
$lang = array_merge($lang, array(
	'ACP_DOWNLOADS'			=> 'Download Panel',

	'ACL_A_DL_OVERVIEW'		=> 'Can see the start screen',
	'ACL_A_DL_CONFIG'		=> 'Can manage the general settings',
	'ACL_A_DL_TRAFFIC'		=> 'Can manage the traffic',
	'ACL_A_DL_CATEGORIES'	=> 'Can manage the categories',
	'ACL_A_DL_FILES'		=> 'Can manage the downloads',
	'ACL_A_DL_PERMISSIONS'	=> 'Can manage the permissions',
	'ACL_A_DL_STATS'		=> 'Can view and manage the statistics',
	'ACL_A_DL_BANLIST'		=> 'Can manage the banlist',
	'ACL_A_DL_BLACKLIST'	=> 'Can manage the file extension blacklist',
	'ACL_A_DL_TOOLBOX'		=> 'Can use the toolbox',
	'ACL_A_DL_FIELDS'		=> 'Can manage user defined fields',
	'ACL_A_DL_BROWSER'		=> 'Can manage user agents',
));
