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

class dlext_cache implements dlext_cache_interface
{
	/* @var \phpbb\db\driver\driver_interface */
	protected $db;

	protected $php_ext;

	/**
	* Constructor
	*
	* @param Container 								$phpbb_container

	* @param \phpbb\db\driver\driver_interfacer		$db
	*/
	public function __construct(
		Container $phpbb_container,

		\phpbb\db\driver\driver_interface $db,
		$php_ext
	)
	{
		$this->db 		= $db;
		$this->php_ext	= '.' . $php_ext;

		if (!defined('DL_AUTH_TABLE'))
		{
			$phpbb_container->get('oxpus.dlext.constants')->init();
		}
	}

	/**
	 * Download MOD Category Cache
	*/
	public function obtain_dl_cats()
	{
		if (($dl_index = $this->get('_dl_cats')) === false)
		{
			$sql_array = array(
				'SELECT'	=> 'c.*, t.cat_traffic_use',
			
				'FROM'		=> array(DL_CAT_TABLE => 'c'),
			);

			$sql_array['LEFT_JOIN'] = array();

			$sql_array['LEFT_JOIN'][] = array(
				'FROM'	=> array(DL_CAT_TRAF_TABLE => 't'),
				'ON'	=> 't.cat_id = c.id'
			);
		
			$sql_array['ORDER_BY'] = 'parent, sort';

			$sql = $this->db->sql_build_query('SELECT', $sql_array);

			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$dl_index[$row['id']] = $row;

				$dl_index[$row['id']]['auth_view_real'] = $dl_index[$row['id']]['auth_view'];
				$dl_index[$row['id']]['auth_dl_real'] = $dl_index[$row['id']]['auth_dl'];
				$dl_index[$row['id']]['auth_up_real'] = $dl_index[$row['id']]['auth_up'];
				$dl_index[$row['id']]['auth_mod_real'] = $dl_index[$row['id']]['auth_mod'];
				$dl_index[$row['id']]['cat_name_nav'] = $dl_index[$row['id']]['cat_name'];
			}

			$this->db->sql_freeresult($result);

			$this->put('_dl_cats', $dl_index);
		}

