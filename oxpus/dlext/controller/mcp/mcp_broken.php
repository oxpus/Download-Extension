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

class mcp_broken
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
	protected $dlext_topic;

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
		$dlext_main,
		$dlext_topic
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
		$this->dlext_topic				= $dlext_topic;
	}

	public function handle()
	{
		$nav_view = 'modcp';
		$modcp_mode = 'broken';

		// Include the default base init script
		include_once($this->ext_path . 'phpbb/includes/base_init.' . $this->php_ext);

		if (!$deny_modcp)
		{
			add_form_key('dl_modcp');

			if (!empty($dlo_id))
			{
				if (!check_form_key('dl_modcp'))
				{
					trigger_error('FORM_INVALID');
				}

				$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', [
					'broken' => 0]) . ' WHERE ' . $this->db->sql_in_set('id', $dlo_id);
				$this->db->sql_query($sql);

				$notification = $this->phpbb_container->get('notification_manager');
				$notification->delete_notifications('oxpus.dlext.notification.type.broken', $dlo_id);
			}

			$sql_access_cats = ($this->auth->acl_get('a_') && $this->user->data['is_registered']) ? '' : ' AND ' . $this->db->sql_in_set('cat', $access_cat);

			$sql = 'SELECT COUNT(id) AS total FROM ' . DOWNLOADS_TABLE . "
				WHERE broken = 1
					$sql_access_cats";
			$result = $this->db->sql_query($sql);
			$total_broken = $this->db->sql_fetchfield('total');
			$this->db->sql_freeresult($result);

			$sql = 'SELECT cat, id, description, desc_uid, desc_bitfield, desc_flags, broken FROM ' . DOWNLOADS_TABLE . "
				WHERE broken = 1
					$sql_access_cats
				ORDER BY cat, description";
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

				$file_id 	= $row['id'];
				$broken		= $row['broken'];

				$this->template->assign_block_vars('broken_row', [
					'CAT_NAME'		=> $cat_name,
					'FILE_ID'		=> $file_id,
					'DESCRIPTION'	=> $description,
					'BROKEN'		=> $broken,

					'U_CAT_VIEW'	=> $cat_view,
					'U_EDIT'		=> $this->helper->route('oxpus_dlext_mcp_edit', ['df_id' => $file_id, 'cat_id' => $cat_id, 'modcp' => 1]),
					'U_DELETE'		=> $this->helper->route('oxpus_dlext_mcp_manage', ['view' => 'toolbox', 'delete' => 1, 'dlo_id[0]' => $file_id, 'cat_id' => $cat_id, 'modcp' => 99]),
					'U_DOWNLOAD'	=> $this->helper->route('oxpus_dlext_details', ['df_id' => $file_id, 'cat_id' => $cat_id, 'modcp' => 1]),
				]);
			}

			$this->db->sql_freeresult($result);

			$s_hidden_fields = [
				'start'		=> $start
			];

			if ($total_broken > $this->config['dl_links_per_page'])
			{
				$pagination = $this->phpbb_container->get('pagination');
				$pagination->generate_template_pagination(
					$this->helper->route('oxpus_dlext_mcp_broken'),
					'pagination',
					'start',
					$total_broken,
					$this->config['dl_links_per_page'],
					$page_start
				);

				$this->template->assign_vars([
					'PAGE_NUMBER'	=> $pagination->on_page($total_broken, $this->config['dl_links_per_page'], $page_start),
					'TOTAL_DL'		=> $this->language->lang('VIEW_DOWNLOADS', $total_broken),
				]);
			}

			$this->template->assign_vars([
				'S_DL_MODCP_ACTION'	=> $this->helper->route('oxpus_dlext_mcp_broken'),
				'S_HIDDEN_FIELDS'	=> build_hidden_fields($s_hidden_fields),
			]);
		}

		return $this->helper->render('dl_mcp_broken.html', $this->language->lang('MCP'));
	}
}
