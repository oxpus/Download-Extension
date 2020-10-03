<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/


namespace oxpus\dlext\controller\classes;

use Symfony\Component\DependencyInjection\Container;

class dlext_physical implements dlext_physical_interface
{
	/* @var \phpbb\db\driver\driver_interface */
	protected $db;

	protected $ext_path;
	protected $phpbb_container;

	protected $dlext_files;
	protected $dlext_format;

	/**
	* Constructor
	*
	* @param Container 								$phpbb_container
	* @param \phpbb\extension\manager				$phpbb_extension_manager

	* @param \phpbb\db\driver\driver_interfacer		$db
	*/
	public function __construct(
		Container $phpbb_container,
		\phpbb\extension\manager $phpbb_extension_manager,

		\phpbb\db\driver\driver_interface $db,
		$dlext_files,
		$dlext_format
		)
	{
		$this->db 				= $db;
		$this->phpbb_container 	= $phpbb_container;

		$this->ext_path			= $phpbb_extension_manager->get_extension_path('oxpus/dlext', true);

		$this->dlext_files		= $dlext_files;
		$this->dlext_format		= $dlext_format;
	}

	public function read_exist_files()
	{
		$dl_files = $this->dlext_files->all_files(0, '', '', '', 0, 1, 'real_file');

		$exist_files = [];

		for ($i = 0; $i < count($dl_files); ++$i)
		{
			$exist_files[] = $dl_files[$i]['real_file'];
		}

		$sql = 'SELECT ver_real_file
				FROM ' . DL_VERSIONS_TABLE;
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$exist_files[] = $row['ver_real_file'];
		}

		$this->db->sql_freeresult($result);

