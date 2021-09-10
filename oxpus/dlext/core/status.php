<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\core;

class status implements status_interface
{
	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames mini_status_icon

	/* phpbb objects */
	protected $language;
	protected $config;
	protected $user;

	/* extension owned objects */
	protected $dlext_auth;
	protected $dlext_cache;
	protected $dlext_main;
	protected $dlext_constants;

	protected $dl_file_p;
	protected $dl_file_icon;
	protected $dl_auth;
	protected $dl_index;

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language				$language
	 * @param \phpbb\config\config					$config
	 * @param \phpbb\user							$user
	 * @param \oxpus\dlext\core\auth				$dlext_auth
	 * @param \oxpus\dlext\core\cache				$dlext_cache
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 */
	public function __construct(
		\phpbb\language\language $language,
		\phpbb\config\config $config,
		\phpbb\user $user,
		\oxpus\dlext\core\auth $dlext_auth,
		\oxpus\dlext\core\cache $dlext_cache,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\helpers\constants $dlext_constants
	)
	{
		$this->language			= $language;
		$this->config 			= $config;
		$this->user 			= $user;

		$this->dlext_auth		= $dlext_auth;
		$this->dlext_cache 		= $dlext_cache;
		$this->dlext_main 		= $dlext_main;
		$this->dlext_constants 	= $dlext_constants;
	}

	public function mini_status_file($parent, $file_id, $rss = false)
	{
		$this->dl_file_icon = $this->dlext_cache->obtain_dl_files(intval($this->config['dl_new_time']), intval($this->config['dl_edit_time']));

		if (isset($this->dl_file_icon['new'][$parent][$file_id]) && $this->dl_file_icon['new'][$parent][$file_id] == $this->dlext_constants::DL_TRUE)
		{
			$mini_icon_img = ($rss) ? $this->language->lang('DL_FILE_NEW') : 'new';
		}
		else if (isset($this->dl_file_icon['edit'][$parent][$file_id]) && $this->dl_file_icon['edit'][$parent][$file_id] == $this->dlext_constants::DL_TRUE)
		{
			$mini_icon_img = ($rss) ? $this->language->lang('DL_FILE_EDIT') : 'edit';
		}
		else
		{
			$mini_icon_img = '';
		}

		return $mini_icon_img;
	}

	public function mini_status_cat($cur, $parent, $flag = 0)
	{
		if (empty($this->dl_index))
		{
			$this->dl_file_icon		= $this->dlext_cache->obtain_dl_files(intval($this->config['dl_new_time']), intval($this->config['dl_edit_time']));
			$this->dl_auth			= $this->dlext_auth->dl_auth();
			$this->dl_index			= $this->dlext_auth->dl_index();
		}

		$mini_status_icon[$cur]['new'] = 0;
		$mini_status_icon[$cur]['edit'] = 0;

		if (empty($this->dl_index))
		{
			return [];
		}

		foreach (array_keys($this->dl_index) as $cat_id)
		{
			if ($cat_id == $parent && !$flag)
			{
				if ((isset($this->dl_index[$cat_id]['auth_view']) && $this->dl_index[$cat_id]['auth_view']) || (isset($this->dl_auth[$cat_id]['auth_view'])))
				{
					if (isset($this->dl_index[$cat_id]['total']))
					{
						$new_sum = (isset($this->dl_file_icon['new_sum'][$cat_id])) ? intval($this->dl_file_icon['new_sum'][$cat_id]) : 0;
						$edit_sum = (isset($this->dl_file_icon['edit_sum'][$cat_id])) ? intval($this->dl_file_icon['edit_sum'][$cat_id]) : 0;

						$mini_status_icon[$cur]['new'] += $new_sum;
						$mini_status_icon[$cur]['edit'] += $edit_sum;
					}
				}

				$mini_icon = $this->mini_status_cat($cur, $cat_id, 1);
				$mini_status_icon[$cur]['new'] += $mini_icon[$cur]['new'];
				$mini_status_icon[$cur]['edit'] += $mini_icon[$cur]['edit'];
			}

			if ((isset($this->dl_index[$cat_id]['parent']) && $this->dl_index[$cat_id]['parent'] == $parent) && $flag)
			{
				if ((isset($this->dl_index[$cat_id]['auth_view']) && $this->dl_index[$cat_id]['auth_view']) || (isset($this->dl_auth[$cat_id]['auth_view'])))
				{
					if (isset($this->dl_index[$cat_id]['total']))
					{
						$new_sum = (isset($this->dl_file_icon['new_sum'][$cat_id])) ? intval($this->dl_file_icon['new_sum'][$cat_id]) : 0;
						$edit_sum = (isset($this->dl_file_icon['edit_sum'][$cat_id])) ? intval($this->dl_file_icon['edit_sum'][$cat_id]) : 0;

						$mini_status_icon[$cur]['new'] += $new_sum;
						$mini_status_icon[$cur]['edit'] += $edit_sum;
					}
				}

				$mini_icon = $this->mini_status_cat($cur, $cat_id, 1);
				$mini_status_icon[$cur]['new'] += $mini_icon[$cur]['new'];
				$mini_status_icon[$cur]['edit'] += $mini_icon[$cur]['edit'];
			}
		}

		return $mini_status_icon;
	}

