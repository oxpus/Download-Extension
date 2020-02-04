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

class dlext_extra implements dlext_extra_interface
{
	/* @var \phpbb\db\driver\driver_interface */
	protected $db;

	protected $dlext_auth;
	protected $dlext_files;
	protected $dlext_init;
	protected $dlext_main;

	/**
	* Constructor
	*
	* @param Container 								$phpbb_container

	* @param \phpbb\db\driver\driver_interfacer		$db
	*/
	public function __construct(
		Container $phpbb_container,

		\phpbb\db\driver\driver_interface $db,
		$dlext_auth,
		$dlext_files,
		$dlext_init,
		$dlext_main
	)
	{
		$this->db 			= $db;

		$this->dlext_auth	= $dlext_auth;
		$this->dlext_files	= $dlext_files;
		$this->dlext_init	= $dlext_init;
		$this->dlext_main	= $dlext_main;
	}

	public function get_todo()
	{
		$todo = array();

		$dl_files = $this->dlext_files->all_files(0, '', 'ASC', "AND todo <> '' AND todo IS NOT NULL", 0, 0, 'cat, id, description, hack_version, todo, todo_uid, todo_flags, todo_bitfield');
		$dl_cats = $this->dlext_main->full_index(0, 0, 0, 1);

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

	public function dl_dropdown($parent = 0, $level = 0, $select_cat = 0, $perm, $rem_cat = 0)
	{
		$dl_auth = $this->dlext_auth->dl_auth();
		$dl_index = $this->dlext_auth->dl_index();

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
				if (isset($dl_index[$cat_id][$perm]) && $dl_index[$cat_id][$perm] || isset($dl_auth[$cat_id][$perm]) && $dl_auth[$cat_id][$perm] || $this->dlext_auth->user_admin())
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
				$catlist .= $this->dl_dropdown($cat_id, $level, $select_cat, $perm, $rem_cat);
				$level--;
			}
		}

		return $catlist;
	}

	public function dl_cat_select($parent = 0, $level = 0, $select_cat = array())
	{
		$dl_index = $this->dlext_auth->dl_index();

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
				$catlist .= $this->dl_cat_select($cat_id, $level, $select_cat);
				$level--;
			}
		}

		return $catlist;
	}

	public function dl_user_switch($user_id = 0, $username = '', $update = false)
	{
		$value = '';

		if ($update && $username)
		{
			$sql = 'SELECT user_id
				FROM ' . USERS_TABLE . "
				WHERE username_clean = '" . $this->db->sql_escape(utf8_clean_string($username)) . "'";
			$result = $this->db->sql_query($sql);
			$user_id = (int) $this->db->sql_fetchfield('user_id');
			$this->db->sql_freeresult($result);

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
			$result = $this->db->sql_query($sql);
			$username = $this->db->sql_fetchfield('username_clean');
			$this->db->sql_freeresult($result);

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
