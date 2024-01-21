<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright (c) 2015-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\acp;

/**
 * @package acp
 */
class main_info
{
	public function module()
	{
		return [
			'filename'	=> '\oxpus\dlext\acp\main_module',
			'title'		=> 'ACP_DOWNLOADS',
			'modes'		=> [
				'overview'		=> [
					'title' => 'DL_ACP_OVERVIEW',				'auth' => 'ext_oxpus/dlext && acl_a_dl_overview',		'cat' => ['ACP_DOWNLOADS']
				],
				'config'		=> [
					'title' => 'DL_ACP_CONFIG_MANAGEMENT',		'auth' => 'ext_oxpus/dlext && acl_a_dl_config',			'cat' => ['ACP_DOWNLOADS']
				],
				'traffic'		=> [
					'title' => 'DL_ACP_TRAFFIC_MANAGEMENT',		'auth' => 'ext_oxpus/dlext && acl_a_dl_traffic',		'cat' => ['ACP_DOWNLOADS']
				],
				'categories'	=> [
					'title' => 'DL_ACP_CATEGORIES_MANAGEMENT',	'auth' => 'ext_oxpus/dlext && acl_a_dl_categories',		'cat' => ['ACP_DOWNLOADS']
				],
				'files'			=> [
					'title' => 'DL_ACP_FILES_MANAGEMENT',		'auth' => 'ext_oxpus/dlext && acl_a_dl_files',			'cat' => ['ACP_DOWNLOADS']
				],
				'permissions'	=> [
					'title' => 'DL_ACP_PERMISSIONS',			'auth' => 'ext_oxpus/dlext && acl_a_dl_permissions',	'cat' => ['ACP_DOWNLOADS']
				],
				'stats'			=> [
					'title' => 'DL_ACP_STATS_MANAGEMENT',		'auth' => 'ext_oxpus/dlext && acl_a_dl_stats',			'cat' => ['ACP_DOWNLOADS']
				],
				'ext_blacklist'	=> [
					'title' => 'DL_EXT_BLACKLIST',				'auth' => 'ext_oxpus/dlext && acl_a_dl_blacklist',		'cat' => ['ACP_DOWNLOADS']
				],
				'toolbox'		=> [
					'title' => 'DL_MANAGE',						'auth' => 'ext_oxpus/dlext && acl_a_dl_toolbox',		'cat' => ['ACP_DOWNLOADS']
				],
				'fields'		=> [
					'title' => 'DL_ACP_FIELDS',					'auth' => 'ext_oxpus/dlext && acl_a_dl_fields',			'cat' => ['ACP_DOWNLOADS']
				],
				'perm_check'	=> [
					'title' => 'DL_ACP_PERM_CHECK',				'auth' => 'ext_oxpus/dlext && acl_a_dl_perm_check',		'cat' => ['ACP_DOWNLOADS']
				],
				'assistant'	=> [
					'title' => 'DL_ACP_ASSISTANT',				'auth' => 'ext_oxpus/dlext && acl_a_dl_assistant',		'cat' => ['ACP_DOWNLOADS']
				],
			],
		];
	}
}
