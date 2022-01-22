<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
	$lang = [];
}

// Download Extension Permissions
$lang = array_merge($lang, [
	'ACP_DOWNLOADS'			=> 'Download Panel',

	'ACL_A_DL_OVERVIEW'		=> 'Can see the start screen',
	'ACL_A_DL_CONFIG'		=> 'Can manage general settings',
	'ACL_A_DL_TRAFFIC'		=> 'Can manage traffic',
	'ACL_A_DL_CATEGORIES'	=> 'Can manage categories',
	'ACL_A_DL_FILES'		=> 'Can manage downloads',
	'ACL_A_DL_PERMISSIONS'	=> 'Can manage permissions',
	'ACL_A_DL_STATS'		=> 'Can view and manage statistics',
	'ACL_A_DL_BLACKLIST'	=> 'Can manage the file extension blacklist',
	'ACL_A_DL_TOOLBOX'		=> 'Can use the toolbox',
	'ACL_A_DL_FIELDS'		=> 'Can manage user defined fields',
	'ACL_A_DL_PERM_CHECK'	=> 'Can check user permissions',
	'ACL_A_DL_ASSISTANT'	=> 'Can run the setup wizard',
]);
