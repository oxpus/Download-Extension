<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2025 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\migrations\v840;

class release_8_4_0 extends \phpbb\db\migration\migration
{
	protected $dl_ext_version = '8.4.0';

	public function effectively_installed()
	{
		return isset($this->config['dl_ext_version']) && version_compare($this->config['dl_ext_version'], $this->dl_ext_version, '>=');
	}

	public static function depends_on()
	{
		return ['\oxpus\dlext\migrations\v830\release_8_3_0'];
	}

	public function update_schema()
	{
		return [
			'add_tables'	=> [
				$this->table_prefix . 'dl_todo_cat' => [
					'COLUMNS'		=> [
						'todo_cat_id'		=> ['UINT:11', null, 'auto_increment'],
						'todo_cat_sort'		=> ['UINT', 0],
						'todo_catname'		=> ['VCHAR', ''],
						'todo_cat_color'	=> ['VCHAR:10', ''],
					],
					'PRIMARY_KEY'	=> 'todo_cat_id'
				],

				$this->table_prefix . 'dl_todo' => [
					'COLUMNS'		=> [
						'todo_id'			=> ['UINT:11', null, 'auto_increment'],
						'todo_cat_id'		=> ['UINT:11', 0],
						'df_id'				=> ['UINT:11', 0],
						'todo_sort'			=> ['UINT', 0],
						'todo_title'		=> ['VCHAR', ''],
					],
					'PRIMARY_KEY'	=> 'todo_id'
				],
			],

			'add_columns'	=> [
				$this->table_prefix . 'downloads_cat'		=> [
					'cat_status'	=> ['BOOL', 0],
				],
				$this->table_prefix . 'dl_auth'		=> [
					'auth_cpost'	=> ['BOOL', 1],
					'auth_cread'	=> ['BOOL', 0],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_tables' => [
				$this->table_prefix . 'dl_todo_cat',
				$this->table_prefix . 'dl_todo',
			],

			'drop_columns'	=> [
				$this->table_prefix . 'downloads_cat'	=> ['cat_status'],
				$this->table_prefix . 'dl_auth'			=> ['auth_cread', 'auth_cpost'],
			],
		];
	}
}
