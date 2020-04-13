<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\controller\mcp;

use Symfony\Component\DependencyInjection\Container;

class mcp_capprove
{
	/* @var string phpBB root path */
	protected $root_path;

	/* @var string phpEx */
	protected $php_ext;

	/* @var Container */
	protected $phpbb_container;

	/* @var \phpbb\extension\manager */
	protected $phpbb_extension_manager;

	/* @var \phpbb\path_helper */
	protected $phpbb_path_helper;

	/* @var \phpbb\db\driver\driver_interface */
	protected $db;

	/* @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\auth\auth */
	protected $auth;

	/* @var \phpbb\request\request_interface */
	protected $request;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\user */
	protected $user;

	/* @var \phpbb\language\language */
	protected $language;

	/** @var extension owned objects */
	protected $ext_path;
	protected $ext_path_web;
	protected $ext_path_ajax;

	protected $dlext_auth;
	protected $dlext_main;

	/**
	* Constructor
	*
	* @param string									$root_path
	* @param string									$php_ext
	* @param Container 								$phpbb_container
	* @param \phpbb\extension\manager				$phpbb_extension_manager
	* @param \phpbb\path_helper						$phpbb_path_helper
	* @param \phpbb\db\driver\driver_interfacer		$db
	* @param \phpbb\config\config					$config
	* @param \phpbb\controller\helper				$helper
	* @param \phpbb\auth\auth						$auth
	* @param \phpbb\request\request_interface 		$request
	* @param \phpbb\template\template				$template
	* @param \phpbb\user							$user
	* @param \phpbb\language\language				$language
	*/
	public function __construct(
		$root_path,
		$php_ext,
		Container $phpbb_container,
		\phpbb\extension\manager $phpbb_extension_manager,
		\phpbb\path_helper $phpbb_path_helper,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\auth\auth $auth,
		\phpbb\request\request_interface $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\language\language $language,
		$dlext_auth,
		$dlext_main
	)
	{
		$this->root_path				= $root_path;
		$this->php_ext 					= $php_ext;
		$this->phpbb_container 			= $phpbb_container;
		$this->phpbb_extension_manager 	= $phpbb_extension_manager;
		$this->phpbb_path_helper		= $phpbb_path_helper;
		$this->db 						= $db;
		$this->config 					= $config;
		$this->helper 					= $helper;
		$this->auth						= $auth;
		$this->request					= $request;
		$this->template 				= $template;
		$this->user 					= $user;
		$this->language					= $language;

		$this->ext_path					= $this->phpbb_extension_manager->get_extension_path('oxpus/dlext', true);
		$this->ext_path_web				= $this->phpbb_path_helper->update_web_root_path($this->ext_path);
		$this->ext_path_ajax			= $this->ext_path_web . 'assets/javascript/';

		$this->dlext_auth				= $dlext_auth;
		$this->dlext_main				= $dlext_main;
	}

	public function handle()
	{
		$nav_view = 'modcp';
		$modcp_mode = 'capprove';

		// Include the default base init script
		include_once($this->ext_path . 'phpbb/includes/base_init.' . $this->php_ext);

		$action = ($delete) ? 'delete' : $action;

		unset($dl_index);

		if (!$deny_modcp)
		{
			add_form_key('dl_modcp');

			if ($action == 'delete')
			{
				if (!empty($dlo_id))
				{
					if (!check_form_key('dl_modcp'))
					{
						trigger_error('FORM_INVALID');
					}

					$sql = 'DELETE FROM ' . DL_COMMENTS_TABLE . '
						WHERE ' . $this->db->sql_in_set('dl_id', $dlo_id);
					$this->db->sql_query($sql);
				}

				$dlo_id = array();
			}

			if (!empty($dlo_id))
			{
				if (!check_form_key('dl_modcp'))
				{
					trigger_error('FORM_INVALID');
				}

				$sql = 'UPDATE ' . DL_COMMENTS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', array(
					'approve' => true)) . ' WHERE ' . $this->db->sql_in_set('dl_id', $dlo_id);
				$this->db->sql_query($sql);
			}

