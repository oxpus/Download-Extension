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

class dlext_main implements dlext_main_interface
{
	/* @var \phpbb\db\driver\driver_interface */
	protected $db;

	/* @var \phpbb\controller\helper */
	protected $helper;

	protected $dlext_auth;
	protected $dlext_init;
	protected $dl_auth;
	protected $dl_index;
	protected $user_admin;

	/**
	* Constructor
	*
	* @param Container 								$phpbb_container

	* @param \phpbb\db\driver\driver_interfacer		$db
	* @param \phpbb\controller\helper				$helper
	*/
	public function __construct(
		Container $phpbb_container,

		\phpbb\db\driver\driver_interface $db,
		\phpbb\controller\helper $helper,
		$dlext_auth,
		$dlext_init
		)
	{
		$this->db 			= $db;
		$this->helper 		= $helper;

		$this->dlext_auth	= $dlext_auth;
		$this->dlext_init	= $dlext_init;

        $this->dl_auth      = $this->dlext_auth->dl_auth();
        $this->dl_index     = $this->dlext_auth->dl_index();
        $this->user_admin   = $this->dlext_auth->user_admin();
	}

	public function full_index($only_cat = 0, $parent = 0, $level = 0, $auth_level = 0, &$tree_dl = array())
	{
		if (!is_array($this->dl_index) || !sizeof($this->dl_index))
		{
			return array();
		}

		if ($only_cat > 0)
		{
			$tree_dl[$only_cat] = $this->dl_index[$only_cat];
			$tree_dl[$only_cat]['nav_path'] = $this->helper->route('oxpus_dlext_index', array('cat' => $only_cat));
			$tree_dl[$only_cat]['cat_path'] = $this->dl_index[$only_cat]['path'];
			$tree_dl[$only_cat]['cat_name_nav'] = $this->dl_index[$only_cat]['cat_name'];
		}
		else
		{
			if ($auth_level)
			{
				unset($access_ids);
				$access_ids = array();
			}

			foreach($this->dl_index as $cat_id => $value)
			{
				if ((isset($value['auth_view']) && $value['auth_view']) || (isset($this->dl_auth[$cat_id]['auth_view']) && $this->dl_auth[$cat_id]['auth_view']) || $this->user_admin)
				{
					/*
					* $auth level will return the following data
					* 0 = Default values for each category
					* 1 = IDs from all viewable categories
					* 2 = IDs from moderated categories
					* 3 = IDs from upload categories
					*/

					if ($auth_level == 1 && isset($value['id']) && $value['id'] && ($value['auth_view'] || (isset($this->dl_auth[$cat_id]['auth_view']) && $this->dl_auth[$cat_id]['auth_view']) || $this->user_admin))
					{
						$access_ids[] = $cat_id;
					}
					else if ($auth_level == 2 && isset($value['id']) && $value['id'] && ((isset($value['auth_mod']) && $value['auth_mod']) || (isset($this->dl_auth[$cat_id]['auth_mod']) && $this->dl_auth[$cat_id]['auth_mod']) || $this->user_admin))
					{
						$access_ids[] = $cat_id;
					}
					else if ($auth_level == 3 && isset($value['id']) && $value['id'] && ((isset($value['auth_up']) && $value['auth_up']) || (isset($this->dl_auth[$cat_id]['auth_up']) && $this->dl_auth[$cat_id]['auth_up']) || $this->user_admin))
					{
						$access_ids[] = $cat_id;
					}
					else if (isset($value['parent']) && $value['parent'] == $parent)
					{
						$seperator = '';
						for ($i = 0; $i < $level; $i++)
						{
							$seperator .= ($value['parent'] != 0) ? '&nbsp;&nbsp;|___&nbsp;' : '';
						}

						$tree_dl[$cat_id] = $value;
						$tree_dl[$cat_id]['nav_path'] = $this->helper->route('oxpus_dlext_index', array('cat' => $cat_id));
						$tree_dl[$cat_id]['cat_path'] = $value['path'];
						$tree_dl[$cat_id]['cat_name'] = $seperator . $value['cat_name'];
						$tree_dl[$cat_id]['cat_name_nav'] = $value['cat_name'];

						$level++;
						$tree_dl = $this->full_index(0, $cat_id, $level, 0, $tree_dl);
						$level--;
					}
				}
			}
		}

		return (isset($auth_level) && $auth_level <> 0) ? $access_ids : $tree_dl;
	}

	public function index($parent = 0)
	{
		if (!is_array($this->dl_index) || !sizeof($this->dl_index))
		{
			return array();
		}

		$tree_dl = array();

		foreach($this->dl_index as $cat_id => $value)
		{
			if (((isset($value['auth_view']) && $value['auth_view']) || (isset($this->dl_auth[$cat_id]['auth_view']) && $this->dl_auth[$cat_id]['auth_view']) || $this->user_admin) && (isset($value['parent']) && $value['parent'] == $parent))
			{
				$tree_dl[$cat_id] = $value;
				$tree_dl[$cat_id]['nav_path'] = $this->helper->route('oxpus_dlext_index', array('cat' => $cat_id));
				$tree_dl[$cat_id]['cat_path'] = $value['path'];
				$tree_dl[$cat_id]['cat_name_nav'] = $value['cat_name'];
				$tree_dl[$cat_id]['sublevel'] = $this->get_sublevel($cat_id);
			}
		}
		return $tree_dl;
	}

