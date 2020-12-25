<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\migrations\v800;

class prepare_8_0_0 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['dl_remain_traffic']);
	}

	static public function depends_on()
	{
		return ['\oxpus\dlext\migrations\v730\release_7_3_5'];
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'move_remain_traffic']]],
			['custom', [[$this, 'move_download_files']]],

			['config.add', ['dl_remain_guest_traffic', '0']],
			['config.add', ['dl_remain_traffic', '0']],
			['config.add', ['dl_enable_blacklist', '0']],

			['config.remove', ['dl_download_dir']],
		];
	}

	public function move_remain_traffic()
	{
		$this->db->sql_return_on_error(true);

		$sql = 'SELECT * FROM ' . $this->table_prefix . 'dl_rem_traf';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->config->set($row['config_name'], $row['config_value']);
		}
		$this->db->sql_freeresult($result);

		$this->db->sql_return_on_error(false);
	}

	public function move_download_files()
	{
		global $phpbb_container;

		if ($phpbb_container->get('request')->variable('action', '') == 'delete_data')
		{
			return;
		}

		if (!isset($this->config['dl_download_dir']))
		{
			return;
		}

		if ($this->config['dl_download_dir'] == 1)
		{
			global $phpbb_extension_manager;

			$ext_path = $phpbb_extension_manager->get_extension_path('oxpus/dlext', true);

			$folder_base = $ext_path . 'files/';
			$folder_drop = $folder_base;
		}
		else
		{
			$folder_base = $this->phpbb_root_path . 'store/oxpus/dlext/';
			$folder_drop = $this->phpbb_root_path . 'store/oxpus/';
		}

		if (!@file_exists($folder_base))
		{
			return;
		}

		$this->check_folders();

		$return = $this->_move_dl_basis($folder_base, $this->phpbb_root_path . $this->config['upload_path'] . '/dlext/');
		$dropped = $this->_drop_dl_basis($folder_drop);
		$dropped = $this->_drop_dl_basis($this->phpbb_root_path . $this->config['upload_path'] . '/dlext/cache/');

		return;
	}

	public function _move_dl_basis($source_path, $dest_path)
	{
		if (substr($source_path, -1) == '/')
		{
			$source_path = substr($source_path, 0, -1);
		}

		if (substr($dest_path, -1) == '/')
		{
			$dest_path = substr($dest_path, 0, -1);
			$this->_create_folder($dest_path);

			if (!@is_dir($dest_path))
			{
				return false;
			}
		}

		$objects = scandir($source_path);

		foreach ($objects as $object)
		{
			if ($object != '.' && $object != '..')
			{
				if (is_dir($source_path . '/' . $object))
				{
					$success = $this->_move_dl_basis($source_path . '/' . $object, $dest_path . '/' . $object . '/');

					if (!$success)
					{
						return false;
					}
				}
				else
				{
					@copy($source_path . '/' . $object, $dest_path . '/' . $object);
					@phpbb_chmod($dest_path . '/' . $object, CHMOD_ALL);

					if (!file_exists($dest_path . '/' . $object))
					{
						return false;
					}
				}
			}
		}

		return true;
	}

	public function _create_folder($path)
	{
		if (@file_exists($path))
		{
			return;
		}

		@mkdir($path);
		@phpbb_chmod($path, CHMOD_ALL);

		$f = fopen($path . 'index.htm', 'w');
		fclose($f);
	}

	public function check_folders()
	{
		// define extension folder constants
		$dl_ext_cache_path = $this->phpbb_root_path . 'cache/' . PHPBB_ENVIRONMENT . '/dlext/';
		$dl_ext_filebase_path = $this->phpbb_root_path . $this->config['upload_path'] . '/dlext/';

		if (!@file_exists($dl_ext_cache_path))
		{
			$this->_create_folder($dl_ext_cache_path);
		}

		if (!@file_exists($dl_ext_filebase_path))
		{
			$this->_create_folder($dl_ext_filebase_path);
			$this->_create_folder($dl_ext_filebase_path . 'thumbs/');
			$this->_create_folder($dl_ext_filebase_path . 'thumbs/');
			$this->_create_folder($dl_ext_filebase_path . 'downloads/');
			$this->_create_folder($dl_ext_filebase_path . 'version/');
			$this->_create_folder($dl_ext_filebase_path . 'version/files/');
			$this->_create_folder($dl_ext_filebase_path . 'version/images/');
		}

		if (!@file_exists($dl_ext_filebase_path . 'thumbs/'))
		{
			$this->_create_folder($dl_ext_filebase_path . 'thumbs/');
		}

		if (!@file_exists($dl_ext_filebase_path . 'downloads/'))
		{
			$this->_create_folder($dl_ext_filebase_path . 'downloads/');
		}

		if (!@file_exists($dl_ext_filebase_path . 'version/'))
		{
			$this->_create_folder($dl_ext_filebase_path . 'version/');
			$this->_create_folder($dl_ext_filebase_path . 'version/files/');
			$this->_create_folder($dl_ext_filebase_path . 'version/images/');
		}

		if (!@file_exists($dl_ext_filebase_path . 'version/files/'))
		{
			$this->_create_folder($dl_ext_filebase_path . 'version/files/');
		}

		if (!@file_exists($dl_ext_filebase_path . 'version/images/'))
		{
			$this->_create_folder($dl_ext_filebase_path . 'version/images/');
		}
	}

	public function _drop_dl_basis($source_path)
	{
		if (substr($source_path, -1) == '/')
		{
			$source_path = substr($source_path, 0, -1);
		}

		if (is_dir($source_path))
		{
			$objects = scandir($source_path);
			foreach ($objects as $object)
			{
				if ($object != '.' && $object != '..')
				{
					if (@is_dir($source_path . '/' . $object))
					{
						$success = $this->_drop_dl_basis($source_path . '/' . $object);
					}
					else
					{
						@unlink($source_path . '/' . $object);
					}
				}
			}

			@rmdir($source_path);

			if (@file_exists($source_path))
			{
				return false;
			}
		}

		return true;
	}
}
