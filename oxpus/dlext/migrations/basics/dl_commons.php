<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\migrations\basics;

use phpbb\db\tools\tools;

class dl_commons extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['dl_cat_edit']);
	}

	static public function depends_on()
	{
		return array('\oxpus\dlext\migrations\basics\dl_perms');
	}

	public function update_data()
	{
		return array(
			// At least run some foreign routines
			array('custom', array(array($this, 'add_fulltext_index'))),
			array('custom', array(array($this, 'add_default_blacklist_extentions'))),
			array('custom', array(array($this, 'first_reset_remain_traffic'))),

			// Preset the config data
			array('config.add', array('dl_cat_edit', '1')),
		);
	}

	public function add_fulltext_index()
	{
		global $phpbb_container;

		if ($phpbb_container->get('request')->variable('action', '') == 'delete_data')
		{
			return;
		}

		$tools = $phpbb_container->get('dbal.tools');

		$check_index = $tools->sql_index_exists($this->table_prefix . 'downloads', 'desc_search');

		if ($check_index)
		{
			return;
		}

		global $dbms;

		switch ($dbms)
		{
			case 'postgres':
			case 'phpbb\db\driver\postgres':
			case 'phpbb\\db\\driver\\postgres':
			case 'oracle':
			case 'phpbb\db\driver\oracle':
			case 'phpbb\\db\\driver\\oracle':
			case 'sqlite':
			case 'phpbb\db\driver\sqlite':
			case 'phpbb\\db\\driver\\sqlite':
			case 'sqlite3':
			case 'phpbb\db\driver\sqlite3':
			case 'phpbb\\db\\driver\\sqlite3':
				$statement = 'CREATE FULLTEXT INDEX desc_search ON ' . $this->table_prefix . 'downloads(desc_search)';
			break;

			case 'mysql':
			case 'phpbb\db\driver\mysql':
			case 'phpbb\\db\\driver\\mysql':
			case 'mysqli':
			case 'phpbb\db\driver\mysqli':
			case 'phpbb\\db\\driver\\mysqli':
				$sql = 'ALTER TABLE ' . $this->table_prefix . 'downloads ENGINE = MyISAM';
				$this->db->sql_query($sql);

				$sql = 'ALTER TABLE ' . $this->table_prefix . 'downloads CHANGE COLUMN description description MEDIUMTEXT NOT NULL';
				$this->db->sql_query($sql);

				$statement = 'ALTER TABLE ' . $this->table_prefix . 'downloads ADD FULLTEXT INDEX desc_search(description)';
			break;

			case 'mssql':
			case 'phpbb\db\driver\mssql':
			case 'phpbb\\db\\driver\\mssql':
			case 'mssql_odbc':
			case 'phpbb\db\driver\mssql_odbc':
			case 'phpbb\\db\\driver\\mssql_odbc':
			case 'mssqlnative':
			case 'phpbb\db\driver\mssqlnative':
			case 'phpbb\\db\\driver\\mssqlnative':
				$statement = 'CREATE FULLTEXT INDEX desc_search ON ' . $this->table_prefix . 'downloads(description) ON [PRIMARY]';
			break;
		}

		$this->db->sql_query($statement);
	}

	public function add_default_blacklist_extentions()
	{
		global $phpbb_container;

		if ($phpbb_container->get('request')->variable('action', '') == 'delete_data')
		{
			return;
		}

		$sql_insert = array(
			array('extention'	=> 'asp'),
			array('extention'	=> 'cgi'),
			array('extention'	=> 'dhtm'),
			array('extention'	=> 'dhtml'),
			array('extention'	=> 'exe'),
			array('extention'	=> 'htm'),
			array('extention'	=> 'html'),
			array('extention'	=> 'jar'),
			array('extention'	=> 'js'),
			array('extention'	=> 'php'),
			array('extention'	=> 'php3'),
			array('extention'	=> 'pl'),
			array('extention'	=> 'sh'),
			array('extention'	=> 'shtm'),
			array('extention'	=> 'shtml'),
		);

		$this->db->sql_multi_insert($this->table_prefix . 'dl_ext_blacklist', $sql_insert);
	}

	public function first_reset_remain_traffic()
	{
		global $phpbb_container;

		if ($phpbb_container->get('request')->variable('action', '') == 'delete_data')
		{
			return;
		}

		$sql_insert = array(
			array('config_name' => 'dl_remain_guest_traffic', 'config_value' => '0'),
			array('config_name' => 'dl_remain_traffic', 'config_value' => '0'),
		);

		$this->db->sql_return_on_error(true);
		$this->db->sql_multi_insert($this->table_prefix . 'dl_rem_traf', $sql_insert);
		$this->db->sql_return_on_error(false);
	}
}
