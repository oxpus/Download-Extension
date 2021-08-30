<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\controller\mcp;

class mcp_capprove
{
	/* phpbb objects */
	protected $root_path;
	protected $php_ext;
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

	protected $dlext_table_dl_comments;
	protected $dlext_table_downloads;

	/**
	 * Constructor
	 *
	 * @param string								$root_path
	 * @param string								$php_ext
	 * @param \phpbb\notification\manager 			$notification
	 * @param \phpbb\pagination						$pagination
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\config\config					$config
	 * @param \phpbb\controller\helper				$helper
	 * @param \phpbb\request\request 				$request
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\language\language				$language
	 * @param \oxpus\dlext\core\auth				$dlext_auth
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param \oxpus\dlext\core\helpers\footer		$dlext_footer
	 * @param string								$dlext_table_dl_comments
	 * @param string								$dlext_table_downloads
	 * @param string								$dlext_constants
	 */
	public function __construct(
		$root_path,
		$php_ext,
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
		$dlext_table_dl_comments,
		$dlext_table_downloads
	)
	{
		$this->root_path				= $root_path;
		$this->php_ext 					= $php_ext;
		$this->notification 			= $notification;
		$this->pagination 				= $pagination;
		$this->db 						= $db;
		$this->config 					= $config;
		$this->helper 					= $helper;
		$this->request					= $request;
		$this->template 				= $template;
		$this->language					= $language;

		$this->dlext_table_dl_comments	= $dlext_table_dl_comments;
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
			'S_DL_MCP_TAB_CAPPROVE'	=> $this->dlext_constants::DL_TRUE,
		]);

		$index		= $this->dlext_main->full_index();

		$cat_id		= $this->request->variable('cat_id', 0);
		$dlo_id		= $this->request->variable('dlo_id', [0 => 0]);
		$delete		= $this->request->variable('delete', '');
		$action		= $this->request->variable('action', '');
		$start		= $this->request->variable('start', 0);

		$action = ($delete) ? 'delete' : $action;

		add_form_key('dl_modcp');

		if ($action == 'delete')
		{
			if (!empty($dlo_id))
			{
				if (!check_form_key('dl_modcp'))
				{
					trigger_error('FORM_INVALID');
				}

				$sql = 'DELETE FROM ' . $this->dlext_table_dl_comments . '
					WHERE ' . $this->db->sql_in_set('dl_id', $dlo_id);
				$this->db->sql_query($sql);

				$this->notification->delete_notifications([
					'oxpus.dlext.notification.type.capprove',
					'oxpus.dlext.notification.type.comments',
				], $dlo_id);
			}

			$dlo_id = [];
		}

		if (!empty($dlo_id))
		{
			if (!check_form_key('dl_modcp'))
			{
				trigger_error('FORM_INVALID');
			}

			$sql = 'UPDATE ' . $this->dlext_table_dl_comments . ' SET ' . $this->db->sql_build_array('UPDATE', [
				'approve' => $this->dlext_constants::DL_TRUE
			]) . ' WHERE ' . $this->db->sql_in_set('dl_id', $dlo_id);
			$this->db->sql_query($sql);

			$this->notification->delete_notifications('oxpus.dlext.notification.type.capprove', $dlo_id);
		}

		$sql_access_cats = ($this->dlext_auth->user_admin()) ? '' : ' AND ' . $this->db->sql_in_set('c.cat_id', $access_cat);

		$sql = 'SELECT COUNT(c.dl_id) AS total FROM ' . $this->dlext_table_dl_comments . " c
			WHERE c.approve = 0
				$sql_access_cats";
		$result = $this->db->sql_query($sql);
		$total_approve = $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		$sql = 'SELECT d.cat, d.id, d.description, d.desc_uid, d.desc_bitfield, d.desc_flags, c.comment_text, c.com_uid, c.com_bitfield, c.com_flags, c.user_id, c.username, c.dl_id FROM ' . $this->dlext_table_downloads . ' d, ' . $this->dlext_table_dl_comments . " c
			WHERE d.id = c.id
				AND c.approve = 0
				$sql_access_cats
			ORDER BY d.cat, d.description";
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

			$comment_text	= $row['comment_text'];
			$com_uid		= $row['com_uid'];
			$com_bitfield	= $row['com_bitfield'];
			$com_flags		= $row['com_flags'];
			$comment_text	= generate_text_for_display($comment_text, $com_uid, $com_bitfield, $com_flags);

			$file_id		= $row['id'];
			$comment_id		= $row['dl_id'];

			$comment_user_id	= $row['user_id'];
			$comment_username	= $row['username'];
			$comment_user_link	= ($comment_user_id != ANONYMOUS) ? append_sid($this->root_path . 'memberlist.' . $this->php_ext, "mode=viewprofile&amp;u=$comment_user_id") : '';

			$this->template->assign_block_vars('approve_row', [
				'DL_CAT_NAME'			=> $cat_name,
				'DL_FILE_ID'			=> $file_id,
				'DL_DESCRIPTION'		=> $description,
				'DL_COMMENT_USERNAME'	=> $comment_username,
				'DL_COMMENT_TEXT'		=> $comment_text,
				'DL_COMMENT_ID'			=> $comment_id,

				'U_DL_CAT_VIEW'			=> $cat_view,
				'U_DL_USER_LINK'		=> $comment_user_link,
				'U_DL_EDIT'				=> $this->helper->route('oxpus_dlext_details', ['view' => 'comment', 'action' => 'edit', 'df_id' => $file_id, 'cat_id' => $cat_id, 'dl_id' => $comment_id]),
				'U_DL_DOWNLOAD'			=> $this->helper->route('oxpus_dlext_details', ['df_id' => $file_id]),
			]);
		}
		$this->db->sql_freeresult($result);

		$s_hidden_fields = [
			'cat_id'	=> $cat_id,
			'start'		=> $start
		];

		if ($total_approve > $this->config['dl_links_per_page'])
		{
			$this->pagination->generate_template_pagination(
				$this->helper->route('oxpus_dlext_mcp_capprove', ['cat_id' => $cat_id]),
				'pagination',
				'start',
				$total_approve,
				$this->config['dl_links_per_page'],
				$start
			);

			$this->template->assign_vars([
				'DL_PAGE_NUMBER'	=> $this->pagination->on_page($total_approve, $this->config['dl_links_per_page'], $start),
				'DL_TOTAL_DL'		=> $this->language->lang('DL_VIEW_DOWNLOADS_NUM', $total_approve),
			]);
		}

		$this->template->assign_vars([
			'S_DL_MODCP_ACTION'		=> $this->helper->route('oxpus_dlext_mcp_capprove'),
			'S_DL_HIDDEN_FIELDS'	=> build_hidden_fields($s_hidden_fields),
		]);

		/*
		* include the mod footer
		*/
		$this->dlext_footer->set_parameter('mcp');
		$this->dlext_footer->handle();

		return $this->helper->render('@oxpus_dlext/mcp/dl_mcp_capprove.html', $this->language->lang('MCP'));
	}
}
