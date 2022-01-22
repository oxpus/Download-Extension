<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\controller\acp;

/**
 * @package acp
 */
class acp_stats_controller implements acp_stats_interface
{
	/* phpbb objects */
	protected $db;
	protected $user;
	protected $log;
	protected $config;
	protected $helper;
	protected $language;
	protected $request;
	protected $template;
	protected $pagination;

	/* extension owned objects */
	public $u_action;

	protected $dlext_format;
	protected $dlext_main;
	protected $dlext_constants;

	protected $dlext_table_dl_stats;
	protected $dlext_table_downloads;
	protected $dlext_table_dl_cat;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config					$config
	 * @param \phpbb\controller\helper				$helper
	 * @param \phpbb\language\language				$language
	 * @param \phpbb\request\request 				$request
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\pagination						$pagination
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\log\log_interface 				$log
	 * @param \phpbb\user							$user
	 * @param \oxpus\dlext\core\format 				$dlext_format
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\helpers\constants 	$dlext_constants
	 * @param string								$dlext_table_dl_stats
	 * @param string								$dlext_table_downloads
	 * @param string								$dlext_table_dl_cat
	 */
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $language,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\pagination $pagination,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\log\log_interface $log,
		\phpbb\user $user,
		\oxpus\dlext\core\format $dlext_format,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		$dlext_table_dl_stats,
		$dlext_table_downloads,
		$dlext_table_dl_cat
	)
	{
		$this->db						= $db;
		$this->log						= $log;
		$this->user						= $user;

		$this->config					= $config;
		$this->helper					= $helper;
		$this->language					= $language;
		$this->request					= $request;
		$this->template					= $template;
		$this->pagination				= $pagination;

		$this->dlext_table_dl_stats		= $dlext_table_dl_stats;
		$this->dlext_table_downloads	= $dlext_table_downloads;
		$this->dlext_table_dl_cat		= $dlext_table_dl_cat;

		$this->dlext_format				= $dlext_format;
		$this->dlext_main				= $dlext_main;
		$this->dlext_constants			= $dlext_constants;
	}

	public function set_action($u_action)
	{
		$this->u_action = $u_action;
	}

	public function handle()
	{
		$cancel				= $this->request->variable('cancel', '');
		$delete				= $this->request->variable('delete', '');
		$filter_string		= $this->request->variable('filter_string', '');
		$filtering			= $this->request->variable('filtering', '');
		$sort_order			= $this->request->variable('sort_order', '');
		$sorting			= $this->request->variable('sorting', '');
		$del_id				= $this->request->variable('del_id', [0]);
		$del_stat			= $this->request->variable('del_stat', 0);
		$show_guests		= $this->request->variable('show_guests', 0);
		$start				= $this->request->variable('start', 0);

		$index = $this->dlext_main->full_index();

		if (empty($index))
		{
			$this->u_action = str_replace('mode=stats', 'mode=assistant', $this->u_action);
			redirect($this->u_action);
		}

		unset($index);

		if ($cancel)
		{
			redirect($this->u_action);
		}

		$sorting = (!$sorting) ? 'username' : $sorting;
		$sql_order_dir = ($sort_order === '') ? 'ASC' : $sort_order;

		if ($delete)
		{
			if ($del_stat == $this->dlext_constants::DL_STATS_DEL_ALL)
			{
				if (confirm_box($this->dlext_constants::DL_TRUE))
				{
					$sql = 'DELETE FROM ' . $this->dlext_table_dl_stats;
					$this->db->sql_query($sql);

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_STATS_ALL');

					redirect($this->u_action);
				}
				else
				{
					$s_hidden_fields = ['delete' => 1, 'del_stat' => $del_stat];
					confirm_box($this->dlext_constants::DL_FALSE, 'DL_ACP_DROP_STATS', build_hidden_fields($s_hidden_fields));
				}
			}
			else if ($del_stat == $this->dlext_constants::DL_STATS_DEL_GUESTS)
			{
				if (confirm_box($this->dlext_constants::DL_TRUE))
				{
					$sql = 'DELETE FROM ' . $this->dlext_table_dl_stats . '
						WHERE user_id = ' . ANONYMOUS;
					$this->db->sql_query($sql);

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_STATS_ANONYM');

					redirect($this->u_action);
				}
				else
				{
					$s_hidden_fields = ['delete' => 1, 'del_stat' => $del_stat];
					confirm_box($this->dlext_constants::DL_FALSE, 'DL_ACP_DROP_STATS', build_hidden_fields($s_hidden_fields));
				}
			}
			else if (is_array($del_id) && !empty($del_id))
			{
				if (confirm_box($this->dlext_constants::DL_TRUE))
				{
					$dl_id = [];

					foreach ($del_id as $value)
					{
						$dl_id[] = (int) $value;
					}

					$sql = 'DELETE FROM ' . $this->dlext_table_dl_stats . '
						WHERE ' . $this->db->sql_in_set('dl_id', $dl_id);
					$this->db->sql_query($sql);

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_STATS_SOME');

					redirect($this->u_action);
				}
				else
				{
					$s_hidden_fields = ['delete' => 1];
					$i = 0;

					foreach ($del_id as $value)
					{
						$s_hidden_fields['del_id[' . $i . ']'] = $value;

						++$i;
					}

					confirm_box($this->dlext_constants::DL_FALSE, 'DL_ACP_DROP_STATS', build_hidden_fields($s_hidden_fields));
				}
			}
		}

		switch ($sorting)
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
			case 'time':
				$sql_order_by = 'time_stamp ' . $sql_order_dir;
				break;
			default:
				$sql_order_by = 'username ' . $sql_order_dir . ', time_stamp DESC';
		}

		switch ($filtering)
		{
			case 'cat':
				$search_filter_by = 'cat_name';
				$filter_by = 'cat';
				break;
			case 'id':
				$search_filter_by = 'description';
				$filter_by = 'id';
				break;
			case 'username':
				$search_filter_by = 'username';
				$filter_by = 'username';
				break;
			default:
				$search_filter_by = $filter_by = '';
		}

		$sql_where = '';

		if (!$show_guests)
		{
			$sql_where = ' s.user_id <> ' . ANONYMOUS;
		}

		$sql_array = [
			'SELECT'	=> 's.*, d.description, c.cat_name, u.user_colour',
			'FROM'		=> [$this->dlext_table_dl_stats => 's']
		];
		$sql_array['LEFT_JOIN'] = [];
		$sql_array['LEFT_JOIN'][] = [
			'FROM'		=> [$this->dlext_table_dl_cat => 'c'],
			'ON'		=> 'c.id = s.cat_id'
		];
		$sql_array['LEFT_JOIN'][] = [
			'FROM'		=> [$this->dlext_table_downloads => 'd'],
			'ON'		=> 'd.id = s.id'
		];
		$sql_array['LEFT_JOIN'][] = [
			'FROM'		=> [USERS_TABLE => 'u'],
			'ON'		=> 'u.user_id = s.user_id'
		];
		$sql_array['WHERE'] = $sql_where;

		$sql = $this->db->sql_build_query('SELECT', $sql_array);

		$result = $this->db->sql_query($sql);
		$total_data = $this->db->sql_affectedrows();

		if ($total_data)
		{
			$search_ids = [];
			$search_result = $this->dlext_constants::DL_FALSE;

			$filter_string = str_replace('*', '', str_replace('%', '', strtolower($filter_string)));

			if ($search_filter_by && $filter_string)
			{
				while ($row = $this->db->sql_fetchrow($result))
				{
					$sql_search_string = strtolower($row[$search_filter_by]);
					if (strpos($sql_search_string, $filter_string) !== false)
					{
						$search_ids[] = $row['dl_id'];
						$search_result = $this->dlext_constants::DL_TRUE;
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

			$page_data = (!empty($search_ids)) ? count($search_ids) : $total_data;

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

				$this->template->assign_vars([
					'DL_PAGE_NUMBER'	=> $this->pagination->on_page($page_data, $this->config['dl_links_per_page'], $start),
					'DL_TOTAL_DL'		=> $this->language->lang('DL_VIEW_DL_STATS', $page_data),
				]);
			}

			$sql = $this->db->sql_build_query('SELECT', $sql_array);

			$result = $this->db->sql_query_limit($sql, $this->config['dl_links_per_page'], $start);

			$i = 0;
			while ($row = $this->db->sql_fetchrow($result))
			{
				switch ($row['direction'])
				{
					case $this->dlext_constants::DL_STATS_FILE_UPLOAD:
						$direction = $this->language->lang('DL_UPLOAD_FILE');
						break;
					case $this->dlext_constants::DL_STATS_FILE_EDIT:
						$direction = $this->language->lang('DL_STAT_EDIT');
						break;
					default:
						$direction = $this->language->lang('DL_DOWNLOAD');
				}

				$this->template->assign_block_vars('dl_stat_row', [
					'DL_CAT_NAME'		=> $row['cat_name'],
					'DL_DESCRIPTION'	=> $row['description'],
					'DL_USERNAME'		=> ($row['user_id'] == ANONYMOUS) ? $this->language->lang('GUEST') : get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
					'DL_TRAFFIC'		=> ($row['traffic'] == $this->dlext_constants::DL_NONE) ? $this->language->lang('DL_EXTERN') : $this->dlext_format->dl_size($row['traffic']),
					'DL_DIRECTION'		=> $direction,
					'DL_USER_IP'		=> $row['user_ip'],
					'DL_TIME_STAMP'		=> $this->user->format_date($row['time_stamp']),
					'DL_TIME_STAMP_RFC'	=> gmdate(DATE_RFC3339, $row['time_stamp']),
					'DL_ID'				=> $row['dl_id'],

					'U_DL_CAT_LINK'		=> $this->helper->route('oxpus_dlext_index', ['cat' => $row['cat_id']]),
					'U_DL_LINK'			=> $this->helper->route('oxpus_dlext_details', ['df_id' => $row['id']]),
				]);

				++$i;
			}

			$this->db->sql_freeresult($result);

			$this->template->assign_var('S_DL_FILLED_FOOTER', $i);
		}
		else
		{
			$this->template->assign_var('S_DL_NO_DL_STAT_ROW', $this->dlext_constants::DL_TRUE);
		}

		$this->template->assign_vars([
			'DL_TOTAL_DATA'		=> $total_data,
			'DL_FILTER_STRING'	=> $filter_string,
			'DL_SELECT_NONE'	=> $this->dlext_constants::DL_PERM_GENERAL_NONE,

			'S_DL_SHOW_GUESTS'	=> ($show_guests) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_FILTER'		=> $filtering,
			'S_DL_SORT_ORDER'	=> $sorting,
			'S_DL_SORT_DIR'		=> $sort_order,

			'S_DL_FORM_ACTION'	=> $this->u_action,
		]);
	}
}