	public function get_sublevel($parent = 0)
	{
		if (!is_array($this->dl_index) || !sizeof($this->dl_index))
		{
			return array();
		}

		$sublevel = array();
		$i = 0;

		foreach($this->dl_index as $cat_id => $value)
		{
			if (((isset($this->dl_index[$cat_id]['auth_view']) && $this->dl_index[$cat_id]['auth_view']) || (isset($this->dl_auth[$cat_id]['auth_view']) && $this->dl_auth[$cat_id]['auth_view']) || $this->user_admin) && (isset($this->dl_index[$cat_id]['parent']) && $this->dl_index[$cat_id]['parent'] == $parent))
			{
				$sublevel['cat_name'][$i] = $this->dl_index[$cat_id]['cat_name'];
				$sublevel['total'][$i] = (isset($this->dl_index[$cat_id]['total'])) ? $this->dl_index[$cat_id]['total'] : 0;
				$sublevel['cat_id'][$i] = $this->dl_index[$cat_id]['id'];
				$sublevel['cat_path'][$i] = $this->helper->route('oxpus_dlext_index', array('cat' => $cat_id));
				$sublevel['cat_sub'][$i] = $cat_id;

				$sublevel['description'][$i] = (isset($this->dl_index[$cat_id]['description'])) ? $this->dl_index[$cat_id]['description'] : '';
				$sublevel['desc_uid'][$i] = (isset($this->dl_index[$cat_id]['desc_uid'])) ? $this->dl_index[$cat_id]['desc_uid'] : '';
				$sublevel['desc_bitfield'][$i] = (isset($this->dl_index[$cat_id]['desc_bitfield'])) ? $this->dl_index[$cat_id]['desc_bitfield'] : '';
				$sublevel['desc_flags'][$i] = (isset($this->dl_index[$cat_id]['desc_flags'])) ? $this->dl_index[$cat_id]['desc_flags'] : '';
				$i++;
			}
		}

		return $sublevel;
	}

	public function get_sublevel_count($parent = 0)
	{
		if (!is_array($this->dl_index) || !sizeof($this->dl_index))
		{
			return 0;
		}

		$sublevel_count = 0;

		foreach($this->dl_index as $cat_id => $value)
		{
			if (isset($this->dl_index[$cat_id]['parent']) && $this->dl_index[$cat_id]['parent'] == $parent && (isset($this->dl_index[$cat_id]['auth_view']) || isset($this->dl_auth[$cat_id]['auth_view']) || $this->user_admin))
			{
				$sublevel_count += (isset($this->dl_index[$cat_id]['total'])) ? $this->dl_index[$cat_id]['total'] : 0;
				$sublevel_count += $this->get_sublevel_count($cat_id);
			}
		}

		return $sublevel_count;
	}

	public function count_sublevel($parent)
	{
		if (!is_array($this->dl_index) || !sizeof($this->dl_index))
		{
			return 0;
		}

		$sublevel = 0;

		foreach($this->dl_index as $cat_id => $value)
		{
			if ((isset($this->dl_index[$cat_id]['auth_view']) || isset($this->dl_auth[$cat_id]['auth_view']) || $this->user_admin) && (isset($this->dl_index[$cat_id]['parent']) && $this->dl_index[$cat_id]['parent'] == $parent))
			{
				$sublevel++;
			}
		}

		return $sublevel;
	}

	public function find_latest_dl($last_data, $parent, $main_cat, $last_dl_time)
	{
		foreach($last_data as $cat_id => $value)
		{
			if ($last_data[$cat_id]['parent'] == $parent || $main_cat == $cat_id)
			{
				$last_cat_time = (isset($last_data[$cat_id]['change_time'])) ? $last_data[$cat_id]['change_time'] : 0;
				$last_dl_times = (isset($last_dl_time['change_time'])) ? $last_dl_time['change_time'] : 0;

				if ($last_cat_time > $last_dl_times && ((isset($this->dl_index[$cat_id]['auth_view']) && $this->dl_index[$cat_id]['auth_view']) || (isset($this->dl_auth[$cat_id]['auth_view']) && $this->dl_auth[$cat_id]['auth_view']) || $this->user_admin))
				{
					$last_dl_time['change_time'] = $last_cat_time;
					$last_dl_time['cat_id'] = $cat_id;
				}

				$last_temp = $this->find_latest_dl($last_data, $cat_id, -1, $last_dl_time);
				$last_temp_time = (isset($last_temp['change_time'])) ? $last_temp['change_time'] : 0;
				$last_dl_times = (isset($last_dl_time['change_time'])) ? $last_dl_time['change_time'] : 0;

				if ($last_temp_time > $last_dl_times)
				{
					$last_dl_time['change_time'] = $last_temp['change_time'];
					$last_dl_time['cat_id'] = $last_temp['cat_id'];
				}
			}
		}

		return $last_dl_time;
	}

	public function dl_prune_stats($cat_id, $stats_prune)
	{
		$stats_prune--;

		if ($stats_prune)
		{
			$sql = 'SELECT time_stamp FROM ' . DL_STATS_TABLE . '
				    WHERE cat_id = ' . (int) $cat_id . '
				    ORDER BY time_stamp DESC';
			$result = $this->db->sql_query_limit($sql, 1, $stats_prune);
			$first_time_stamp = $this->db->sql_fetchfield('time_stamp');
			$this->db->sql_freeresult($result);

			if ($first_time_stamp)
			{
				$sql = 'DELETE FROM ' . DL_STATS_TABLE . '
					    WHERE time_stamp <= ' . (int) $first_time_stamp . '
						    AND cat_id = ' . (int) $cat_id;
				$this->db->sql_query($sql);
			}
		}

		return true;
	}
}