	/**
	 * file_auth	Downlaod permission
	 * file_name	File name
	 * file_link	File download link
	 * file_status	File icon indicator
	 *	return [
	 *		'file_auth' 	=> $this->dlext_constants::DL_FALSE|$this->dlext_constants::DL_true,
	 *		'file_status'	=> 'red|blue|grey|white|yellow|green',
	 *  ];
	 */
	public function status($df_id)
	{
		if (empty($this->dl_file_p))
		{
			$this->dl_file_p		= $this->dlext_cache->obtain_dl_file_p();
			$this->dl_file_icon		= $this->dlext_cache->obtain_dl_files(intval($this->config['dl_new_time']), intval($this->config['dl_edit_time']));
		}

		if (!isset($this->dl_file_p[$df_id]['c']))
		{
			return [
				'file_auth' 	=> $this->dlext_constants::DL_FALSE,
				'file_status'	=> 'red',
			];
		}

		$cat_id			= $this->dl_file_p[$df_id]['c'];
		$cat_auth		= $this->dlext_auth->dl_cat_auth($cat_id);
		$index			= $this->dlext_main->full_index($cat_id);

		$file_auth		= $this->dlext_constants::DL_FALSE;
		$file_status	= 'red';

		if (!$this->config['dl_traffic_off'] && ($this->dlext_constants->get_value('users_traffics') || $this->dlext_constants->get_value('founder_traffics')))
		{
			if ($this->dlext_constants->get_value('founder_traffics'))
			{
				$file_status = 'yellow';
				$file_auth = $this->dlext_constants::DL_TRUE;
			}
			else if ($this->user->data['is_registered'] && intval($this->user->data['user_traffic']) >= $this->dl_file_p[$df_id]['s'] && !$this->dl_file_p[$df_id]['e'])
			{
				$file_status = 'yellow';
				$file_auth = $this->dlext_constants::DL_TRUE;
			}
			else if ($this->user->data['is_registered'] && intval($this->user->data['user_traffic']) < $this->dl_file_p[$df_id]['s'] && !$this->dl_file_p[$df_id]['e'])
			{
				$file_status = 'red';
				$file_auth = $this->dlext_constants::DL_FALSE;
			}
		}
		else
		{
			$file_status = 'green';
			$file_auth = $this->dlext_constants::DL_TRUE;
		}

		if ($this->user->data['user_posts'] < $this->config['dl_posts'] && !$this->dl_file_p[$df_id]['e'] && !$this->dl_file_p[$df_id]['f'])
		{
			$file_status = 'red';
			$file_auth = $this->dlext_constants::DL_FALSE;
		}

		if (!$this->user->data['is_registered'] && !$this->dl_file_p[$df_id]['e'] && !$this->dl_file_p[$df_id]['f'])
		{
			$file_status = 'red';
			$file_auth = $this->dlext_constants::DL_FALSE;
		}

		if ($this->dl_file_p[$df_id]['f'] == $this->dlext_constants::DL_FILE_FREE_ALL)
		{
			$file_status = 'green';
			$file_auth = $this->dlext_constants::DL_TRUE;
		}

		if ($this->dl_file_p[$df_id]['f'] == $this->dlext_constants::DL_FILE_FREE_REG_USER)
		{
			if (($this->config['dl_icon_free_for_reg'] && !$this->user->data['is_registered']) || (!$this->config['dl_icon_free_for_reg'] && $this->user->data['is_registered']))
			{
				$file_status = 'white';
			}

			if ($this->user->data['is_registered'] || $this->dlext_constants->get_value('founder_traffics'))
			{
				$file_auth = $this->dlext_constants::DL_TRUE;
			}
			else
			{
				$file_auth = $this->dlext_constants::DL_FALSE;
			}
		}

		if (!$cat_auth['auth_dl'] && !$index[$cat_id]['auth_dl'] && !$this->dlext_auth->user_admin())
		{
			$file_status = 'red';
			$file_auth = $this->dlext_constants::DL_FALSE;
		}

		if ($this->dl_file_p[$df_id]['t'] && $this->dl_file_p[$df_id]['k'] * $this->dl_file_p[$df_id]['s'] >= $this->dl_file_p[$df_id]['t'] && !$this->config['dl_traffic_off'])
		{
			$file_status = 'blue';

			if ($this->dlext_constants->get_value('founder_traffics'))
			{
				$file_auth = $this->dlext_constants::DL_TRUE;
			}
			else
			{
				$file_auth = $this->dlext_constants::DL_FALSE;
			}
		}

		if ($this->user->data['is_registered'])
		{
			$load_limit = $this->dlext_constants->get_value('overall_traffics');
			$overall_traffic = $this->config['dl_overall_traffic'];
			$remain_traffic = $this->config['dl_remain_traffic'];
		}
		else
		{
			$load_limit = $this->dlext_constants->get_value('guests_traffics');
			$overall_traffic = $this->config['dl_overall_guest_traffic'];
			$remain_traffic = $this->config['dl_remain_guest_traffic'];
		}

		if (($overall_traffic - (int) $remain_traffic <= $this->dl_file_p[$df_id]['s']) && !$this->config['dl_traffic_off'] && $load_limit == $this->dlext_constants::DL_TRUE)
		{
			$file_status = 'blue';

			if ($this->dlext_constants->get_value('founder_traffics'))
			{
				$file_auth = $this->dlext_constants::DL_TRUE;
			}
			else
			{
				$file_auth = $this->dlext_constants::DL_FALSE;
			}
		}

		if (($index[$cat_id]['cat_traffic'] && ($index[$cat_id]['cat_traffic'] - $index[$cat_id]['cat_traffic_use'] <= 0)) && !$this->config['dl_traffic_off'])
		{
			$file_status = 'blue';

			if ($this->dlext_constants->get_value('founder_traffics'))
			{
				$file_auth = $this->dlext_constants::DL_TRUE;
			}
			else
			{
				$file_auth = $this->dlext_constants::DL_FALSE;
			}
		}

		if ($this->dl_file_p[$df_id]['e'])
		{
			$file_status = 'grey';
			$file_auth = $this->dlext_constants::DL_TRUE;
		}

		return [
			'file_auth' 	=> $file_auth,
			'file_status'	=> $file_status,
		];
	}

	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames
}
