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

/**
* Class for grabbing/handling cached entries on Download MOD
*/
class dl_cache extends dl_mod
{
	/**
	 * Download MOD Category Cache
	*/
	public static function obtain_dl_cats()
	{
		global $db, $user;

		if (($dl_index = self::get('_dl_cats')) === false)
		{
			$sql = "SELECT * FROM " . DL_CAT_TABLE . '
				ORDER BY parent, sort';
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$dl_index[$row['id']] = $row;

				$dl_index[$row['id']]['auth_view_real'] = $dl_index[$row['id']]['auth_view'];
				$dl_index[$row['id']]['auth_dl_real'] = $dl_index[$row['id']]['auth_dl'];
				$dl_index[$row['id']]['auth_up_real'] = $dl_index[$row['id']]['auth_up'];
				$dl_index[$row['id']]['auth_mod_real'] = $dl_index[$row['id']]['auth_mod'];
				$dl_index[$row['id']]['cat_name_nav'] = $dl_index[$row['id']]['cat_name'];
				$dl_index[$row['id']]['cat_traffic_use'] = 0;
			}

			$db->sql_freeresult($result);

			self::put('_dl_cats', $dl_index);
		}

		$sql = "SELECT cat_id, cat_traffic_use FROM " . DL_CAT_TRAF_TABLE;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$dl_index[$row['cat_id']]['cat_traffic_use'] = $row['cat_traffic_use'];
		}

		$db->sql_freeresult($result);

		return $dl_index;
	}

	/**
	 * Download MOD Configuration Cache
	*/
	public static function obtain_dl_config()
	{
		global $db;

		$sql = 'SELECT * FROM ' . DL_REM_TRAF_TABLE;
		$result = $db->sql_query($sql);

		while ( $row = $db->sql_fetchrow($result) )
		{
			$dl_config[$row['config_name']] = $row['config_value'];
		}
		$db->sql_freeresult($result);

		return $dl_config;
	}

	/**
	 * Download MOD Blacklist Cache
	*/
	public static function obtain_dl_blacklist()
	{
		global $db;

		if (($dl_black = self::get('_dl_black')) === false)
		{
			$sql = 'SELECT extention FROM ' . DL_EXT_BLACKLIST . '
				ORDER BY extention';
			$result = $db->sql_query($sql);

			while ( $row = $db->sql_fetchrow($result) )
			{
				$dl_black[] = $row['extention'];
			}
			$db->sql_freeresult($result);

			self::put('_dl_black', $dl_black);
		}

		return $dl_black;
	}

	/**
	 * Download MOD Cat Filecount Cache
	*/
	public static function obtain_dl_cat_counts()
	{
		global $db;

		if (($dl_cat_counts = self::get('_dl_cat_counts')) === false)
		{
			$sql = 'SELECT COUNT(id) AS total, cat FROM ' . DOWNLOADS_TABLE . '
				GROUP BY cat';
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$dl_cat_counts[$row['cat']] = $row['total'];
			}
			$db->sql_freeresult($result);

			self::put('_dl_cat_counts', $dl_cat_counts);
		}

		return $dl_cat_counts;
	}

	/**
	 * Download MOD Files Cache
	*/
	public static function obtain_dl_files($dl_new_time, $dl_edit_time)
	{
		$sl_file = array();
		$dl_file['new'] = array();
		$dl_file['new_sum'] = array();
		$dl_file['edit'] = array();
		$dl_file['edit_sum'] = array();
		$dl_file['id'] = array();

		if (!$dl_new_time && !$dl_edit_time)
		{
			return $dl_file;
		}

		$dl_new_time		= intval($dl_new_time);
		$dl_edit_time		= intval($dl_edit_time);

		$cache_release_time = max($dl_new_time, $dl_edit_time) * 86400;

		if (($dl_file = self::get('_dl_file_preset')) === false)
		{
			global $db;

			$cur_time = time();
			$sql_time_preset = '';

			if ($dl_new_time && $dl_edit_time)
			{
				$sql_time_preset = " AND ((add_time = change_time AND (($cur_time - change_time) / 86400 <= $dl_new_time)) OR ";
				$sql_time_preset .= " (add_time <> change_time AND (($cur_time - change_time) / 86400 <= $dl_edit_time))) ";
			}

			if ($dl_new_time && !$dl_edit_time)
			{
				$sql_time_preset = " AND (add_time = change_time AND (($cur_time - change_time) / 86400 <= $dl_new_time)) ";
			}

			if (!$dl_new_time && $dl_edit_time)
			{
				$sql_time_preset = " AND (add_time <> change_time AND (($cur_time - change_time) / 86400 <= $dl_edit_time)) ";
			}

			$sql = 'SELECT id, cat, add_time, change_time FROM ' . DOWNLOADS_TABLE . '
					WHERE approve = ' . true . "
						$sql_time_preset";
			$result = $db->sql_query($sql);

			$total_presets = $db->sql_affectedrows($result);

			if ($total_presets)
			{
				while ($row = $db->sql_fetchrow($result))
				{
					$dl_id = $row['id'];
					$cat_id = $row['cat'];
					$change_time = $row['change_time'];
					$add_time = $row['add_time'];

					if (!isset($dl_file['new'][$cat_id]))
					{
						$dl_file['new'][$cat_id][$dl_id] = 0;
					}
					if (!isset($dl_file['new_sum'][$cat_id]))
					{
						$dl_file['new_sum'][$cat_id] = 0;
					}
					if (!isset($dl_file['edit'][$cat_id]))
					{
						$dl_file['edit'][$cat_id][$dl_id] = 0;
					}
					if (!isset($dl_file['edit_sum'][$cat_id]))
					{
						$dl_file['edit_sum'][$cat_id] = 0;
					}

					$count_new = ($change_time == $add_time && ((time() - $change_time)) / 86400 <= $dl_new_time && $dl_new_time > 0) ? 1 : 0;
					$count_edit = ($change_time != $add_time && ((time() - $change_time) / 86400) <= $dl_edit_time && $dl_edit_time > 0) ? 1 : 0;

					$dl_file['new'][$cat_id][$dl_id] = $count_new;
					$dl_file['new_sum'][$cat_id] += $count_new;
					$dl_file['edit'][$cat_id][$dl_id] = $count_edit;
					$dl_file['edit_sum'][$cat_id] += $count_edit;
				}
			}

			$db->sql_freeresult($result);

			self::put('_dl_file_preset', $dl_file, $cache_release_time);
		}

		return $dl_file;
	}

	/**
	 * Download MOD Auth Cache
	*/
	public static function obtain_dl_auth()
	{
		global $db;

		$auth_cat = $group_perm_ids = $auth_perm = array();

		if (($dl_auth_perm = self::get('_dl_auth')) === false)
		{
			$sql = 'SELECT * FROM ' . DL_AUTH_TABLE;
			$result = $db->sql_query($sql);

			$total_perms = $db->sql_affectedrows($result);

			if ($total_perms)
			{
				while ($row = $db->sql_fetchrow($result))
				{
					$cat_id = $row['cat_id'];
					$group_id = $row['group_id'];

					$auth_cat[] = $cat_id;
					$group_perm_ids[] = $group_id;

					$auth_perm[$cat_id][$group_id]['auth_view'] = $row['auth_view'];
					$auth_perm[$cat_id][$group_id]['auth_dl'] = $row['auth_dl'];
					$auth_perm[$cat_id][$group_id]['auth_up'] = $row['auth_up'];
					$auth_perm[$cat_id][$group_id]['auth_mod'] = $row['auth_mod'];
				}
				$db->sql_freeresult($result);

				if ($total_perms > 1)
				{
					$auth_cat = array_unique($auth_cat);
					sort($auth_cat);
				}
			}

			$dl_auth_perm['auth_cat'] = $auth_cat;
			$dl_auth_perm['group_perm_ids'] = $group_perm_ids;
			$dl_auth_perm['auth_perm'] = $auth_perm;

			self::put('_dl_auth', $dl_auth_perm);
		}

		return $dl_auth_perm;
	}

	/**
	* Get saved cache object
	*/
	public static function get($var_name)
	{
		static $vars;

		if ($var_name[0] == '_')
		{
			if (!self::_exists($var_name))
			{
				return false;
			}

			return self::_read('data' . $var_name);
		}
		else
		{
			return (self::_exists($var_name)) ? $vars[$var_name] : false;
		}
	}

	/**
	* Put data into cache
	*/
	public static function put($var_name, $var, $ttl = 31536000)
	{
		static $vars;
		static $var_expires;
		static $is_modified;

		if ($var_name[0] == '_')
		{
			self::_write('data' . $var_name, $var, time() + $ttl);
		}
		else
		{
			$vars[$var_name] = $var;
			$var_expires[$var_name] = time() + $ttl;
			$is_modified = true;
		}
	}

	/**
	* Check if a given cache entry exist
	*/
	static private function _exists($var_name)
	{
		static $var_expires;
		static $vars;

		if ($var_name[0] == '_')
		{
			return file_exists(DL_EXT_CACHE_FOLDER . 'data' . $var_name . dl_init::phpEx());
		}
		else
		{
			if (!isset($var_expires[$var_name]))
			{
				return false;
			}

			return (time() > $var_expires[$var_name]) ? false : isset($vars[$var_name]);
		}
	}

	/**
	* Read cached data from a specified file
	*
	* @access private
	* @param string $filename Filename to write
	* @return mixed False if an error was encountered, otherwise the data type of the cached data
	*/
	static private function _read($filename)
	{
		$file = DL_EXT_CACHE_FOLDER . $filename . dl_init::phpEx();

		$type = substr($filename, 0, strpos($filename, '_'));

		if (!file_exists($file))
		{
			return false;
		}

		if (!($handle = @fopen($file, 'rb')))
		{
			return false;
		}

		// Skip the PHP header
		fgets($handle);

		$data = false;
		$line = 0;

		while (($buffer = fgets($handle)) && !feof($handle))
		{
			$buffer = substr($buffer, 0, -1); // Remove the LF

			// $buffer is only used to read integers
			// if it is non numeric we have an invalid
			// cache file, which we will now remove.
			if (!is_numeric($buffer))
			{
				break;
			}

			if ($line == 0)
			{
				$expires = (int) $buffer;

				if (time() >= $expires)
				{
					break;
				}

				if ($type == 'sql')
				{
					// Skip the query
					fgets($handle);
				}
			}
			else if ($line == 1)
			{
				$bytes = (int) $buffer;

				// Never should have 0 bytes
				if (!$bytes)
				{
					break;
				}

				// Grab the serialized data
				$data = fread($handle, $bytes);

				// Read 1 byte, to trigger EOF
				fread($handle, 1);

				if (!feof($handle))
				{
					// Somebody tampered with our data
					$data = false;
				}
				break;
			}
			else
			{
				// Something went wrong
				break;
			}
			$line++;
		}
		fclose($handle);

		// unserialize if we got some data
		$data = ($data !== false) ? @json_decode($data, true) : $data;

		if ($data === false)
		{
			self::remove_file($file);
			return false;
		}

		return $data;
	}

	/**
	* Write cache data to a specified file
	*
	* @access private
	* @param string $filename Filename to write
	* @param mixed $data Data to store
	* @param int $expires Timestamp when the data expires
	* @param string $query Query when caching SQL queries
	* @return bool True if the file was successfully created, otherwise false
	*/
	static private function _write($filename, $data = null, $expires = 0, $query = '')
	{
		$file = DL_EXT_CACHE_FOLDER . $filename . dl_init::phpEx();

		if ($handle = @fopen($file, 'wb'))
		{
			@flock($handle, LOCK_EX);

			// File header
			fwrite($handle, '<' . '?php exit; ?' . '>');

			fwrite($handle, "\n" . $expires . "\n");

			if (strpos($filename, 'sql_') === 0)
			{
				fwrite($handle, $query . "\n");
			}

			$data = json_encode($data);

			fwrite($handle, strlen($data) . "\n");
			fwrite($handle, $data);

			@flock($handle, LOCK_UN);
			fclose($handle);

			chmod($file, 0755);

			return true;
		}

		return false;
	}

	/**
	* Removes/unlinks file
	*/
	public static function remove_file($filename, $check = false)
	{
		if ($check && !@is_writable(DL_EXT_CACHE_FOLDER))
		{
			// E_USER_ERROR - not using language entry - intended.
			trigger_error('Die Datei ' . DL_EXT_CACHE_FOLDER . $filename . ' kann nicht entfernt werden. Bitte die Berechtigungen des Cache-Ordners prüfen.', E_USER_ERROR);
		}

		return @unlink(DL_EXT_CACHE_FOLDER . $filename);
	}
}
