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

class dl_extra extends dl_mod
{
	public static function get_todo()
	{
		global $helper;

		$todo = array();

		$dl_files = dl_files::all_files(0, '', 'ASC', "AND todo <> '' AND todo IS NOT NULL", 0, 0, 'cat, id, description, hack_version, todo, todo_uid, todo_flags, todo_bitfield');
		$dl_cats = dl_main::full_index('', 0, 0, 0, 1);

		for ($i = 0; $i < sizeof($dl_files); $i++)
		{
			$cat_id = $dl_files[$i]['cat'];
			if (in_array($cat_id, $dl_cats))
			{
				$file_name		= $dl_files[$i]['description'];
				$hack_version	= ($dl_files[$i]['hack_version'] != '') ? ' ' . $dl_files[$i]['hack_version'] : '';
				$todo_text		= generate_text_for_display($dl_files[$i]['todo'], $dl_files[$i]['todo_uid'], $dl_files[$i]['todo_bitfield'], $dl_files[$i]['todo_flags']);

				$todo['file_name'][]	= $file_name;
				$todo['hack_version'][]	= $hack_version;
				$todo['todo'][]			= $todo_text;
				$todo['df_id'][]		= $dl_files[$i]['id'];
			}
		}

		return $todo;
	}

	public static function dl_dropdown($parent = 0, $level = 0, $select_cat = 0, $perm, $rem_cat = 0)
	{
		static $dl_index, $dl_auth, $user_admin;

		global $dl_index, $dl_auth, $user_admin;

		if (!is_array($dl_index) || !sizeof($dl_index))
		{
			return;
		}

		if (!isset($catlist))
		{
			$catlist = '';
		}

		foreach($dl_index as $cat_id => $value)
		{
			if (isset($dl_index[$cat_id]['parent']) && $dl_index[$cat_id]['parent'] == $parent)
			{
				if (isset($dl_index[$cat_id][$perm]) && $dl_index[$cat_id][$perm] || isset($dl_auth[$cat_id][$perm]) && $dl_auth[$cat_id][$perm] || $user_admin)
				{
					$cat_name = $dl_index[$cat_id]['cat_name'];

					$seperator = '';

					if ($dl_index[$cat_id]['parent'] != 0)
					{
						for($i = 0; $i < $level; $i++)
						{
							$seperator .= '&nbsp;&nbsp;|';
						}
						$seperator .= '___&nbsp;';
					}

					if ($perm == 'auth_up' || $rem_cat)
					{
						$status = ($select_cat == $cat_id) ? 'selected="selected"' : '';
					}
					else
					{
						$status = '';
					}

					if ($rem_cat != $cat_id)
					{
						$catlist .= '<option value="' . $cat_id . '" ' . $status . '>' . $seperator . $cat_name . '</option>';
					}
				}

				$level++;
				$catlist .= self::dl_dropdown($cat_id, $level, $select_cat, $perm, $rem_cat);
				$level--;
			}
		}

		return $catlist;
	}

	public static function dl_cat_select($parent = 0, $level = 0, $select_cat = array())
	{
		static $dl_index;

		global $dl_index;

		if (!isset($catlist))
		{
			$catlist = '';
		}

		foreach($dl_index as $cat_id => $value)
		{
			if (isset($dl_index[$cat_id]['parent']) && $dl_index[$cat_id]['parent'] == $parent)
			{
				$cat_name = $dl_index[$cat_id]['cat_name'];

				$seperator = '';

				if ($dl_index[$cat_id]['parent'] != 0)
				{
					for($i = 0; $i < $level; $i++)
					{
						$seperator .= '&nbsp;&nbsp;|';
					}
					$seperator .= '___&nbsp;';
				}

				$status = (in_array($cat_id, $select_cat)) ? 'selected="selected"' : '';

				$catlist .= '<option value="' . $cat_id . '" ' . $status . '>' . $seperator . $cat_name . '</option>';

				$level++;
				$catlist .= self::dl_cat_select($cat_id, $level, $select_cat);
				$level--;
			}
		}

		return $catlist;
	}

	public static function dl_user_switch($user_id = 0, $username = '', $update = false)
	{
		global $db;

		$value = '';

		if ($update && $username)
		{
			$sql = 'SELECT user_id
				FROM ' . USERS_TABLE . "
				WHERE username_clean = '" . $db->sql_escape(utf8_clean_string($username)) . "'";
			$result = $db->sql_query($sql);
			$user_id = (int) $db->sql_fetchfield('user_id');
			$db->sql_freeresult($result);

			if (!$user_id)
			{
				$value = 0;
			}
			else
			{
				$value = $user_id;
			}
		}
		else if ($user_id > 0)
		{
			$sql = 'SELECT username_clean
				FROM ' . USERS_TABLE . '
				WHERE user_id = ' . (int) $user_id;
			$result = $db->sql_query($sql);
			$username = $db->sql_fetchfield('username_clean');
			$db->sql_freeresult($result);

			if (!$username)
			{
				$value = '';
			}
			else
			{
				$value = $username;
			}
		}

		return $value;
	}
}
