<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/*
* connect to phpBB
*/
if ( !defined('IN_PHPBB') )
{
	exit;
}

define('DL_AUTH_TABLE',			$table_prefix . 'dl_auth');
define('DL_BANLIST_TABLE',		$table_prefix . 'dl_banlist');
define('DL_BUGS_TABLE',			$table_prefix . 'dl_bug_tracker');
define('DL_BUG_HISTORY_TABLE',	$table_prefix . 'dl_bug_history');
define('DL_CAT_TABLE',			$table_prefix . 'downloads_cat');
define('DL_CAT_TRAF_TABLE',		$table_prefix . 'dl_cat_traf');
define('DL_COMMENTS_TABLE',		$table_prefix . 'dl_comments');
define('DL_EXT_BLACKLIST',		$table_prefix . 'dl_ext_blacklist');
define('DL_FAVORITES_TABLE',	$table_prefix . 'dl_favorites');
define('DL_FIELDS_DATA_TABLE',	$table_prefix . 'dl_fields_data');
define('DL_FIELDS_LANG_TABLE',	$table_prefix . 'dl_fields_lang');
define('DL_FIELDS_TABLE',		$table_prefix . 'dl_fields');
define('DL_HOTLINK_TABLE',		$table_prefix . 'dl_hotlink');
define('DL_IMAGES_TABLE',		$table_prefix . 'dl_images');
define('DL_LANG_TABLE',			$table_prefix . 'dl_lang');
define('DL_NOTRAF_TABLE',		$table_prefix . 'dl_notraf');
define('DL_RATING_TABLE',		$table_prefix . 'dl_ratings');
define('DL_REM_TRAF_TABLE',		$table_prefix . 'dl_rem_traf');
define('DL_STATS_TABLE',		$table_prefix . 'dl_stats');
define('DL_VERSIONS_TABLE',		$table_prefix . 'dl_versions');
define('DL_VER_FILES_TABLE',	$table_prefix . 'dl_ver_files');
define('DOWNLOADS_TABLE',		$table_prefix . 'downloads');