		return $exist_files;
	}

	public function read_dl_dirs($path = '', $cur = '')
	{
		$language = $this->phpbb_container->get('language');

		$folders = '';

		$download_dir = DL_EXT_FILEBASE_PATH . 'downloads/';

		$dl_dir = substr($download_dir, 0, strlen($download_dir)-1);

		@$dir = opendir($download_dir . $path);

		while (false !== ($file=@readdir($dir)))
		{
			if ($file{0} != ".")
			{
				if(is_dir($download_dir . $path . '/' . $file))
				{
					$temp_dir = $dl_dir . $path . '/' . $file;
					$folders .= ('/' . $cur != $path . '/' . $file) ? '<option value="' . $dl_dir . $path . '/' . $file . '/">' . $language->lang('DL_MOVE') . ' » ' . $temp_dir . '/</option>' : '';
					$folders .= $this->read_dl_dirs($path . '/' . $file, $cur);
				}
			}
		}

		closedir($dir);

		return $folders;
	}

	public function read_dl_files($path = '', $unas_files = [])
	{
		$files = '';

		$download_dir = DL_EXT_FILEBASE_PATH . 'downloads/';

		$dl_dir = ($path) ? $download_dir : substr($download_dir, 0, strlen($download_dir)-1);

		@$dir = opendir($dl_dir . $path);

		while (false !== ($file=@readdir($dir)))
		{
			if ($file{0} != ".")
			{
				$files .= (in_array($file, $unas_files)) ? $path . '/' . $file . '|' : '';
				$files .= $this->read_dl_files($download_dir, $path . '/' . $file, $unas_files);
			}
		}

		@closedir($dir);

		return $files;
	}

	public function read_dl_sizes($download_dir = '')
	{
		$file_size = 0;

		if (!$download_dir)
		{
			$download_dir = DL_EXT_FILEBASE_PATH . 'downloads/';
		}

		$dirs = array_diff(scandir($download_dir), ['.', '..']);
		$dir_array = [];

		foreach($dirs as $d)
		{
			if (is_dir($download_dir . '/' . $d))
			{
				$file_size += $this->read_dl_sizes($download_dir . '/' . $d);
			}
			else
			{
				$file_size += sprintf("%u", @filesize($download_dir . '/' . $d));
			}
		}

		return $file_size;
	}

	public function dl_max_upload_size()
	{
		$post_max	= ini_get('post_max_size');
		$upload_max	= ini_get('upload_max_filesize');

		$post_max_unit		= substr($post_max, -1, 1);
		$upload_max_unit	= substr($upload_max, -1, 1);

		$post_max_value		= intval(substr($post_max, 0, strlen($post_max) - 1));
		$upload_max_value	= intval(substr($upload_max, 0, strlen($upload_max) - 1));

		$unit_factor		= ['K' => 1024, 'M' => 1024*1024, 'G' => 1024*1024*1024];

		$post_max_size		= $post_max_value * $unit_factor[$post_max_unit];
		$upload_max_size	= $upload_max_value * $unit_factor[$upload_max_unit];

		$max_upload_size = min($post_max_size, $upload_max_size);

		return $this->dlext_format->dl_size($max_upload_size, 0, 'combine');
	}

	public function _create_folder($path)
	{
		if (@file_exists($path))
		{
			return;
		}

		@mkdir($path);
		@phpbb_chmod($path, CHMOD_ALL);

		$f = fopen($path . '/index.htm', 'w');
		fclose($f);
	}

	public function check_folders()
	{
		if (!defined('DL_EXT_FILEBASE_PATH'))
		{
			return;
		}

		if(!@file_exists(DL_EXT_CACHE_PATH))
		{
			$this->_create_folder(DL_EXT_CACHE_PATH);
		}

		if(!@file_exists(DL_EXT_FILEBASE_PATH))
		{
			$this->_create_folder(DL_EXT_FILEBASE_PATH);
			$this->_create_folder(DL_EXT_FILEBASE_PATH . 'thumbs/');
			$this->_create_folder(DL_EXT_FILEBASE_PATH . 'thumbs/');
			$this->_create_folder(DL_EXT_FILEBASE_PATH . 'downloads/');
			$this->_create_folder(DL_EXT_FILEBASE_PATH . 'version/');
			$this->_create_folder(DL_EXT_FILEBASE_PATH . 'version/files/');
			$this->_create_folder(DL_EXT_FILEBASE_PATH . 'version/images/');
		}

		if(!@file_exists(DL_EXT_FILEBASE_PATH . 'thumbs/'))
		{
			$this->_create_folder(DL_EXT_FILEBASE_PATH . 'thumbs/');
		}

		if(!@file_exists(DL_EXT_FILEBASE_PATH . 'downloads/'))
		{
			$this->_create_folder(DL_EXT_FILEBASE_PATH . 'downloads/');
		}

		if(!@file_exists(DL_EXT_FILEBASE_PATH . 'version/'))
		{
			$this->_create_folder(DL_EXT_FILEBASE_PATH . 'version/');
			$this->_create_folder(DL_EXT_FILEBASE_PATH . 'version/files/');
			$this->_create_folder(DL_EXT_FILEBASE_PATH . 'version/images/');
		}

		if(!@file_exists(DL_EXT_FILEBASE_PATH . 'version/files/'))
		{
			$this->_create_folder(DL_EXT_FILEBASE_PATH . 'version/files/');
		}

		if(!@file_exists(DL_EXT_FILEBASE_PATH . 'version/images/'))
		{
			$this->_create_folder(DL_EXT_FILEBASE_PATH . 'version/images/');
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
			reset($objects);
			@rmdir($source_path);
			if (@file_exists($source_path))
			{
				return false;
			}
		}

		return true;
	}

	/*
	* Read the existing downloads folder tree to select a path within the categories management
	* instead enter the complete path "old school" manually.
	*/
	public function get_file_base_tree($file_base, $path, $level = 0)
	{
		$tree = [];
		if (substr($file_base, -1, 1) != '/')
		{
			$file_base .= '/';
		}

		$handle = @opendir($file_base);

		while (($entry = @readdir($handle)) !== false)
		{
			if (@is_dir($file_base . $entry) && $entry[0] != '.')
			{
				$separator = '';
				for ($i = 0; $i < $level; ++$i)
				{
					$separator .= '&nbsp;&nbsp;&nbsp;-&nbsp;';
				}

				$check_path = substr($path, 0, -1);
				if ($entry == $check_path)
				{
					$selected = ' selected="selected" ';
				}
				else
				{
					$selected = '';
				}

				$cat_path = str_replace(DL_EXT_FILEBASE_PATH . 'downloads/', '', $file_base . $entry . '/');
				$entry_path = str_replace(DL_EXT_FILEBASE_PATH . 'downloads/', '', $file_base);
				$tree[] = [
					'cat_path'	=> $cat_path,
					'selected'	=> $selected,
					'entry'		=> $separator . $entry . '/',
				];

				++$level;
				$tmp_tree = $this->get_file_base_tree($file_base . $entry, $path, $level);
				--$level;

				if (is_array($tmp_tree))
				{
					$tree = array_merge($tree, $tmp_tree);
				}
			}
		}

		@closedir($handle);

		return $tree;
	}
}
