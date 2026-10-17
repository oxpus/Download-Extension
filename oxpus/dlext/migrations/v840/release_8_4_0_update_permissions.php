<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2025 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\migrations\v840;

class release_8_4_0_update_permissions extends \phpbb\db\migration\migration
{
	const DL_PERM_GENERAL_ALL = 1;
	const DL_PERM_GENERAL_REG_USER = 2;
	const DL_PERM_ALL = 0;
	const DL_PERM_USER = 1;
	const DL_FALSE = 0;
	const DL_TRUE = 1;
	const DL_PERM_ADMIN = 3;
	const DL_PERM_MOD = 2;

	protected $dl_ext_version = '8.4.0';

	public function effectively_installed()
	{
		return isset($this->config['dl_ext_version']) && version_compare($this->config['dl_ext_version'], $this->dl_ext_version, '>=');
	}

	public static function depends_on()
	{
		return ['\oxpus\dlext\migrations\v840\release_8_4_0'];
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'convert_permission_settings']]],
		];
	}

	public function convert_permission_settings()
	{
		// Read all permissions for every user and registered users from the category setting
		// Read the given auth set from auth table
		// Insert cat permissions for every user to "REGISTERED", "GUESTS" and "REGISTERED_COPPA". Strictly avoid "BOTS" and custom groups!
		// Insert cat permissions for registered users to group "REGISTERED" and "REGISTERED_COPPA".
		// Compare cat permissions with saved auth datasets
		// Update existing, insert new datasets

		$special_groups = [];
		$special_groups_ids = [];
		$perms_data = [];
		$insert_perms = [];
		$sql_insert_perms = [];
		$existing_auth_ary = [];

		// get all special user groups
		$sql = 'SELECT group_id, group_name FROM ' . GROUPS_TABLE . ' WHERE group_type = ' . (int) GROUP_SPECIAL;
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$special_groups[$row['group_name']] = $row['group_id'];
			$special_groups_ids[] = $row['group_id'];
		}

		$this->db->sql_freeresult($result);

		// read all cat owned permissions and prepare the new permission datasets
		$sql = 'SELECT id as cat_id, auth_view, auth_dl, auth_up, auth_mod, auth_cread, auth_cpost FROM ' . $this->table_prefix . 'downloads_cat';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($row['auth_view'] == self::DL_PERM_GENERAL_ALL)
			{
				$perms_data[$row['cat_id']][$special_groups['GUESTS']]['auth_view'] = self::DL_TRUE;
				$perms_data[$row['cat_id']][$special_groups['REGISTERED']]['auth_view'] = self::DL_TRUE;
				$perms_data[$row['cat_id']][$special_groups['REGISTERED_COPPA']]['auth_view'] = self::DL_TRUE;
				$perms_data[$row['cat_id']][$special_groups['ADMINISTRATORS']]['auth_view'] = self::DL_TRUE;
			}

			if ($row['auth_view'] == self::DL_PERM_GENERAL_REG_USER)
			{
				$perms_data[$row['cat_id']][$special_groups['GUESTS']]['auth_view'] = self::DL_FALSE;
				$perms_data[$row['cat_id']][$special_groups['REGISTERED']]['auth_view'] = self::DL_TRUE;
				$perms_data[$row['cat_id']][$special_groups['REGISTERED_COPPA']]['auth_view'] = self::DL_TRUE;
				$perms_data[$row['cat_id']][$special_groups['ADMINISTRATORS']]['auth_view'] = self::DL_TRUE;
			}

			if ($row['auth_dl'] == self::DL_PERM_GENERAL_ALL)
			{
				$perms_data[$row['cat_id']][$special_groups['GUESTS']]['auth_dl'] = self::DL_TRUE;
				$perms_data[$row['cat_id']][$special_groups['REGISTERED']]['auth_dl'] = self::DL_TRUE;
				$perms_data[$row['cat_id']][$special_groups['REGISTERED_COPPA']]['auth_dl'] = self::DL_TRUE;
				$perms_data[$row['cat_id']][$special_groups['ADMINISTRATORS']]['auth_dl'] = self::DL_TRUE;
			}

			if ($row['auth_dl'] == self::DL_PERM_GENERAL_REG_USER)
			{
				$perms_data[$row['cat_id']][$special_groups['GUESTS']]['auth_dl'] = self::DL_FALSE;
				$perms_data[$row['cat_id']][$special_groups['REGISTERED']]['auth_dl'] = self::DL_TRUE;
				$perms_data[$row['cat_id']][$special_groups['REGISTERED_COPPA']]['auth_dl'] = self::DL_TRUE;
				$perms_data[$row['cat_id']][$special_groups['ADMINISTRATORS']]['auth_dl'] = self::DL_TRUE;
			}

			if ($row['auth_up'] == self::DL_PERM_GENERAL_ALL)
			{
				$perms_data[$row['cat_id']][$special_groups['GUESTS']]['auth_up'] = self::DL_TRUE;
				$perms_data[$row['cat_id']][$special_groups['REGISTERED']]['auth_up'] = self::DL_TRUE;
				$perms_data[$row['cat_id']][$special_groups['REGISTERED_COPPA']]['auth_up'] = self::DL_TRUE;
				$perms_data[$row['cat_id']][$special_groups['ADMINISTRATORS']]['auth_up'] = self::DL_TRUE;
			}

			if ($row['auth_up'] == self::DL_PERM_GENERAL_REG_USER)
			{
				$perms_data[$row['cat_id']][$special_groups['GUESTS']]['auth_up'] = self::DL_FALSE;
				$perms_data[$row['cat_id']][$special_groups['REGISTERED']]['auth_up'] = self::DL_TRUE;
				$perms_data[$row['cat_id']][$special_groups['REGISTERED_COPPA']]['auth_up'] = self::DL_TRUE;
				$perms_data[$row['cat_id']][$special_groups['ADMINISTRATORS']]['auth_up'] = self::DL_TRUE;
			}

			if ($row['auth_mod'] == self::DL_PERM_GENERAL_ALL)
			{
				$perms_data[$row['cat_id']][$special_groups['GUESTS']]['auth_mod'] = self::DL_TRUE;
				$perms_data[$row['cat_id']][$special_groups['REGISTERED']]['auth_mod'] = self::DL_TRUE;
				$perms_data[$row['cat_id']][$special_groups['REGISTERED_COPPA']]['auth_mod'] = self::DL_TRUE;
				$perms_data[$row['cat_id']][$special_groups['ADMINISTRATORS']]['auth_mod'] = self::DL_TRUE;
			}

			if ($row['auth_mod'] == self::DL_PERM_GENERAL_REG_USER)
			{
				$perms_data[$row['cat_id']][$special_groups['GUESTS']]['auth_mod'] = self::DL_FALSE;
				$perms_data[$row['cat_id']][$special_groups['REGISTERED']]['auth_mod'] = self::DL_TRUE;
				$perms_data[$row['cat_id']][$special_groups['REGISTERED_COPPA']]['auth_mod'] = self::DL_TRUE;
				$perms_data[$row['cat_id']][$special_groups['ADMINISTRATORS']]['auth_mod'] = self::DL_TRUE;
			}

			if ($row['auth_cread'] == self::DL_PERM_ALL)
			{
				$perms_data[$row['cat_id']][$special_groups['GUESTS']]['auth_cread'] = self::DL_TRUE;
				$perms_data[$row['cat_id']][$special_groups['REGISTERED']]['auth_cread'] = self::DL_TRUE;
				$perms_data[$row['cat_id']][$special_groups['REGISTERED_COPPA']]['auth_cread'] = self::DL_TRUE;
				$perms_data[$row['cat_id']][$special_groups['ADMINISTRATORS']]['auth_cread'] = self::DL_TRUE;
			}

			if ($row['auth_cread'] == self::DL_PERM_USER)
			{
				$perms_data[$row['cat_id']][$special_groups['GUESTS']]['auth_cread'] = self::DL_FALSE;
				$perms_data[$row['cat_id']][$special_groups['REGISTERED']]['auth_cread'] = self::DL_TRUE;
				$perms_data[$row['cat_id']][$special_groups['REGISTERED_COPPA']]['auth_cread'] = self::DL_TRUE;
				$perms_data[$row['cat_id']][$special_groups['ADMINISTRATORS']]['auth_cread'] = self::DL_TRUE;
			}

			if ($row['auth_cread'] == self::DL_PERM_ADMIN)
			{
				$perms_data[$row['cat_id']][$special_groups['ADMINISTRATORS']]['auth_cread'] = self::DL_TRUE;
			}

			if ($row['auth_cpost'] == self::DL_PERM_ALL)
			{
				$perms_data[$row['cat_id']][$special_groups['GUESTS']]['auth_cpost'] = self::DL_TRUE;
				$perms_data[$row['cat_id']][$special_groups['REGISTERED']]['auth_cpost'] = self::DL_TRUE;
				$perms_data[$row['cat_id']][$special_groups['REGISTERED_COPPA']]['auth_cpost'] = self::DL_TRUE;
				$perms_data[$row['cat_id']][$special_groups['ADMINISTRATORS']]['auth_cpost'] = self::DL_TRUE;
			}

			if ($row['auth_cpost'] == self::DL_PERM_USER)
			{
				$perms_data[$row['cat_id']][$special_groups['GUESTS']]['auth_cpost'] = self::DL_FALSE;
				$perms_data[$row['cat_id']][$special_groups['REGISTERED']]['auth_cpost'] = self::DL_TRUE;
				$perms_data[$row['cat_id']][$special_groups['REGISTERED_COPPA']]['auth_cpost'] = self::DL_TRUE;
				$perms_data[$row['cat_id']][$special_groups['ADMINISTRATORS']]['auth_cpost'] = self::DL_TRUE;
			}

			if ($row['auth_cpost'] == self::DL_PERM_ADMIN)
			{
				$perms_data[$row['cat_id']][$special_groups['ADMINISTRATORS']]['auth_cpost'] = self::DL_TRUE;
			}
		}

		$this->db->sql_freeresult($result);

		// read all existing permissions for the special groups
		$sql = 'SELECT cat_id, group_id, auth_view, auth_dl, auth_up, auth_mod, auth_cread, auth_cpost
				FROM ' . $this->table_prefix . 'dl_auth
				WHERE ' . $this->db->sql_in_set('group_id', $special_groups_ids);
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$existing_auth_ary[$row['cat_id'] . '-' . $row['group_id']] = [
				'cat_id'		=> $row['cat_id'],
				'group_id'		=> $row['group_id'],
				'auth_view'		=> $row['auth_view'],
				'auth_dl'		=> $row['auth_dl'],
				'auth_up'		=> $row['auth_up'],
				'auth_mod'		=> $row['auth_mod'],
				'auth_cread'	=> $row['auth_cread'],
				'auth_cpost'	=> $row['auth_cpost'],
			];
		}

		$this->db->sql_freeresult($result);

		// prepare permissions from special groups to insert into auth table
		foreach ($perms_data as $cat_id => $perm_group_data)
		{
			foreach ($perm_group_data as $group_id => $perm_data)
			{
				$insert_perms[$cat_id . '-' . $group_id] = [
					'cat_id'		=> $cat_id,
					'group_id'		=> $group_id,
					'auth_view'		=> (!empty($perm_data['auth_view'])) ? $perm_data['auth_view'] : self::DL_FALSE,
					'auth_dl'		=> (!empty($perm_data['auth_dl'])) ? $perm_data['auth_dl'] : self::DL_FALSE,
					'auth_up'		=> (!empty($perm_data['auth_up'])) ? $perm_data['auth_up'] : self::DL_FALSE,
					'auth_mod'		=> (!empty($perm_data['auth_mod'])) ? $perm_data['auth_mod'] : self::DL_FALSE,
					'auth_cread'	=> (!empty($perm_data['auth_cread'])) ? $perm_data['auth_cread'] : self::DL_FALSE,
					'auth_cpost'	=> (!empty($perm_data['auth_cpost'])) ? $perm_data['auth_cpost'] : self::DL_FALSE
				];
			}
		}

		// merge all permissions for insert
		$insert_perms = array_merge($insert_perms, $existing_auth_ary);

		// before inserting drop all permissions for special groups
		$sql = 'DELETE FROM ' . $this->table_prefix . 'dl_auth WHERE ' . $this->db->sql_in_set('group_id', $special_groups_ids);
		$this->db->sql_query($sql);

		foreach ($insert_perms as $sql_data)
		{
			$sql_insert_perms[] = $sql_data;
		}

		// at least save all new defined permissions
		$this->db->sql_multi_insert($this->table_prefix . 'dl_auth', $sql_insert_perms);
	}
}
