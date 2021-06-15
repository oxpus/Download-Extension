<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2021 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\controller\mcp;

class mcp_broken
{
	/* phpbb objects */
	protected $notification;
	protected $pagination;
	protected $db;
	protected $config;
	protected $helper;
	protected $request;
	protected $template;
	protected $language;

	/* extension owned objects */
	protected $dlext_auth;
	protected $dlext_main;
	protected $dlext_constants;
	protected $dlext_footer;

	protected $dlext_table_downloads;

	/**
	* Constructor
	*
	* @param \phpbb\notification\manager 			$notification
	* @param \phpbb\pagination						$pagination
	* @param \phpbb\db\driver\driver_interface		$db
	* @param \phpbb\config\config					$config
	* @param \phpbb\controller\helper				$helper
	* @param \phpbb\request\request 				$request
	* @param \phpbb\template\template				$template
	* @param \phpbb\language\language				$language
	* @param \oxpus\dlext\core\auth					$dlext_auth
	* @param \oxpus\dlext\core\main					$dlext_main
	* @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	* @param \oxpus\dlext\core\helpers\footer		$dlext_footer
	* @param string									$dlext_table_downloads
	*/
	public function __construct(
		\phpbb\notification\manager $notification,
		\phpbb\pagination $pagination,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\language\language $language,
		\oxpus\dlext\core\auth $dlext_auth,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		\oxpus\dlext\core\helpers\footer $dlext_footer,
		$dlext_table_downloads
	)
	{
		$this->notification 			= $notification;
		$this->pagination 				= $pagination;
		$this->db 						= $db;
		$this->config 					= $config;
		$this->helper 					= $helper;
		$this->request					= $request;
		$this->template 				= $template;
		$this->language					= $language;

		$this->dlext_table_downloads	= $dlext_table_downloads;

		$this->dlext_auth				= $dlext_auth;
		$this->dlext_main				= $dlext_main;
		$this->dlext_constants			= $dlext_constants;
		$this->dlext_footer				= $dlext_footer;
	}

	public function handle()
	{
		$access_cat = $this->dlext_main->full_index(0, 0, 0, $this->dlext_constants::DL_AUTH_CHECK_MOD);

		if (empty($access_cat))
		{
			trigger_error($this->language->lang('DL_NO_PERMISSION'));
		}

		$this->template->assign_vars([
			'DL_MCP_TAB_MODULE'		=> $this->language->lang('DL_MODCP_QUEUE'),

			'S_DL_MCP'				=> $this->dlext_constants::DL_TRUE,
			'S_DL_MCP_TAB_BROKEN'	=> $this->dlext_constants::DL_TRUE,
		]);

		$index		= $this->dlext_main->full_index();

		$dlo_id		= $this->request->variable('dlo_id', [0 => 0]);
		$start		= $this->request->variable('start', 0);

		add_form_key('dl_modcp');

		if (!empty($dlo_id))
		{
			if (!check_form_key('dl_modcp'))
			{
				trigger_error('FORM_INVALID');
			}

			$sql = 'UPDATE ' . $this->dlext_table_downloads . ' SET ' . $this->db->sql_build_array('UPDATE', [
				'broken' => 0]) . ' WHERE ' . $this->db->sql_in_set('id', $dlo_id);
			$this->db->sql_query($sql);

			$this->notification->delete_notifications('oxpus.dlext.notification.type.broken', $dlo_id);
		}

		$sql_access_cats = ($this->dlext_auth->user_admin()) ? '' : ' AND ' . $this->db->sql_in_set('cat', $access_cat);

		$sql = 'SELECT COUNT(id) AS total FROM ' . $this->dlext_table_downloads . "
			WHERE broken = 1
				$sql_access_cats";
		$result = $this->db->sql_query($sql);
		$total_broken = $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		$sql = 'SELECT cat, id, description, desc_uid, desc_bitfield, desc_flags, broken FROM ' . $this->dlext_table_downloads . "
			WHERE broken = 1
				$sql_access_cats
			ORDER BY cat, description";
		$result = $this->db->sql_query_limit($sql, $this->config['dl_links_per_page'], $start);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$cat_id		= $row['cat'];
			$cat_name	= $index[$cat_id]['cat_name_nav'];
			$cat_view	= $index[$cat_id]['nav_path'];

			$description	= $row['description'];
			$desc_uid		= $row['desc_uid'];
			$desc_bitfield	= $row['desc_bitfield'];
			$desc_flags		= $row['desc_flags'];
			$description	= generate_text_for_display($description, $desc_uid, $desc_bitfield, $desc_flags);

			$file_id 	= $row['id'];
			$broken		= $row['broken'];

			$this->template->assign_block_vars('broken_row', [
				'DL_CAT_NAME'		=> $cat_name,
				'DL_FILE_ID'		=> $file_id,
				'DL_DESCRIPTION'	=> $description,
				'DL_BROKEN'			=> $broken,

				'U_DL_CAT_VIEW'		=> $cat_view,
				'U_DL_EDIT'			=> $this->helper->route('oxpus_dlext_mcp_edit', ['df_id' => $file_id, 'cat_id' => $cat_id, 'modcp' => 1]),
				'U_DL_DELETE'		=> $this->helper->route('oxpus_dlext_mcp_manage', ['view' => 'toolbox', 'delete' => 1, 'dlo_id[0]' => $file_id, 'cat_id' => $cat_id, 'modcp' => $this->dlext_constants::DL_RETURN_MCP_APPROVE]),
				'U_DL_DOWNLOAD'		=> $this->helper->route('oxpus_dlext_details', ['df_id' => $file_id, 'cat_id' => $cat_id, 'modcp' => 1]),
			]);
		}

		$this->db->sql_freeresult($result);

		$s_hidden_fields = [
			'start'		=> $start
		];

		if ($total_broken > $this->config['dl_links_per_page'])
		{
			$this->pagination->generate_template_pagination(
				$this->helper->route('oxpus_dlext_mcp_broken'),
				'pagination',
				'start',
				$total_broken,
				$this->config['dl_links_per_page'],
				$start
			);

			$this->template->assign_vars([
				'DL_AGE_NUMBER'	=> $this->pagination->on_page($total_broken, $this->config['dl_links_per_page'], $start),
				'DL_TOTAL_DL'	=> $this->language->lang('DL_VIEW_DOWNLOADS_NUM', $total_broken),
			]);
		}

		$this->template->assign_vars([
			'S_DL_MODCP_ACTION'		=> $this->helper->route('oxpus_dlext_mcp_broken'),
			'S_DL_HIDDEN_FIELDS'	=> build_hidden_fields($s_hidden_fields),
		]);

		/*
		* include the mod footer
		*/
		$this->dlext_footer->set_parameter('mcp');
		$this->dlext_footer->handle();

		return $this->helper->render('@oxpus_dlext/mcp/dl_mcp_broken.html', $this->language->lang('MCP'));
	}
}