		return $dl_index;
	}

	/**
	 * Download MOD Blacklist Cache
	*/
	public function obtain_dl_blacklist()
	{
		if (($dl_black = $this->get('_dl_black')) === false)
		{
			$sql = 'SELECT extention FROM ' . DL_EXT_BLACKLIST . '
					ORDER BY extention';
			$result = $this->db->sql_query($sql);

			while ( $row = $this->db->sql_fetchrow($result) )
			{
				$dl_black[] = $row['extention'];
			}

			$this->db->sql_freeresult($result);

			$this->put('_dl_black', $dl_black);
		}

		return $dl_black;
	}

	/**
	 * Download MOD Cat Filecount Cache
	*/
	public function obtain_dl_cat_counts()
	{
		if (($dl_cat_counts = $this->get('_dl_cat_counts')) === false)
		{
			$sql = 'SELECT COUNT(id) AS total, cat FROM ' . DOWNLOADS_TABLE . '
				GROUP BY cat';
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$dl_cat_counts[$row['cat']] = $row['total'];
			}

			$this->db->sql_freeresult($result);

			$this->put('_dl_cat_counts', $dl_cat_counts);
		}

		return $dl_cat_counts;
	}

	/**
	 * Download MOD Files Cache
	*/
	public function obtain_dl_files($dl_new_time, $dl_edit_time)
	{
		if (!$dl_new_time && !$dl_edit_time)
		{
			return $dl_file;
		}

		$dl_new_time		= (int) $dl_new_time;
		$dl_edit_time		= (int) $dl_edit_time;

		$cache_release_time = 86400;

		if (($dl_file = $this->get('_dl_file_preset')) === false)
		{
			$dl_file = array();

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
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
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

			$this->db->sql_freeresult($result);

			$this->put('_dl_file_preset', $dl_file, $cache_release_time);
		}

		return $dl_file;
	}

	/**
	 * Download MOD Auth Cache
	*/
	public function obtain_dl_auth()
	{
		$auth_cat = $group_perm_ids = $auth_perm = array();

		if (($dl_auth_perm = $this->get('_dl_auth')) === false)
		{
			$sql = 'SELECT * FROM ' . DL_AUTH_TABLE;
			$result = $this->db->sql_query($sql);

			$total_perms = $this->db->sql_affectedrows($result);

			if ($total_perms)
			{
				while ($row = $this->db->sql_fetchrow($result))
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

				$this->db->sql_freeresult($result);

				if ($total_perms > 1)
				{
					$auth_cat = array_unique($auth_cat);
					$group_perm_ids = array_unique($group_perm_ids);
					sort($auth_cat);
					sort($group_perm_ids);
				}
			}

			$dl_auth_perm['auth_cat'] = $auth_cat;
			$dl_auth_perm['group_perm_ids'] = $group_perm_ids;
			$dl_auth_perm['auth_perm'] = $auth_perm;

			$this->put('_dl_auth', $dl_auth_perm);
		}

		return $dl_auth_perm;
	}

	/**
	 * Download MOD Auth Group Settings Cache
	*/
	public function obtain_dl_access_groups($auth_cat, $group_perm_ids, $user_id)
	{
		$dl_auth_groups = array();

		if (($dl_auth_groups = $this->get('_dl_auth_groups')) === false)
		{
			$sql = 'SELECT user_id, group_id FROM ' . USER_GROUP_TABLE . '
				WHERE ' . $this->db->sql_in_set('group_id', array_map('intval', $group_perm_ids)) . '
					AND user_pending <> ' . true;
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$dl_auth_groups[$row['user_id']][]	= $row['group_id'];
			}
			$this->db->sql_freeresult($result);

			$this->put('_dl_auth_groups', $dl_auth_groups);
		}

		if (!isset($dl_auth_groups[$user_id]) || !$dl_auth_groups[$user_id][0])
		{
			return array();
		}

		$group_ids = $dl_auth_groups[$user_id];

		for ($i = 0; $i < sizeof($auth_cat); $i++)
		{
			$auth_view = $auth_dl = $auth_up = $auth_mod = 0;
			$cat = $auth_cat[$i];

			for ($j = 0; $j < sizeof($group_ids); $j++)
			{
				$user_group = $group_ids[$j];

				if (isset($auth_perm[$cat][$user_group]['auth_view']) && $auth_perm[$cat][$user_group]['auth_view'] == true)
				{
					$auth_view = true;
				}
				if (isset($auth_perm[$cat][$user_group]['auth_dl']) && $auth_perm[$cat][$user_group]['auth_dl'] == true)
				{
					$auth_dl = true;
				}
				if (isset($auth_perm[$cat][$user_group]['auth_up']) && $auth_perm[$cat][$user_group]['auth_up'] == true)
				{
					$auth_up = true;
				}
				if (isset($auth_perm[$cat][$user_group]['auth_mod']) && $auth_perm[$cat][$user_group]['auth_mod'] == true)
				{
					$auth_mod = true;
				}
			}

			$cat_auth_array[$cat]['auth_view'] = $auth_view;
			$cat_auth_array[$cat]['auth_dl'] = $auth_dl;
			$cat_auth_array[$cat]['auth_up'] = $auth_up;
			$cat_auth_array[$cat]['auth_mod'] = $auth_mod;
		}

		return $cat_auth_array;
	}

	/**
	 * Download MOD File preload
	*/
	public function obtain_dl_file_p()
	{
		$dl_file_p = array();

		if (($dl_file_p = $this->get('_dl_file_p')) === false)
		{
			$sql = 'SELECT id, cat, file_name, real_file, file_size, extern, free, file_traffic, klicks FROM ' . DOWNLOADS_TABLE . '
					WHERE approve = ' . true;
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$dl_file_p[$row['id']] = $row;
			}
			$this->db->sql_freeresult($result);

			$this->put('_dl_file_p', $dl_file_p);
		}

		return $dl_file_p;
	}

	/**
	* Get saved cache object
	*/
	public function get($var_name)
	{
		if ($var_name[0] == '_')
		{
			if (!$this->_exists($var_name))
			{
				return false;
			}

			return $this->_read('data' . $var_name);
		}
		else
		{
			return ($this->_exists($var_name)) ? $vars[$var_name] : false;
		}
	}

	/**
	* Put data into cache
	*/
	public function put($var_name, $var, $ttl = 31536000)
	{
		if ($var_name[0] == '_')
		{
			$this->_write('data' . $var_name, $var, time() + $ttl);
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
	public function _exists($var_name)
	{
		if ($var_name[0] == '_')
		{
			return file_exists(DL_EXT_CACHE_PATH . 'data' . $var_name . $this->php_ext);
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
	public function _read($filename)
	{
		$file = DL_EXT_CACHE_PATH . $filename . $this->php_ext;

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
			$this->remove_file($file);
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
	public function _write($filename, $data = null, $expires = 0, $query = '')
	{
		$file = DL_EXT_CACHE_PATH . $filename . $this->php_ext;

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
	public function remove_file($filename, $check = false)
	{
		if ($check && !@is_writable(DL_EXT_CACHE_PATH))
		{
			// E_USER_ERROR - not using language entry - intended.
			trigger_error('Die Datei ' . DL_EXT_CACHE_PATH . $filename . ' kann nicht entfernt werden. Bitte die Berechtigungen des Cache-Ordners prüfen.', E_USER_ERROR);
		}

		return @unlink(DL_EXT_CACHE_PATH . $filename);
	}
}
