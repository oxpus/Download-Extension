<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\phpbb\classes;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class dl_physical extends dl_mod
{
	public static function read_exist_files()
	{
		global $db;

		$dl_files = dl_files::all_files(0, '', '', '', 0, 1, 'real_file');

		$exist_files = array();

		for ($i = 0; $i < sizeof($dl_files); $i++)
		{
			$exist_files[] = $dl_files[$i]['real_file'];
		}

		$sql = 'SELECT ver_real_file FROM ' . DL_VERSIONS_TABLE;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$exist_files[] = $row['ver_real_file'];
		}

		$db->sql_freeresult($result);

		return $exist_files;
	}

	public static function read_dl_dirs($download_dir = '', $path = '')
	{
		global $user, $cur, $unas_files;
		global $phpbb_container;
		$language = $phpbb_container->get('language');

		$folders = '';

		if (!$download_dir)
		{
			$download_dir = DL_EXT_FILES_FOLDER;
			$download_dir = str_replace('//', '/', $download_dir);
		}

		$dl_dir = substr($download_dir, 0, strlen($download_dir)-1);

		@$dir = opendir($download_dir . $path);

		while (false !== ($file=@readdir($dir)))
		{
			if ($file{0} != ".")
			{
				if(is_dir($download_dir . $path . '/' . $file))
				{
					$temp_dir = $dl_dir . $path . '/' . $file;
					$folders .= ('/'.$cur != $path . '/' . $file) ? '<option value="' . $dl_dir . $path . '/' . $file . '/">'.$language->lang('DL_MOVE').' » ' . $temp_dir . '/</option>' : '';
					$folders .= self::read_dl_dirs($download_dir, $path . '/' . $file);
				}
			}
		}

		closedir($dir);

		return $folders;
	}

	public static function read_dl_files($download_dir = '', $path = '', $unas_files = array())
	{
		$files = '';

		if (!$download_dir)
		{
			$download_dir = DL_EXT_FILES_FOLDER;
			$download_dir = str_replace('//', '/', $download_dir);
		}

		$dl_dir = ($path) ? $download_dir : substr($download_dir, 0, strlen($download_dir)-1);

		@$dir = opendir($dl_dir . $path);

		while (false !== ($file=@readdir($dir)))
		{
			if ($file{0} != ".")
			{
				$files .= (in_array($file, $unas_files)) ? $path . '/' . $file . '|' : '';
				$files .= self::read_dl_files($download_dir, $path . '/' . $file, $unas_files);
			}
		}

		@closedir($dir);

		return $files;
	}

	public static function read_dl_sizes($download_dir = '')
	{
		$file_size = 0;

		if (!$download_dir)
		{
			$download_dir = DL_EXT_FILES_FOLDER;
			$download_dir = str_replace('//', '/', $download_dir);
		}

		$dirs = array_diff(scandir($download_dir), array(".", ".."));
		$dir_array = array();

		foreach($dirs as $d)
		{
			if (is_dir($download_dir . '/' . $d))
			{
				$file_size += self::read_dl_sizes($download_dir . '/' . $d);
			}
			else
			{
				$file_size += sprintf("%u", @filesize($download_dir . '/' . $d));
			}
		}

		return $file_size;
	}

	public static function dl_max_upload_size()
	{
		$post_max	= ini_get('post_max_size');
		$upload_max	= ini_get('upload_max_filesize');

		$post_max_unit		= substr($post_max, -1, 1);
		$upload_max_unit	= substr($upload_max, -1, 1);

		$post_max_value		= intval(substr($post_max, 0, strlen($post_max) - 1));
		$upload_max_value	= intval(substr($upload_max, 0, strlen($upload_max) - 1));

		$unit_factor = array('K' => 1024, 'M' => 1024*1024, 'G' => 1024*1024*1024);

		$post_max_size		= $post_max_value * $unit_factor[$post_max_unit];
		$upload_max_size	= $upload_max_value * $unit_factor[$upload_max_unit];

		$max_upload_size = min($post_max_size, $upload_max_size);

		return dl_format::dl_size($max_upload_size, 0, 'combine');
	}

	private static function _move_dl_basis($source_path, $dest_path)
	{
		if (substr($source_path, -1) == '/')
		{
			$source_path = substr($source_path, 0, -1);
		}

		if (substr($dest_path, -1) == '/')
		{
			$dest_path = substr($dest_path, 0, -1);
		}

		if (!@is_dir($dest_path))
		{
			@mkdir($dest_path);
			@chmod($dest_path, 0755);
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
				if (filetype($source_path . '/' . $object) == 'dir')
				{
					$success = self::_move_dl_basis($source_path . '/' . $object, $dest_path . '/' . $object);
					if (!$success)
					{
						return false;
					}
				}
				else
				{
					@copy($source_path . '/' . $object, $dest_path . '/' . $object);
					@chmod($dest_path . '/' . $object, 0777);
					if (!file_exists($dest_path . '/' . $object))
					{
						return false;
					}
				}
			}
		}

		return true;
	}

	private static function _drop_dl_basis($source_path)
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
						$success = self::_drop_dl_basis($source_path . '/' . $object);
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
	* Moves the folder base for all files controlled by the extension from inside to outside
	* the extension folder or backward from outsite into the extension folder.
	* Regard:
	* The normal way is to host all files outside the extension folder, so updating the
	* extension can be done as written in the ACP or within other extensions like the
	* "Upload Extension".
	* This will be used on ACP config module while changing the folder base
	* The folder name will not longer be aviable for edit by an admin and is now fix setted.
	*/
	public static function switch_ext_file_path($source_path, $dest_path, $cur_dl_dir, $phpbb_root_path, $ext_path)
	{
		// wait a moment before continue to give the users, forum and database the chance to stop without errors
		sleep(1); // Do not increase this to break the extension module with a possible bad error!!

		switch ($cur_dl_dir)
		{
			case 1:
				@mkdir($phpbb_root_path . 'store/oxpus');
				@chmod($phpbb_root_path . 'store/oxpus', 0755);
				@mkdir($phpbb_root_path . 'store/oxpus/dlext');
				@chmod($phpbb_root_path . 'store/oxpus/dlext', 0755);
				$handle = fopen($phpbb_root_path . 'store/.htaccess', 'w');
				fputs($handle, "<Files *>\n");
				fputs($handle, "	Order Deny,Allow\n");
				fputs($handle, "	Allow from localhost 127.0.0.1 " . generate_board_url() . "\n");
				fputs($handle, "	Require ip 127.0.0.1\n");
				fputs($handle, "</Files>");
				fclose($handle);
			break;
			case 2:
				@mkdir($ext_path . 'files');
				@chmod($ext_path . 'files', 0755);
			break;
		}

		$move_success = self::_move_dl_basis($source_path, $dest_path);

		if (!$move_success)
		{
			$return = 1; // Can't build new file base!
		}
		else
		{
			if (!self::_drop_dl_basis($source_path))
			{
				$return = 2; // Old file base can't be dropped!
			}
			else
			{
				$return = -1; // File base successfull moved. Yeah!
			}
		}

		switch ($cur_dl_dir)
		{
			case 1:
				@rmdir($ext_path . 'files');
			break;
			case 2:
				@rmdir($phpbb_root_path . 'store/oxpus/dlext');
				@rmdir($phpbb_root_path . 'store/oxpus');
				$handle = fopen($phpbb_root_path . 'store/.htaccess', 'w');
				fwrite($handle, "<Files *>\n");
				fwrite($handle, "	Order Allow,Deny\n");
				fwrite($handle, "	Deny from All\n");
				fwrite($handle, "</Files>");
				fclose($handle);
			break;
		}

		return $return;
	}

	/*
	* Read the existing downloads folder tree to select a path within the categories management
	* instead enter the complete path "old school" manually.
	*/
	public static function get_file_base_tree($file_base, $path, $level = 0)
	{
		$tree = array();
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
				for ($i = 0; $i < $level; $i++)
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

				$cat_path = str_replace(DL_EXT_FILES_FOLDER, '', $file_base . $entry . '/');
				$entry_path = str_replace(DL_EXT_FILES_FOLDER, '', $file_base);
				$tree[] = array(
					'cat_path'	=> $cat_path,
					'selected'	=> $selected,
					'entry'		=> $separator . $entry . '/',
				);

				$level++;
				$tmp_tree = self::get_file_base_tree($file_base . $entry, $path, $level);
				$level--;

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
