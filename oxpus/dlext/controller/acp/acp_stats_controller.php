<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\controller\acp;

use Symfony\Component\DependencyInjection\Container;

/**
* @package acp
*/
class acp_stats_controller implements acp_stats_interface
{
	public $u_action;
	public $db;
	public $user;
	public $auth;
	public $phpEx;
	public $phpbb_extension_manager;
	public $phpbb_container;
	public $phpbb_path_helper;
	public $phpbb_log;

	public $config;
	public $helper;
	public $language;
	public $request;
	public $template;
	public $pagination;

	public $ext_path;
	public $ext_path_web;
	public $ext_path_ajax;

	protected $dlext_format;

	/*
	 * @param string								$phpEx
	 * @param Container 							$phpbb_container
	 * @param \phpbb\extension\manager				$phpbb_extension_manager
	 * @param \phpbb\path_helper					$phpbb_path_helper
	 * @param \phpbb\db\driver\driver_interfacer	$db
	 * @param \phpbb\log\log_interface 				$log
	 * @param \phpbb\auth\auth						$auth
	 * @param \phpbb\user							$user
	 */
	public function __construct(
		$phpEx,
		Container $phpbb_container,
		\phpbb\extension\manager $phpbb_extension_manager,
		\phpbb\path_helper $phpbb_path_helper,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\log\log_interface $log,
		\phpbb\auth\auth $auth,
		\phpbb\user $user,
		$dlext_format
	)
	{
		$this->phpEx					= $phpEx;
		$this->phpbb_container			= $phpbb_container;
		$this->phpbb_extension_manager	= $phpbb_extension_manager;
		$this->phpbb_path_helper		= $phpbb_path_helper;
		$this->db						= $db;
		$this->phpbb_log				= $log;
		$this->auth						= $auth;
		$this->user						= $user;

		$this->config					= $this->phpbb_container->get('config');
		$this->helper					= $this->phpbb_container->get('controller.helper');
		$this->language					= $this->phpbb_container->get('language');
		$this->request					= $this->phpbb_container->get('request');
		$this->template					= $this->phpbb_container->get('template');
		$this->pagination				= $this->phpbb_container->get('pagination');

		$this->ext_path					= $this->phpbb_extension_manager->get_extension_path('oxpus/dlext', true);
		$this->ext_path_web				= $this->phpbb_path_helper->update_web_root_path($this->ext_path);
		$this->ext_path_ajax			= $this->ext_path_web . 'assets/javascript/';

		$this->dlext_format				= $dlext_format;
	}

	public function set_action($u_action)
	{
		$this->u_action = $u_action;
	}