			$sql_access_cats = ($this->auth->acl_get('a_') && $this->user->data['is_registered']) ? '' : ' AND ' . $this->db->sql_in_set('c.cat_id', $access_cat);

			$sql = 'SELECT COUNT(c.dl_id) AS total FROM ' . DL_COMMENTS_TABLE . " c
				WHERE c.approve = 0
					$sql_access_cats";
			$result = $this->db->sql_query($sql);
			$total_approve = $this->db->sql_fetchfield('total');
			$this->db->sql_freeresult($result);

			$sql = 'SELECT d.cat, d.id, d.description, d.desc_uid, d.desc_bitfield, d.desc_flags, c.comment_text, c.com_uid, c.com_bitfield, c.com_flags, c.user_id, c.username, c.dl_id FROM ' . DOWNLOADS_TABLE . ' d, ' . DL_COMMENTS_TABLE . " c
				WHERE d.id = c.id
					AND c.approve = 0
					$sql_access_cats
				ORDER BY d.cat, d.description";
			$result = $this->db->sql_query_limit($sql, $this->config['dl_links_per_page'], $start);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$cat_id		= $row['cat'];
				$cat_name	= $index[$cat_id]['cat_name'];
				$cat_name	= str_replace('&nbsp;&nbsp;|', '', $cat_name);
				$cat_name	= str_replace('___&nbsp;', '', $cat_name);
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

				$file_id = $row['id'];

				$comment_id = $row['dl_id'];

				$comment_user_id	= $row['user_id'];
				$comment_username	= $row['username'];
				$comment_user_link	= ($comment_user_id <> ANONYMOUS) ? append_sid($this->root_path . 'memberlist.' . $this->php_ext, "mode=viewprofile&amp;u=$comment_user_id") : '';

				$this->template->assign_block_vars('approve_row', array(
					'CAT_NAME'			=> $cat_name,
					'FILE_ID'			=> $file_id,
					'DESCRIPTION'		=> $description,
					'COMMENT_USERNAME'	=> $comment_username,
					'COMMENT_TEXT'		=> $comment_text,
					'COMMENT_ID'		=> $comment_id,

					'U_CAT_VIEW'	=> $cat_view,
					'U_USER_LINK'	=> $comment_user_link,
					'U_EDIT'		=> $this->helper->route('oxpus_dlext_details', array('view' => 'comment', 'action' => 'edit', 'df_id' => $file_id, 'cat_id' => $cat_id, 'dl_id' => $comment_id)),
					'U_DOWNLOAD'	=> $this->helper->route('oxpus_dlext_details', array('df_id' => $file_id)),
				));
			}
			$this->db->sql_freeresult($result);

			$s_hidden_fields = array(
				'cat_id'	=> $cat_id,
				'start'		=> $start
			);

			if ($total_approve > $this->config['dl_links_per_page'])
			{
				$pagination = $this->phpbb_container->get('pagination');
				$pagination->generate_template_pagination(
					$this->helper->route('oxpus_dlext_mcp_capprove', array('cat_id' => $cat_id)),
					'pagination',
					'start',
					$total_approve,
					$this->config['dl_links_per_page'],
					$page_start
				);

				$this->template->assign_vars(array(
					'PAGE_NUMBER'	=> $pagination->on_page($total_approve, $this->config['dl_links_per_page'], $page_start),
					'TOTAL_DL'		=> $this->language->lang('VIEW_DOWNLOADS', $total_approve),
				));
			}

			$this->template->assign_vars(array(
				'S_DL_MODCP_ACTION'	=> $this->helper->route('oxpus_dlext_mcp_capprove'),
				'S_HIDDEN_FIELDS'	=> build_hidden_fields($s_hidden_fields))
			);
		}

		return $this->helper->render('dl_mcp_capprove.html', $this->language->lang('MCP'));
	}
}
