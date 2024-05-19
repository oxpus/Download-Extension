<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2024 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/*
* [ english ] language file for Download Extension
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

$lang = array_merge($lang, [
	'DL_LIMIT_TITLE_SHOW'				=> 'View current limits',
	'DL_LIMIT_TITLE_HIDE'				=> 'Set limits',
	'DL_PHP_LIMITS'						=> 'Settings in PHP',
	'DL_CUR_LIMITS'						=> 'Settings within the download extension',

	'DL_PHP_INI_EXPLAIN'				=> 'The PHP settings can be changed in the file <strong>%1$s</strong> or in one of the included configuration files; see PHP-information.',

	'DL_LIMIT_PHP_FILE_UPLOAD'			=> 'file_upload',
	'DL_LIMIT_PHP_MAX_FILE_UPLOAD'		=> 'max_file_upload',
	'DL_LIMIT_PHP_MAX_INPUT_TIME'		=> 'max_input_time',
	'DL_LIMIT_PHP_MAX_EXECUTION_TIME'	=> 'max_execution_time',
	'DL_LIMIT_PHP_MEMORY_LIMIT'			=> 'memory_limit',
	'DL_LIMIT_PHP_POST_MAX_SIZE'		=> 'post_max_size',
	'DL_LIMIT_PHP_UPLOAD_MAX_FILESIZE'	=> 'upload_max_filesize',

	'DL_LIMIT_TOTAL_REMAIN'				=> 'Remaining memory for all download files',
	'DL_LIMIT_THUMBNAIL_XY_SIZE'		=> 'Maximum dimensions of uploaded thumbnails',
	'DL_LIMIT_THUMBNAIL_XYSIZE'			=> '%1$s x %2$s pixel',

	'DL_LIMIT_PHP_FILE_UPLOAD_EXPLAIN'			=> 'Default = 1 (On)<br />Allows PHP to process uploaded files.<br />Otherwise, these files will not be available for PHP.',
	'DL_LIMIT_PHP_MAX_FILE_UPLOAD_EXPLAIN'		=> 'Default = 20, recommendation >= 10<br />Limits the number of simultaneously uploaded files that PHP can process.',
	'DL_LIMIT_PHP_MAX_INPUT_TIME_EXPLAIN'		=> 'Default = -1 (not active)<br />Maximum processing time for POST and GET data in seconds.<br />The time period begins with the start of PHP and ends with the start of the first PHP script.',
	'DL_LIMIT_PHP_MAX_EXECUTION_TIME_EXPLAIN'	=> 'Default = 30 seconds<br />Maximum execution time of a PHP script from the start of PHP to the end of a script to be executed.<br />After this period of time, PHP stops processing unless the script was ended prematurely.',
	'DL_LIMIT_PHP_MEMORY_LIMIT_EXPLAIN'			=> 'Default = 128 MB (in modern PHP versions)<br />Limits the server’s RAM, which PHP is allowed to use.<br />Should be increased to match the download files you uses.<br />It is strongly recommended to not exceed the server’s RAM limit.',
	'DL_LIMIT_PHP_POST_MAX_SIZE_EXPLAIN'		=> 'Default = 8 MB<br />Maximum memory consumption for an HTTP(S) upload stream / HTML form.<br />Limited by the value under memory_limit.<br />Should be increased when larger files should be available for downloads.',
	'DL_LIMIT_PHP_UPLOAD_MAX_FILESIZE_EXPLAIN'	=> 'Default = 2 MB<br />Maximum file size that PHP is allowed to process per file after submitting an HTML form.<br />Larger files will not be available for PHP.<br />Should be increased if larger files are used for downloads.<br /><strong>Attention:</strong><br />This limit counts per uploaded file. If several files are to be uploaded together, the limit per file is multiplied and overall limited by the value of post_max_size.',

	'DL_LIMIT_TRAFFIC_USER_REMAIN_EXPLAIN'		=> 'The download traffic currently available to all registered users in the current month.',
	'DL_LIMIT_TRAFFIC_GUESTS_REMAIN_EXPLAIN'	=> 'The download traffic currently available to all guests in the current month.',
	'DL_LIMIT_TOTAL_REMAIN_EXPLAIN'				=> 'The maximum storage space provided for all files that are to be offered for download.<br /><strong>Important:</strong><br />Thumbnails and file versions are excluded from this limit!<br /><strong> Attention:</strong><br />This limit must not reach or even exceed the physically available storage space of the server, otherwise the server could fail due to lack of memory!<br />It is also important to ensure that the directory sizes of the thumbnails and file versions must be included for this physical limit.',
	'DL_LIMIT_THUMBNAIL_SIZE_EXPLAIN'			=> 'Uploaded thumbnails with a larger file size will be rejected and not included in the downloads.',
	'DL_LIMIT_THUMBNAIL_XY_SIZE_EXPLAIN'		=> 'Maximum dimensions in pixels for the width and height of all uploaded thumbnail files.<br />Larger image files will be rejected and not accepted.',
	'DL_LIMIT_THUMBNAIL_XYSIZE_EXPLAIN'			=> '%1$s x %2$s Pixel',
]);