	public function handle()
	{
		$this->auth->acl($this->user->data);
		if (!$this->auth->acl_get('a_dl_stats'))
		{
			trigger_error('DL_NO_PERMISSION', E_USER_WARNING);
		}

		include_once($this->ext_path . 'phpbb/includes/acm_init.' . $this->phpEx);

		if ($cancel)
		{
			redirect($this->u_action);
		}

		$sorting = (!$sorting) ? 'username' : $sorting;
		$sql_order_dir = ($sort_order === '') ? 'ASC' : $sort_order;
		
		if ($delete)
		{
			if ($del_stat == 1)
			{
				$sql = 'DELETE FROM ' . DL_STATS_TABLE;
				$this->db->sql_query($sql);
		
				$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_STATS_ALL');
			}
			else if ($del_stat == 2)
			{
				$sql = 'DELETE FROM ' . DL_STATS_TABLE . '
					WHERE user_id = ' . ANONYMOUS;
				$this->db->sql_query($sql);
		
				$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_STATS_ANONYM');
			}
			else if (is_array($del_id) && sizeof($del_id))
			{
				$dl_id = array();
				foreach($del_id as $key => $value)
				{
					$dl_id[] = (int) $value;
				}
		
				$sql = 'DELETE FROM ' . DL_STATS_TABLE . '
					WHERE ' . $this->db->sql_in_set('dl_id', $dl_id);
				$this->db->sql_query($sql);
		
				$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_STATS_SOME');
			}
		}
		
		switch($sorting)
		{
			case 'cat':
				$sql_order_by = 'cat_name ' . $sql_order_dir . ', time_stamp DESC';
				break;
		
			case 'id':
				$sql_order_by = 'description ' . $sql_order_dir . ', time_stamp DESC';
				break;
		
			case 'size':
				$sql_order_by = 'traffic ' . $sql_order_dir . ', time_stamp DESC';
				break;
		
			case 'ip':
				$sql_order_by = 'user_ip ' . $sql_order_dir . ', time_stamp DESC';
				break;
		
			case 'agent':
				$sql_order_by = 'browser ' . $sql_order_dir . ', time_stamp DESC';
				break;
		
			case 'time':
				$sql_order_by = 'time_stamp ' . $sql_order_dir;
				break;
		
			default:
				$sql_order_by = 'username ' . $sql_order_dir . ', time_stamp DESC';
		}
		
		$s_sort_order = '<select name="sorting">';
		$s_sort_order .= '<option value="username">' . $this->language->lang('USERNAME') . '</option>';
		$s_sort_order .= '<option value="id">' . $this->language->lang('DOWNLOADS') . '</option>';
		$s_sort_order .= '<option value="cat">' . $this->language->lang('DL_CAT_NAME') . '</option>';
		$s_sort_order .= '<option value="size">' . $this->language->lang('TRAFFIC') . '</option>';
		$s_sort_order .= '<option value="ip">' . $this->language->lang('DL_IP') . '</option>';
		$s_sort_order .= '<option value="agent">' . $this->language->lang('DL_BROWSER') . '</option>';
		$s_sort_order .= '<option value="time">' . $this->language->lang('TIME') . '</option>';
		$s_sort_order .= '</select>';
		$s_sort_order = str_replace('value="' . $sorting . '">', 'value="' . $sorting . '" selected="selected">', $s_sort_order);
		
		$s_sort_dir = '<select name="sort_order">';
		$s_sort_dir .= '<option value="ASC">' . $this->language->lang('ASCENDING') . '</option>';
		$s_sort_dir .= '<option value="DESC">' . $this->language->lang('DESCENDING') . '</option>';
		$s_sort_dir .= '</select>';
		$s_sort_dir = str_replace('value="' . $sort_order . '">', 'value="' . $sort_order . '" selected="selected">', $s_sort_dir);
		
		switch($filtering)
		{
			case 'cat':
				$search_filter_by = 'cat_name';
				$filter_by = 'cat';
				break;
		
			case 'id':
				$search_filter_by = 'description';
				$filter_by = 'id';
				break;
		
			case 'agent':
				$search_filter_by = 'browser';
				$filter_by = 'agent';
				break;
		
			case 'username':
				$search_filter_by = 'username';
				$filter_by = 'username';
				break;
		
			default:
				$search_filter_by = $filter_by = '';
		}
		
		$sql_where = '';
		
		$s_filter = '<select name="filtering">';
		$s_filter .= '<option value="-1">' . $this->language->lang('DL_NO_FILTER') . '</option>';
		$s_filter .= '<option value="username">' . $this->language->lang('USERNAME') . '</option>';
		$s_filter .= '<option value="id">' . $this->language->lang('DOWNLOADS') . '</option>';
		$s_filter .= '<option value="cat">' . $this->language->lang('DL_CAT_NAME') . '</option>';
		$s_filter .= '<option value="agent">' . $this->language->lang('DL_BROWSER') . '</option>';
		$s_filter .= '</select>';
		$s_filter = str_replace('value="' . $filtering . '">', 'value="' . $filtering . '" selected="selected">', $s_filter);
		
		$this->template->set_filenames(array(
			'stats' => 'dl_stats_admin_body.html')
		);
		
		if (!$show_guests)
		{
			$sql_where = ' s.user_id <> ' . ANONYMOUS;
		}
		
		$sql_array = array(
			'SELECT'	=> 's.*, d.description, c.cat_name, u.user_colour',
		
			'FROM'		=> array(DL_STATS_TABLE => 's'));
		
		$sql_array['LEFT_JOIN'] = array();
		$sql_array['LEFT_JOIN'][] = array(
			'FROM'		=> array(DL_CAT_TABLE => 'c'),
			'ON'		=> 'c.id = s.cat_id');
		$sql_array['LEFT_JOIN'][] = array(
			'FROM'		=> array(DOWNLOADS_TABLE => 'd'),
			'ON'		=> 'd.id = s.id');
		$sql_array['LEFT_JOIN'][] = array(
			'FROM'		=> array(USERS_TABLE => 'u'),
			'ON'		=> 'u.user_id = s.user_id');
		
		$sql_array['WHERE'] = $sql_where;
		
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		
		$result = $this->db->sql_query($sql);
		$total_data = $this->db->sql_affectedrows($result);
		
		if ($total_data)
		{
			$search_ids = array();
			$search_result = false;
		
			$filter_string = str_replace('*', '', str_replace('%', '', strtolower($filter_string)));
		
			if ($search_filter_by && $filter_string)
			{
				while ($row = $this->db->sql_fetchrow($result))
				{
					$sql_search_string = strtolower($row[$search_filter_by]);
					if (strpos($sql_search_string, $filter_string) !== false)
					{
						$search_ids[] = $row['dl_id'];
						$search_result = true;
					}
				}
			}
		
			$this->db->sql_freeresult($result);
		
			if ($search_filter_by && $filter_string && $search_result)
			{
				$sql_array['WHERE'] .= (($sql_where) ? ' AND ' : '') . $this->db->sql_in_set('s.dl_id', $search_ids);
			}
		
			$sql_array['ORDER_BY'] = $sql_order_by;
		
			if ($start >= $total_data && $start >= $this->config['dl_links_per_page'])
			{
				$start -= $this->config['dl_links_per_page'];
			}
		
			$page_data = (sizeof($search_ids)) ? sizeof($search_ids) : $total_data;
		
			if ($page_data > $this->config['dl_links_per_page'])
			{
				$this->pagination->generate_template_pagination(
					$this->u_action . '&amp;sorting=' . $sorting . '&amp;sort_order=' . $sort_order . '&amp;show_guests=' . $show_guests . '&amp;filtering=' . $filter_by . '&amp;filter_string=' . $filter_string,
					'pagination',
					'start',
					$page_data,
					$this->config['dl_links_per_page'],
					$start
				);
					
				$this->template->assign_vars(array(
					'PAGE_NUMBER'	=> $this->pagination->on_page($page_data, $this->config['dl_links_per_page'], $start),
					'TOTAL_DL'		=> $this->language->lang('VIEW_DL_STATS', $page_data),
				));
			}

			$sql = $this->db->sql_build_query('SELECT', $sql_array);
		
			$result = $this->db->sql_query_limit($sql, $this->config['dl_links_per_page'], $start);
		
			$i = 0;
			while ($row = $this->db->sql_fetchrow($result))
			{
				switch ($row['direction'])
				{
					case 1:
						$direction = $this->language->lang('DL_UPLOAD_FILE');
					break;
		
					case 2:
						$direction = $this->language->lang('DL_STAT_EDIT');
					break;
		
					default:
						$direction = $this->language->lang('DL_DOWNLOAD');
				}
		
				$this->template->assign_block_vars('dl_stat_row', array(
					'CAT_NAME'			=> $row['cat_name'],
					'DESCRIPTION'		=> $row['description'],
					'USERNAME'			=> ($row['user_id'] == ANONYMOUS) ? $this->language->lang('GUEST') : get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
					'TRAFFIC'			=> ($row['traffic'] == -1) ? $this->language->lang('DL_EXTERN') : $this->dlext_format->dl_size($row['traffic']),
					'DIRECTION'			=> $direction,
					'USER_IP'			=> $row['user_ip'],
					'BROWSER'			=> $row['browser'],
					'TIME_STAMP'		=> $this->user->format_date($row['time_stamp']),
					'TIME_STAMP_RFC'	=> gmdate(DATE_RFC3339, $row['time_stamp']),
					'ID'				=> $row['dl_id'],
		
					'U_CAT_LINK'		=> $this->helper->route('oxpus_dlext_index', array('cat' => $row['cat_id'])),
					'U_DL_LINK'			=> $this->helper->route('oxpus_dlext_details', array('df_id' => $row['id'])),
				));
		
				$i++;
			}
		
			$this->db->sql_freeresult($result);
		
			$this->template->assign_var('S_FILLED_FOOTER', true);
		}
		else
		{
			$this->template->assign_var('S_NO_DL_STAT_ROW', true);
		}
		
		$this->template->assign_vars(array(
			'TOTAL_DATA'		=> $total_data,
			'FILTER_STRING'		=> $filter_string,
		
			'S_FILTER'			=> $s_filter,
			'S_SHOW_GUESTS'		=> ($show_guests) ? 'checked="checked"' : '',
			'S_FORM_ACTION'		=> $this->u_action,
			'S_SORT_ORDER'		=> $s_sort_order,
			'S_SORT_DIR'		=> $s_sort_dir)
		);
	}
}
