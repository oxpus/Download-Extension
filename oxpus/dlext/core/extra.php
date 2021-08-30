<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\core;

class extra implements extra_interface
{
	/* phpbb objects */
	protected $db;
	protected $language;

	/* extension owned objects */
	protected $dlext_auth;
	protected $dlext_files;
	protected $dlext_main;
	protected $dlext_constants;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\language\language				$language
	 * @param \oxpus\dlext\core\auth				$dlext_auth
	 * @param \oxpus\dlext\core\files				$dlext_files
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants

	 */
	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		\phpbb\language\language $language,
		\oxpus\dlext\core\auth $dlext_auth,
		\oxpus\dlext\core\files $dlext_files,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\helpers\constants $dlext_constants
	)
	{
		$this->db 				= $db;
		$this->language			= $language;

		$this->dlext_auth		= $dlext_auth;
		$this->dlext_files		= $dlext_files;
		$this->dlext_main		= $dlext_main;
		$this->dlext_constants	= $dlext_constants;
	}

	public function get_todo()
	{
		$todo = [];

		$fields		= ['cat', 'id', 'description', 'hack_version', 'todo', 'todo_uid', 'todo_flags', 'todo_bitfield'];
		$where		= ['todo' => ['AND', '<>', '~']];
		$dl_files 	= $this->dlext_files->all_files(0, [], $where, 0, 0, $fields);
		$dl_cats	= $this->dlext_main->full_index(0, 0, 0, $this->dlext_constants::DL_AUTH_CHECK_VIEW);

		for ($i = 0; $i < count($dl_files); ++$i)
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

	public function dl_dropdown($parent = 0, $level = 0, $select_cat = 0, $perm = 'auth_view', $rem_cat = 0, &$catlist = [])
	{
		$dl_index	= $this->dlext_auth->dl_index();
		$dl_auth	= $this->dlext_auth->dl_auth();

		if (empty($dl_index))
		{
			return '';
		}

		foreach (array_keys($dl_index) as $cat_id)
		{
			if (isset($dl_index[$cat_id]['parent']) && $dl_index[$cat_id]['parent'] == $parent)
			{
				if (isset($dl_index[$cat_id][$perm]) && $dl_index[$cat_id][$perm] || isset($dl_auth[$cat_id][$perm]) && $dl_auth[$cat_id][$perm] || $this->dlext_auth->user_admin())
				{
					$cat_name = $dl_index[$cat_id]['cat_name'];

					$seperator = '';

					if ($dl_index[$cat_id]['parent'] != 0)
					{
						$catlist_sub = $this->dlext_constants::DL_TRUE;

						for ($i = 0; $i < $level; ++$i)
						{
							$seperator .= $this->language->lang('DL_SEPERATOR_PREFIX');
						}

						$seperator .= $this->language->lang('DL_SEPERATOR_SUFFIX');
					}
					else
					{
						$catlist_sub = $this->dlext_constants::DL_FALSE;
					}

					if ($perm == 'auth_up' || $rem_cat)
					{
						$selected = ($select_cat == $cat_id) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
					}
					else
					{
						if (is_array($select_cat))
						{
							$selected = (in_array($cat_id, $select_cat)) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
						}
						else
						{
							$selected = $this->dlext_constants::DL_FALSE;
						}
					}

					if ($rem_cat != $cat_id || $rem_cat == $this->dlext_constants::DL_FALSE)
					{
						$catlist[$cat_id] = [
							'cat_id'	=> $cat_id,
							'sub'		=> $catlist_sub,
							'selected'	=> $selected,
							'seperator'	=> $seperator,
							'cat_name'	=> $cat_name,
						];
					}
				}

				++$level;
				$this->dl_dropdown($cat_id, $level, $select_cat, $perm, $rem_cat, $catlist);
				--$level;
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
