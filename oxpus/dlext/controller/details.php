<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\controller;

use Symfony\Component\DependencyInjection\Container;

class details
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

	protected $phpbb_dispatcher;

	protected $dlext_auth;
	protected $dlext_files;
	protected $dlext_format;
	protected $dlext_main;
	protected $dlext_status;

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
	* @param \phpbb\event\dispatcher_interface		$phpbb_dispatcher
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
		\phpbb\event\dispatcher_interface $phpbb_dispatcher,
		$dlext_auth,
		$dlext_files,
		$dlext_format,
		$dlext_main,
		$dlext_status
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
		$this->phpbb_dispatcher			= $phpbb_dispatcher;

		$this->ext_path					= $this->phpbb_extension_manager->get_extension_path('oxpus/dlext', true);
		$this->ext_path_web				= $this->phpbb_path_helper->update_web_root_path($this->ext_path);
		$this->ext_path_ajax			= $this->ext_path_web . 'assets/javascript/';

		$this->dlext_auth				= $dlext_auth;
		$this->dlext_files				= $dlext_files;
		$this->dlext_format				= $dlext_format;
		$this->dlext_main				= $dlext_main;
		$this->dlext_status				= $dlext_status;
	}

	public function handle($view = '')
	{
		$nav_view = 'details';

		// Include the default base init script
		include_once($this->ext_path . 'phpbb/includes/base_init.' . $this->php_ext);

		/*
		* wanna have smilies ;-)
		*/
		if ($action == 'smilies')
		{
			include($this->root_path . '/includes/functions_posting.' . $this->php_ext);
			generate_smilies('window', 0);
		}

		/*
		* set the right values for comments
		*/
		if (in_array($action, ['view', 'save', 'delete', 'edit']))
		{
			$nav_view = 'comment';
		}

		if ($cancel)
		{
			$action = '';
		}

		if (!$df_id)
		{
			redirect($this->helper->route('oxpus_dlext_index'));
		}

		/*
		* default entry point for download details
		*/
		$dl_files = [];
		$dl_files = $this->dlext_files->all_files(0, '', 'ASC', '', $df_id, $modcp, '*');

		if (!$dl_files)
		{
			redirect($this->helper->route('oxpus_dlext_index'));
		}

		if (!$dl_files['id'])
		{
			trigger_error('DL_NO_PERMISSION');
		}

		$check_status = [];
		$check_status = $this->dlext_status->status($df_id);

		/*
		* prepare the download for displaying
		*/
		$long_desc			= $dl_files['long_desc'];
		$long_desc_uid		= $dl_files['long_desc_uid'];
		$long_desc_bitfield	= $dl_files['long_desc_bitfield'];
		$long_desc_flags	= (isset($dl_files['long_desc_flags'])) ? $dl_files['long_desc_flags'] : 0;
		$long_desc			= generate_text_for_display($long_desc, $long_desc_uid, $long_desc_bitfield, $long_desc_flags);

		$broken				= $dl_files['broken'];

		$status				= $check_status['status_detail'];
		$file_name			= $check_status['file_detail'];
		$file_load			= $check_status['auth_dl'];

		$real_file			= $dl_files['real_file'];
				
		$description		= $dl_files['description'];
		$desc_uid			= $dl_files['desc_uid'];
		$desc_bitfield		= $dl_files['desc_bitfield'];
		$desc_flags			= $dl_files['desc_flags'];
		$description		= generate_text_for_display($description, $desc_uid, $desc_bitfield, $desc_flags);

		$real_comment_exists = 0;

		if (!$cat_id)
		{
			$cat_id = $dl_files['cat'];
		}

		$mini_icon			= $this->dlext_status->mini_status_file($cat_id, $df_id);

		$hack_version		= '&nbsp;'.$dl_files['hack_version'];

		$cat_auth = [];
		$cat_auth = $this->dlext_auth->dl_cat_auth($cat_id);

		/*
		* check the permissions
		*/
		$user_can_alltimes_load = false;

		if (($cat_auth['auth_mod'] || ($this->auth->acl_get('a_') && $this->user->data['is_registered'])) && !$this->dlext_auth->user_banned())
		{
			$modcp = ($modcp) ? 1 : 0;
			$user_can_alltimes_load = true;
			$user_is_mod = true;
		}
		else
		{
			$modcp = 0;
			$user_is_mod = false;
		}

		/*
		* Prepare all permissions for the current user
		*/
		$captcha_active = true;
		$user_is_guest = false;
		$user_is_admin = false;
		$user_is_founder = false;

		if (!$this->user->data['is_registered'])
		{
			$user_is_guest = true;
		}
		else
		{
			if ($this->auth->acl_get('a_'))
			{
				$user_is_admin = true;
			}

			if ($this->user->data['user_type'] == USER_FOUNDER)
			{
				$user_is_founder = true;
			}
		}

		switch ($this->config['dl_download_vc'])
		{
			case 0:
				$captcha_active = false;
			break;

			case 1:
				if (!$user_is_guest)
				{
					$captcha_active = false;
				}
			break;

			case 2:
				if ($user_is_mod || $user_is_admin || $user_is_founder)
				{
					$captcha_active = false;
				}
			break;

			case 3:
				if ($user_is_admin || $user_is_founder)
				{
					$captcha_active = false;
				}
			break;

			case 4:
				if ($user_is_founder)
				{
					$captcha_active = false;
				}
			break;
		}

		$this->language->add_lang('posting');

		/*
		* Check saved thumbs
		*/
		$s_dl_popupimage = false;

		if ($dl_files['thumbnail'])
		{
			$s_dl_popupimage = true;
		}

		$sql = 'SELECT * FROM ' . DL_IMAGES_TABLE . '
			WHERE dl_id = ' . (int) $df_id;
		$result = $this->db->sql_query($sql);
		$total_images = $this->db->sql_affectedrows($result);

		$thumbs_ary = [];
		$i = 1;

		if ($total_images)
		{
			$s_dl_popupimage = true;

			while ($row = $this->db->sql_fetchrow($result))
			{
				$thumbs_ary[$i] = $row;
				++$i;
			}
		}

		$this->db->sql_freeresult($result);

		$notification = $this->phpbb_container->get('notification_manager');

		page_header($this->language->lang('DOWNLOADS') . ' - ' . strip_tags($dl_files['description']));

		/*
		* User is banned?
		*/
		if ($this->dlext_auth->user_banned())
		{
			$s_dl_userban = true;
		}
		else
		{
			$s_dl_userban = false;
		}

		/*
		* Forum rules?
		*/
		if (isset($index[$cat_id]['rules']) && $index[$cat_id]['rules'] != '')
		{
			$cat_rule = $index[$cat_id]['rules'];
			$cat_rule_uid = (isset($index[$cat_id]['rule_uid'])) ? $index[$cat_id]['rule_uid'] : '';
			$cat_rule_bitfield = (isset($index[$cat_id]['rule_bitfield'])) ? $index[$cat_id]['rule_bitfield'] : '';
			$cat_rule_flags = (isset($index[$cat_id]['rule_flags'])) ? $index[$cat_id]['rule_flags'] : 0;
			$cat_rule = censor_text($cat_rule);
			$cat_rule = generate_text_for_display($cat_rule, $cat_rule_uid, $cat_rule_bitfield, $cat_rule_flags);

			$s_cat_rule = true;
		}
		else
		{
			$s_cat_rule = false;
			$cat_rule = '';
		}

		/*
		* Cat Traffic?
		*/
		$cat_traffic = 0;
		$s_cat_traffic = false;

		if (!$this->config['dl_traffic_off'])
		{
			if ($this->user->data['is_registered'])
			{
				$cat_overall_traffic = $this->config['dl_overall_traffic'];
				$cat_limit = DL_OVERALL_TRAFFICS;
			}
			else
			{
				$cat_overall_traffic = $this->config['dl_overall_guest_traffic'];
				$cat_limit = DL_GUESTS_TRAFFICS;
			}

			if (isset($index[$cat_id]['cat_traffic']) && isset($index[$cat_id]['cat_traffic_use']))
			{
				$cat_traffic = $index[$cat_id]['cat_traffic'] - $index[$cat_id]['cat_traffic_use'];

				if ($index[$cat_id]['cat_traffic'] && $cat_traffic > 0)
				{
					$cat_traffic = ($cat_traffic > $cat_overall_traffic && $cat_limit == true) ? $cat_overall_traffic : $cat_traffic;
					$cat_traffic = $this->dlext_format->dl_size($cat_traffic);

					$s_cat_traffic = false;
				}
			}
		}
		else
		{
			unset($cat_traffic);
		}

		/*
		* Read the ratings for this little download
		*/
		$rating = $s_hidden_fields = '';
		$ratings = 0;
		$rating_access = $user_have_rated = false;

		if ($this->config['dl_enable_rate'])
		{
			$sql = 'SELECT dl_id, user_id FROM ' . DL_RATING_TABLE . '
				WHERE dl_id = ' . (int) $df_id;
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				++$ratings;
				$user_have_rated = ($row['user_id'] == $this->user->data['user_id']) ? true : false;
			}

			$this->db->sql_freeresult($result);

			if ($this->user->data['is_registered'] && !$user_have_rated)
			{
				$rating_access = true;
			}
		}

		/*
		* fetch last comment, if exists
		*/
		$s_comments_tab = false;
		$allow_manage = false;

		if ($index[$cat_id]['comments'] && $this->dlext_auth->cat_auth_comment_read($cat_id))
		{
			/*
			* check permissions to manage comments
			*/
			$sql = 'SELECT user_id FROM ' . DL_COMMENTS_TABLE . '
				WHERE id = ' . (int) $df_id . '
					AND dl_id = ' . (int) $dl_id . '
					AND approve = ' . true . '
					AND cat_id = ' . (int) $cat_id;
			$result = $this->db->sql_query($sql);
			$row_user = $this->db->sql_fetchfield('user_id');
			$this->db->sql_freeresult($result);

			if (($row_user == $this->user->data['user_id'] || $cat_auth['auth_mod'] || $index[$cat_id]['auth_mod'] || $this->auth->acl_get('a_')) && $this->user->data['is_registered'])
			{
				$allow_manage = true;
			}

			$deny_post = false;

			if (!$this->dlext_auth->cat_auth_comment_post($cat_id))
			{
				$allow_manage = false;
				$deny_post = true;
			}

			$s_comments_tab = true;

			$comment_text = $this->request->variable('message', '', true);

			if ($action == 'save' && !$deny_post && $comment_text)
			{
				// check form
				if (!check_form_key('dl_comment_posting'))
				{
					trigger_error($this->language->lang('FORM_INVALID'), E_USER_WARNING);
				}

				$allow_bbcode	= ($this->config['allow_bbcode']) ? true : false;
				$allow_urls		= true;
				$allow_smilies	= ($this->config['allow_smilies']) ? true : false;
				$com_uid		= '';
				$com_bitfield	= '';
				$com_flags		= 0;

				generate_text_for_storage($comment_text, $com_uid, $com_bitfield, $com_flags, $allow_bbcode, $allow_urls, $allow_smilies);

				if ($index[$cat_id]['approve_comments'] || $this->dlext_auth->user_admin())
				{
					$approve = true;
				}
				else
				{
					$approve = 0;
				}

				/**
				 * Additional actions before storage a download comment
				 *
				 * @event oxpus.dlext.details_comment_storage_before
				 * @var int		df_id			download ID
				 * @var int		dl_id			download comment ID
				 * @var int		cat_id			download category ID
				 * @var string	comment_text	comment text, unformatted
				 * @var bool	approve			download approval
				 * @since 8.1.1
				 */
				$vars = array(
					'df_id',
					'dl_id',
					'cat_id',
					'comment_text',
					'approve',
				);
				extract($this->phpbb_dispatcher->trigger_event('oxpus.dlext.details_comment_storage_before', compact($vars)));

				if ($dl_id)
				{
					$sql = 'UPDATE ' . DL_COMMENTS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', [
						'comment_edit_time'	=> time(),
						'comment_text'		=> $comment_text,
						'com_uid'			=> $com_uid,
						'com_bitfield'		=> $com_bitfield,
						'com_flags'			=> $com_flags,
						'approve'			=> $approve]) . ' WHERE dl_id = ' . (int) $dl_id;
					$this->db->sql_query($sql);

					$comment_message = $this->language->lang('DL_COMMENT_UPDATED');
				}
				else
				{
					$sql = 'INSERT INTO ' . DL_COMMENTS_TABLE . ' ' . $this->db->sql_build_array('INSERT', [
						'id'				=> $df_id,
						'cat_id'			=> $cat_id,
						'user_id'			=> $this->user->data['user_id'],
						'username'			=> $this->user->data['username'],
						'comment_time'		=> time(),
						'comment_edit_time'	=> time(),
						'comment_text'		=> $comment_text,
						'com_uid'			=> $com_uid,
						'com_bitfield'		=> $com_bitfield,
						'com_flags'			=> $com_flags,
						'approve'			=> $approve]);
					$this->db->sql_query($sql);

					$dl_id = $this->db->sql_nextid();

					$comment_message = $this->language->lang('DL_COMMENT_ADDED');
				}

				/**
				 * Additional actions after storage a download comment
				 *
				 * @event oxpus.dlext.details_comment_storage_after
				 * @var int		df_id			download ID
				 * @var int		dl_id			download comment ID
				 * @var int		cat_id			download category ID
				 * @var string	comment_text	comment text prepared for storage
				 * @var string	com_uid			comment bbcode uid
				 * @var string	com_bitfield	comment bbcode bitfields
				 * @var int		com_flags		comment bbcode flags
				 * @var bool	approve			download approval
				 * @var string	comment_message	success message after save/update comment
				 * @since 8.1.1
				 */
				$vars = array(
					'df_id',
					'dl_id',
					'cat_id',
					'comment_text',
					'com_uid',
					'com_bitfield',
					'com_flags',
					'approve',
					'comment_message',
				);
				extract($this->phpbb_dispatcher->trigger_event('oxpus.dlext.details_comment_storage_after', compact($vars)));

				$approve_message	= '';

				if (!$approve)
				{
					$processing_user = ($index[$cat_id]['auth_cread'] == 3) ? false : $this->dlext_auth->dl_auth_users($cat_id, 'auth_mod');

					if (is_array($processing_user))
					{
						$notification_data = [
							'user_ids'			=> $processing_user,
							'df_id'				=> $df_id,
							'dl_id'				=> $dl_id,
							'description'		=> $description,
							'cat_name'			=> $index[$cat_id]['cat_name_nav'],
						];

						$notification->add_notifications('oxpus.dlext.notification.type.capprove', $notification_data);
						$approve_message	= '<br />' . $this->language->lang('DL_MUST_BE_APPROVE_COMMENT');
					}

					$return_parameters	= ['df_id' => $df_id];
					$return_text		= $this->language->lang('CLICK_RETURN_DOWNLOAD_DETAILS');
				}
				else
				{
					$sql = 'SELECT fav_user_id FROM ' . DL_FAVORITES_TABLE . '
						WHERE fav_dl_id = ' . (int) $df_id;
					$result = $this->db->sql_query($sql);

					$fav_user = [];

					while ($row = $this->db->sql_fetchrow($result))
					{
						$fav_user[] = $row['fav_user_id'];
					}

					$this->db->sql_freeresult($result);

					$send_notify = false;

					if (!$this->config['dl_disable_email'] && $fav_user)
					{
						switch ($index[$cat_id]['auth_cread'])
						{
							case 0:
							case 1:
								if (!empty($fav_user))
								{
									$send_notify = true;
									$processing_user = $fav_user;
								}
								else
								{
									$send_notify = false;
								}
							break;

							case 2:
								$processing_user = $this->dlext_auth->dl_auth_users($cat_id, 'auth_mod');

								if (!empty($processing_user))
								{
									$send_notify = true;
								}

								if (!empty($fav_user))
								{
									$proccessing_user = array_merge($fav_user, $processing_user);
									$send_notify = true;
								}
								else
								{
									$send_notify = false;
								}
							break;

							case 3:
							default:
								$send_notify = false;
							break;
						}

						if ($send_notify)
						{
							$notification_data = [
								'user_ids'			=> $processing_user,
								'df_id'				=> $df_id,
								'dl_id'				=> $dl_id,
								'description'		=> $description,
								'cat_name'			=> $index[$cat_id]['cat_name_nav'],
							];
		
							$notification->add_notifications('oxpus.dlext.notification.type.comments', $notification_data);
						}
					}

					$return_parameters	= ['view' => 'comment', 'action' => 'view', 'df_id' => $df_id];
					$return_text		= $this->language->lang('CLICK_RETURN_COMMENTS');
				}

				$message = $comment_message . $approve_message . '<br /><br />' . sprintf($return_text, '<a href="' . $this->helper->route('oxpus_dlext_details', $return_parameters) . '">', '</a>');

				meta_refresh(3, $this->helper->route('oxpus_dlext_details', $return_parameters));

				trigger_error($message);
			}

			if ($action == 'delete' && $allow_manage)
			{
				// Delete comment by poster or admin or dl_mod
				if (confirm_box(true))
				{
					$sql = 'DELETE FROM ' . DL_COMMENTS_TABLE . '
						WHERE cat_id = ' . (int) $cat_id . '
							AND id = ' . (int) $df_id . '
							AND dl_id = ' . (int) $dl_id;
					$this->db->sql_query($sql);

					$sql = 'SELECT dl_id FROM ' . DL_COMMENTS_TABLE . '
						WHERE cat_id = ' . (int) $cat_id . '
							AND id = ' . (int) $df_id;
					$result = $this->db->sql_query($sql);
					$total_comments = $this->db->sql_affectedrows($result);
					$this->db->sql_freeresult($result);
					
					$notification->delete_notifications([
						'oxpus.dlext.notification.type.capprove',
						'oxpus.dlext.notification.type.comments',
					], $dl_id);

					if (!$total_comments)
					{
						redirect($this->helper->route('oxpus_dlext_details', ['df_id' => $df_id]));
					}
					else
					{
						$action = 'view';
					}
				}
				else
				{
					$s_hidden_fields = [
						'cat_id'	=> $cat_id,
						'df_id'		=> $df_id,
						'dl_id'		=> $dl_id,
						'action'	=> 'delete',
						'view'		=> 'comment'
					];

					confirm_box(false, $this->language->lang('DELETE_MESSAGE_CONFIRM'), build_hidden_fields($s_hidden_fields));
				}
			}

			if (!$deny_post)
			{
				// Edit or add a comment
				if ($action == 'edit')
				{
					$sql = 'SELECT comment_text, com_uid, com_flags FROM ' . DL_COMMENTS_TABLE . '
						WHERE dl_id = ' . (int) $dl_id . '
							AND id = ' . (int) $df_id . '
							AND cat_id = ' . (int) $cat_id;
					$result = $this->db->sql_query($sql);
					$row = $this->db->sql_fetchrow($result);

					$comment_text	= $row['comment_text'];
					$com_uid		= $row['com_uid'];
					$com_flags		= $row['com_flags'];

					$this->db->sql_freeresult($result);

					$text_ary		= generate_text_for_edit($comment_text, $com_uid, $com_flags);
					$comment_text	= $text_ary['text'];
				}

				$s_hidden_fields = [
					'dl_id'		=> $dl_id,
					'df_id'		=> $df_id,
					'cat_id'	=> $cat_id,
					'action'	=> 'save',
					'view'		=> 'comment'
				];

				add_form_key('dl_comment_posting', '_COMMENT');

				// Status for HTML, BBCode, Smilies, Images and Flash
				$bbcode_status	= ($this->config['allow_bbcode']) ? true : false;
				$smilies_status	= ($bbcode_status && $this->config['allow_smilies']) ? true : false;
				$img_status		= ($bbcode_status) ? true : false;
				$url_status		= ($this->config['allow_post_links']) ? true : false;
				$flash_status	= ($bbcode_status && $this->config['allow_post_flash']) ? true : false;
				$quote_status	= true;

				// Smilies Block
				include($this->root_path . 'includes/functions_posting.' . $this->php_ext);
				generate_smilies('inline', 0);

				// BBCode-Block
				display_custom_bbcodes();

				$this->template->assign_vars([
					'COMMENT_TEXT'		=> $comment_text,

					'S_BBCODE_ALLOWED'	=> $bbcode_status,
					'S_BBCODE_IMG'		=> $img_status,
					'S_BBCODE_URL'		=> $url_status,
					'S_BBCODE_FLASH'	=> $flash_status,
					'S_BBCODE_QUOTE'	=> $quote_status,

					'S_COMMENT_POST_ACTION'	=> $this->helper->route('oxpus_dlext_details'),
					'S_HIDDEN_POST_FIELDS'	=> build_hidden_fields($s_hidden_fields),

					'U_MORE_SMILIES'		=> $this->helper->route('oxpus_dlext_details', ['action' => 'smilies']),
				]);
			}

			$sql = 'SELECT * FROM ' . DL_COMMENTS_TABLE . '
				WHERE cat_id = ' . (int) $cat_id . '
					AND id = ' . (int) $df_id . '
					AND approve = ' . true;
			$result = $this->db->sql_query($sql);
			$real_comment_exists = $this->db->sql_affectedrows($result);
			$this->db->sql_freeresult($result);

			if ($real_comment_exists > $this->config['dl_links_per_page'])
			{
				$pagination = $this->phpbb_container->get('pagination');
				$pagination->generate_template_pagination(
					$this->helper->route('oxpus_dlext_details', ['view' => 'comment', 'action' => 'view', 'cat_id' => $cat_id, 'df_id' => $df_id]),
					'pagination',
					'start',
					$real_comment_exists,
					$this->config['dl_links_per_page'],
					$page_start
				);

				$this->template->assign_vars([
					'PAGE_NUMBER'	=> $pagination->on_page($real_comment_exists, $this->config['dl_links_per_page'], $page_start),
					'TOTAL_DL'		=> $this->language->lang('DL_COMMENTS_COUNT', $real_comment_exists),
				]);
			}

			if ($real_comment_exists)
			{
				$this->template->assign_var('S_VIEW_COMMENTS', true);

				$sql = 'SELECT c.*, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height FROM ' . DL_COMMENTS_TABLE . ' c
					LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = c.user_id
					WHERE cat_id = ' . (int) $cat_id . '
						AND id = ' . (int) $df_id . '
						AND approve = ' . true . '
					ORDER BY comment_time DESC';
				$result = $this->db->sql_query_limit($sql, $this->config['dl_links_per_page'], $start);

				$dl_ids = [];

				while ($row = $this->db->sql_fetchrow($result))
				{
					$avatar_row = [
						'avatar'		=> $row['user_avatar'],
						'avatar_type'	=> $row['user_avatar_type'],
						'avatar_width'	=> $row['user_avatar_width'],
						'avatar_height'	=> $row['user_avatar_height'],
					];

					$poster_id			= $row['user_id'];
					$poster				= $row['username'];
					$poster_color		= $row['user_colour'];
					$poster_avatar		= phpbb_get_avatar($avatar_row, $poster, false, true);
					$dl_id				= $row['dl_id'];

					$message			= $row['comment_text'];
					$com_uid			= $row['com_uid'];
					$com_bitfield		= $row['com_bitfield'];
					$com_flags			= (isset($row['com_flags'])) ? $row['com_flags'] : 0;

					$message			= censor_text($message);
					$message			= generate_text_for_display($message, $com_uid, $com_bitfield, $com_flags);

					$comment_time		= $row['comment_time'];
					$comment_edit_time	= $row['comment_edit_time'];

					if($comment_time <> $comment_edit_time)
					{
						$edited_by = $this->language->lang('DL_COMMENT_EDITED', $this->user->format_date($comment_edit_time));
					}
					else
					{
						$edited_by = '';
					}

					$u_delete_comment	= $this->helper->route('oxpus_dlext_details', ['view' => 'comment', 'action' => 'delete', 'cat_id' => $cat_id, 'df_id' => $df_id, 'dl_id' => $dl_id]);
					$u_edit_comment		= $this->helper->route('oxpus_dlext_details', ['view' => 'comment', 'action' => 'edit', 'cat_id' => $cat_id, 'df_id' => $df_id, 'dl_id' => $dl_id]);

					$this->template->assign_block_vars('comment_row', [
						'EDITED_BY'		=> $edited_by,
						'POSTER'		=> get_username_string('full', $poster_id, $poster, $poster_color),
						'POSTER_AVATAR'	=> $poster_avatar,
						'MESSAGE'		=> $message,
						'POST_TIME'		=> $this->user->format_date($comment_time),
						'POST_TIME_RFC'	=> gmdate(DATE_RFC3339, $comment_time),
						'DL_ID'			=> $dl_id,

						'U_DELETE_COMMENT'	=> $u_delete_comment,
						'U_EDIT_COMMENT'	=> ($deny_post) ? '' : $u_edit_comment,
					]);

					$dl_ids[] = $dl_id;

					if (($poster_id == $this->user->data['user_id'] || $cat_auth['auth_mod'] || $index[$cat_id]['auth_mod'] || $this->auth->acl_get('a_')) && $this->user->data['is_registered'] && !$deny_post)
					{
						$this->template->assign_block_vars('comment_row.action_button', []);
					}
				}

				$this->db->sql_freeresult($result);

				$notification->delete_notifications('oxpus.dlext.notification.type.comments', $dl_ids, false, $this->user->data['user_id']);
			}
		}

		/*
		* Check existing hashes and build the hash table if the category allowes it
		*/
		$hash_method = $this->config['dl_file_hash_algo'];
		$func_hash = $hash_method . '_file';
		$hash_table_tmp = [];
		$hash_table = [];
		$hash_ary = [];
		$hash_tab = false;
		$ver_tab = false;
		$ver_can_edit = false;

		if (($user_is_mod || $user_is_admin || $user_is_founder) || ($this->config['dl_edit_own_downloads'] && $dl_files['add_user'] == $this->user->data['user_id']))
		{
			$ver_can_edit = true;
		}

		if (!$dl_files['extern'])
		{
			if (!$dl_files['file_hash'])
			{
				if ($dl_files['real_file'] && file_exists(DL_EXT_FILEBASE_PATH. 'downloads/' . $index[$cat_id]['cat_path'] . $dl_files['real_file']))
				{
					$dl_files['file_hash'] = $func_hash(DL_EXT_FILEBASE_PATH. 'downloads/' . $index[$cat_id]['cat_path'] . $dl_files['real_file']);
					$sql = 'UPDATE ' . DOWNLOADS_TABLE . " SET file_hash = '" . $this->db->sql_escape($dl_files['file_hash']) . "' WHERE id = " . (int) $df_id;
					$this->db->sql_query($sql);
				}
			}

			if ($index[$cat_id]['show_file_hash'])
			{
				$dl_key = $dl_files['description'] . (($dl_files['hack_version']) ? ' ' . $dl_files['hack_version'] : ' (' . $this->language->lang('DL_CURRENT_VERSION') . ')');
				$hash_table_tmp[$dl_key]['hash'] = ($dl_files['file_hash']) ? $dl_files['file_hash'] : '';
				$hash_table_tmp[$dl_key]['file'] = $dl_files['file_name'];
				$hash_table_tmp[$dl_key]['type'] = ($dl_files['file_hash']) ? $hash_method : $this->language->lang('DL_FILE_NOT_FOUND', $dl_files['file_name'], DL_EXT_FILEBASE_PATH. 'downloads/' . $index[$cat_id]['cat_path']);
				$hash_ary[] = $dl_key;
			}

			$sql = 'SELECT * FROM ' . DL_VERSIONS_TABLE . '
				WHERE dl_id = ' . (int) $df_id . '
				ORDER BY ver_version DESC, ver_change_time DESC';
			$result = $this->db->sql_query($sql);
			$total_releases = $this->db->sql_affectedrows($result);

			if ($total_releases)
			{
				$version_array = [];
				$ver_key_ary = [];

				while ($row = $this->db->sql_fetchrow($result))
				{
					$ver_file_hash = $row['ver_file_hash'];

					if (!$ver_file_hash)
					{
						if ($row['ver_real_file'] && file_exists(DL_EXT_FILEBASE_PATH. 'downloads/' . $index[$cat_id]['cat_path'] . $row['ver_real_file']))
						{
							$ver_file_hash = $func_hash(DL_EXT_FILEBASE_PATH. 'downloads/' . $index[$cat_id]['cat_path'] . $row['ver_real_file']);
							$sql = 'UPDATE ' . DL_VERSIONS_TABLE . " SET ver_file_hash = '" . $this->db->sql_escape($ver_file_hash) . "' WHERE ver_id = " . (int) $row['ver_id'];
							$this->db->sql_query($sql);
						}
					}

					$dl_key = $dl_files['description'] . (($row['ver_version']) ? ' ' . $row['ver_version'] : ' (' . $this->user->format_date($row['ver_change_time']) . ')');

					if ($index[$cat_id]['show_file_hash'] && ($row['ver_active'] || $ver_can_edit))
					{
						$hash_table_tmp[$dl_key]['hash'] = ($ver_file_hash) ? $ver_file_hash : '';
						$hash_table_tmp[$dl_key]['file'] = $row['ver_file_name'];
						$hash_table_tmp[$dl_key]['type'] = ($ver_file_hash) ? $hash_method : $this->language->lang('DL_FILE_NOT_FOUND', $row['ver_file_name'], DL_EXT_FILEBASE_PATH. 'downloads/' . $index[$cat_id]['cat_path']);
						$hash_ary[] = $dl_key;
					}

					if ($row['ver_active'] || $ver_can_edit)
					{
						$ver_tab = true;
						$ver_desc = censor_text($row['ver_text']);
						$flags = (isset($row['ver_flags'])) ? $row['ver_flags'] : 0;
						$ver_desc = generate_text_for_display($ver_desc, $row['ver_uid'], $row['ver_bitfield'], $flags);
						if (strlen($ver_desc) > 150)
						{
							$ver_desc = substr($ver_desc, 0, 100) . ' [...]';
						}

						$ver_tmp = ($row['ver_version']) ? $row['ver_version'] : $row['ver_change_time'];
						$ver_key_ary[] = $ver_tmp;
						$version_array[$ver_tmp] = [
							'VER_TITLE'			=> $dl_key,
							'VER_TIME'			=> $this->user->format_date($row['ver_change_time']),
							'VER_TIME_RFC'		=> gmdate(DATE_RFC3339, $row['ver_change_time']),
							'VER_DESC'			=> $ver_desc,
							'VER_ACTIVE'		=> $row['ver_active'],
							'S_USER_PERM'		=> $ver_can_edit,
							'U_VERSION'			=> $this->helper->route('oxpus_dlext_version', ['action' => 'detail', 'ver_id' => $row['ver_id'], 'df_id' => $df_id]),
							'U_VERSION_EDIT'	=> $this->helper->route('oxpus_dlext_version', ['action' => 'edit', 'ver_id' => $row['ver_id'], 'df_id' => $df_id]),
						];
					}
				}

				natsort($ver_key_ary);
				$ver_key_ary = array_reverse($ver_key_ary);
				foreach ($ver_key_ary as $key => $value)
				{
					$this->template->assign_block_vars('ver_cell', $version_array[$value]);
				}
				unset($ver_key_ary);
				unset($version_array);
			}

			natsort($hash_ary);
			$hash_ary = array_unique(array_reverse($hash_ary));
			foreach ($hash_ary as $key => $value)
			{
				$hash_table[$value] = $hash_table_tmp[$value];
			}
			unset($hash_ary);
			unset($hash_table_tmp);

			$this->db->sql_freeresult($result);

			if (!empty($hash_table) && $index[$cat_id]['show_file_hash'])
			{
				foreach ($hash_table as $key => $value)
				{
					$this->template->assign_block_vars('hash_row', [
						'DL_VERSION'		=> $key,
						'DL_FILE_NAME'		=> $value['file'],
						'DL_HASH_METHOD'	=> $value['type'],
						'DL_HASH'			=> $value['hash'],
					]);
				}

				$hash_tab = true;
			}
		}

		/*
		* generate page
		*/
		$this->template->set_filenames(['body' => 'view_dl_body.html']);

		$user_id = $this->user->data['user_id'];
		$username = $this->user->data['username'];

		if ($dl_files['extern'])
		{
			if ($this->config['dl_shorten_extern_links'])
			{
				if (strlen($file_name) > $this->config['dl_shorten_extern_links'] && strlen($file_name) <= $this->config['dl_shorten_extern_links'] * 2)
				{
					$file_name = substr($file_name, strlen($file_name) - $this->config['dl_shorten_extern_links']);
				}
				else
				{
					$file_name = substr($file_name, 0, $this->config['dl_shorten_extern_links']) . '...' . substr($file_name, strlen($file_name) - $this->config['dl_shorten_extern_links']);
				}
			}
		}

		if ($dl_files['file_size'])
		{
			$file_size_out = $this->dlext_format->dl_size($dl_files['file_size'], 2);
		}
		else
		{
			$file_size_out = $this->language->lang('DL_NOT_AVAILIBLE');
		}

		$file_klicks			= $dl_files['klicks'];
		$file_overall_klicks	= $dl_files['overall_klicks'];

		$cat_name = $index[$cat_id]['cat_name'];
		$cat_view = $index[$cat_id]['nav_path'];
		$cat_desc = $index[$cat_id]['description'];

		$add_user		= $add_time = '';
		$change_user	= $change_time = '';

		$sql = 'SELECT username, user_id, user_colour FROM ' . USERS_TABLE . '
			WHERE user_id = ' . (int) $dl_files['add_user'];
		$result = $this->db->sql_query($sql);

		$row			= $this->db->sql_fetchrow($result);
		$add_user		= get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']);
		$add_time		= $this->user->format_date($dl_files['add_time']);

		$this->db->sql_freeresult($result);

		if ($dl_files['add_time'] != $dl_files['change_time'])
		{
			$sql = 'SELECT username, user_id, user_colour FROM ' . USERS_TABLE . '
				WHERE user_id = ' . (int) $dl_files['change_user'];
			$result = $this->db->sql_query($sql);

			$row			= $this->db->sql_fetchrow($result);
			$change_user	= get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']);
			$change_time	= $this->user->format_date($dl_files['change_time']);

			$this->db->sql_freeresult($result);
		}

		$last_time_string		= ($dl_files['extern']) ? $this->language->lang('DL_LAST_TIME_EXTERN') : $this->language->lang('DL_LAST_TIME');
		$last_time				= ($dl_files['last_time']) ? sprintf($last_time_string, $this->user->format_date($dl_files['last_time'])) : $this->language->lang('DL_NO_LAST_TIME');

		$hack_author_email		= $dl_files['hack_author_email'];
		$hack_author			= ($dl_files['hack_author'] != '') ? $dl_files['hack_author'] : 'n/a';
		$hack_author_website	= $dl_files['hack_author_website'];
		$hack_dl_url			= $dl_files['hack_dl_url'];
		$hack_version			= $dl_files['hack_version'];

		$mod_test				= $dl_files['test'];
		$mod_require			= $dl_files['req'];
		$mod_warning			= $dl_files['warning'];
		$mod_desc				= $dl_files['mod_desc'];

		/*
		* hacklist
		*/
		$s_hacklist = false;
		if ($dl_files['hacklist'] && $this->config['dl_use_hacklist'])
		{
			$s_hacklist = true;
		}

		/*
		* Block for extra informations - The MOD Block ;-)
		*/
		$s_mod_test = false;
		$s_mod_desc = false;
		$s_mod_warning = false;
		$s_mod_require = false;

		if ($dl_files['mod_list'])
		{
			if ($index[$cat_id]['allow_mod_desc'])
			{
				$this->template->assign_var('S_MOD_LIST', true);

				if ($mod_test)
				{
					$s_mod_test = true;
				}

				if ($mod_desc)
				{
					$s_mod_desc			= true;
					$mod_desc_uid		= $dl_files['mod_desc_uid'];
					$mod_desc_bitfield	= $dl_files['mod_desc_bitfield'];
					$mod_desc_flags		= (isset($dl_files['mod_desc_flags'])) ? $dl_files['mod_desc_flags'] : 0;
					$mod_desc			= generate_text_for_display($mod_desc, $mod_desc_uid, $mod_desc_bitfield, $mod_desc_flags);
				}

				if ($mod_warning)
				{
					$s_mod_warning		= true;
					$mod_warn_uid		= $dl_files['warn_uid'];
					$mod_warn_bitfield	= $dl_files['warn_bitfield'];
					$mod_warn_flags		= (isset($dl_files['warn_flags'])) ? $dl_files['warn_flags'] : 0;
					$mod_warning		= generate_text_for_display($mod_warning, $mod_warn_uid, $mod_warn_bitfield, $mod_warn_flags);
				}

				if ($mod_require)
				{
					$s_mod_require = true;
				}
			}
		}

		/*
		* ToDO's? ToDo's!
		*/
		$mod_todo = $dl_files['todo'];

		if ($mod_todo)
		{
			$s_mod_todo			= true;
			$mod_todo_uid		= $dl_files['todo_uid'];
			$mod_todo_bitfield	= $dl_files['todo_bitfield'];
			$mod_todo_flags		= (isset($dl_files['todo_flags'])) ? $dl_files['todo_flags'] : 0;
			$mod_todo			= generate_text_for_display($mod_todo, $mod_todo_uid, $mod_todo_bitfield, $mod_todo_flags);
		}
		else
		{
			$s_mod_todo = false;
		}

		/*
		* Check for recurring downloads
		*/
		$s_trafficfree_dl = false;

		if ($this->config['dl_user_traffic_once'] && !$file_load && !$dl_files['free'] && !$dl_files['extern'] && ($dl_files['file_size'] > $this->user->data['user_traffic'] ) && !$this->config['dl_traffic_off'] && DL_USERS_TRAFFICS == true)
		{
			$sql = 'SELECT * FROM ' . DL_NOTRAF_TABLE . '
				WHERE user_id = ' . (int) $this->user->data['user_id'] . '
					AND dl_id = ' . (int) $df_id;
			$result = $this->db->sql_query($sql);
			$still_count = $this->db->sql_affectedrows($result);
			$this->db->sql_freeresult($result);

			if ($still_count)
			{
				$file_load = true;
				$s_trafficfree_dl = true;
			}
		}

		/*
		* Hotlink or not hotlink, that is the question :-P
		* And we will check a broken download inclusive the visual confirmation here ...
		*/
		if (($file_load || $user_can_alltimes_load) && !$this->user->data['is_bot'])
		{
			if (!$dl_files['broken'] || ($dl_files['broken'] && !$this->config['dl_report_broken_lock']) || $user_can_alltimes_load)
			{
				if ($this->config['dl_prevent_hotlink'])
				{
					$hotlink_id = md5($this->user->data['user_id'] . time() . $df_id . $this->user->data['session_id']);

					$sql = 'INSERT INTO ' . DL_HOTLINK_TABLE . ' ' . $this->db->sql_build_array('INSERT', [
						'user_id'		=> $this->user->data['user_id'],
						'session_id'	=> $this->user->data['session_id'],
						'hotlink_id'	=> $hotlink_id]);
					$this->db->sql_query($sql);
				}
				else
				{
					$hotlink_id = '';
				}

				$error = [];

				$s_hidden_fields = [
					'df_id'			=> $df_id,
					'modcp'			=> $modcp,
					'cat_id'		=> $cat_id,
					'hotlink_id'	=> $hotlink_id,
				];

				if (!$ver_can_edit && !$user_can_alltimes_load)
				{
					$sql_ver_where = ' AND v.ver_active = 1 ';
				}
				else
				{
					$sql_ver_where = '';
				}

				$sql = 'SELECT v.ver_id, v.ver_change_time, v.ver_version, u.username FROM ' . DL_VERSIONS_TABLE . ' v
					LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = v.ver_change_user
					WHERE v.dl_id = ' . (int) $df_id . $sql_ver_where . '
					ORDER BY v.ver_version DESC, v.ver_change_time DESC';
				$result = $this->db->sql_query($sql);
				$total_releases = $this->db->sql_affectedrows($result);

				if ($total_releases)
				{
					$s_select_version = '<select name="file_version">';
					$s_select_version .= '<option value="0" selected="selected">' . $this->language->lang('DL_VERSION_CURRENT') . '</option>';
					$version_array = [];

					while ($row = $this->db->sql_fetchrow($result))
					{
						$ver_id			= $row['ver_id'];
						$ver_version	= $row['ver_version'];
						$ver_time		= $this->user->format_date($row['ver_change_time']);
						$ver_username	= ($row['username']) ? ' [ ' . $row['username'] . ' ]' : '';

						$version_array[$ver_version . ' - ' . $ver_time . $ver_username] = $ver_id;
					}

					natsort($version_array);
					$version_array = array_unique(array_reverse($version_array));
					foreach($version_array as $key => $value)
					{
						$s_select_version .= '<option value="' . $value . '">' . $key . '</option>';
					}

					$s_select_version .= '</select>';
				}
				else
				{
					$s_select_version = '<input type="hidden" name="file_version" value="0" />';
				}

				$this->db->sql_freeresult($result);

				$this->template->assign_block_vars('download_button', [
					'S_HIDDEN_FIELDS'	=> build_hidden_fields($s_hidden_fields),
					'S_DL_WINDOW'		=> ($dl_files['extern'] && $this->config['dl_ext_new_window']) ? 'target="_blank"' : '',
					'S_DL_VERSION'		=> $s_select_version,
					'U_DOWNLOAD'		=> $this->helper->route('oxpus_dlext_details'),
				]);

				add_form_key('dl_load', '_DOWNLOAD');

				if ($captcha_active)
				{
					$code_match = false;

					$this->template->assign_var('S_VC', true);

					$captcha = $this->phpbb_container->get('captcha.factory')->get_instance($this->config['captcha_plugin']);
					$captcha->init(CONFIRM_POST);

					if ($submit)
					{
						$vc_response = $captcha->validate();

						if ($vc_response !== false)
						{
							$error[] = $vc_response;
						}

						if (empty($error))
						{
							$captcha->reset();
							$code_match = true;
						}
						else
						{
							$this->template->assign_block_vars('dl_error', [
								'DL_ERROR' => $error[0]
							]);
						}
					}

					if (!$code_match)
					{
						$this->template->assign_vars([
							'CAPTCHA_TEMPLATE'	=> $captcha->get_template(),
							'S_HIDDEN_FIELDS'	=> build_hidden_fields($captcha->get_hidden_fields()),
						]);
					}
				}
				else
				{
					$code_match = true;
				}

				if ($submit && $code_match)
				{
					// check form
					if (!check_form_key('dl_load'))
					{
						trigger_error($this->language->lang('FORM_INVALID'), E_USER_WARNING);
					}

					$code = $this->request->variable('confirm_id', '');
	
					if (!$code)
					{
						$code = $this->request->variable('confirm_code', '');
					}

					if ($code)
					{	
						$sql = 'INSERT INTO ' . DL_HOTLINK_TABLE . ' ' . $this->db->sql_build_array('INSERT', [
							'user_id'		=> $this->user->data['user_id'],
							'session_id'	=> $this->user->data['session_id'],
							'hotlink_id'	=> $code,
							'code'			=> 'dlvc']);
						$this->db->sql_query($sql);
					}

					/**
					 * Additional actions before redirect to download the file / open the webpage
					 *
					 * @event oxpus.dlext.details_download_before
					 * @var int		hotlink_id		hotlink protection ID
					 * @var string	code			confirmation code
					 * @var int		df_id			download ID
					 * @var bool	modcp			current modcp action
					 * @var int		cat_id			download category ID
					 * @var int		file_version	download version ID
					 * @since 8.1.1
					 */
					$vars = array(
						'hotlink_id',
						'code',
						'df_id',
						'modcp',
						'cat_id',
						'file_version',
					);
					extract($this->phpbb_dispatcher->trigger_event('oxpus.dlext.details_download_before', compact($vars)));

					redirect($this->helper->route('oxpus_dlext_load', ['hotlink_id' => $hotlink_id, 'code' => $code, 'df_id' => $df_id, 'modcp' => $modcp, 'cat_id' => $cat_id, 'file_version' => $file_version]));
				}
			}
		}

		/*
		* Display the link ro report the download as broken
		*/
		$s_report_broken = false;
		if ($this->config['dl_report_broken'] && !$dl_files['broken'] && !$this->user->data['is_bot'])
		{
			if ($this->user->data['is_registered'] || (!$this->user->data['is_registered'] && $this->config['dl_report_broken'] == 1))
			{
				$s_report_broken = true;
			}
		}

		/*
		* Second part of the report link
		*/
		$s_dl_broken_mod = false;
		$s_dl_broken_cur = false;
		if ($dl_files['broken'] && !$this->user->data['is_bot'])
		{
			if ($index[$cat_id]['auth_mod'] || $cat_auth['auth_mod'] || ($this->auth->acl_get('a_') && $this->user->data['is_registered']))
			{
				$s_dl_broken_mod = true;
			}

			if (!$this->config['dl_report_broken_message'] || ($this->config['dl_report_broken_lock'] && $this->config['dl_report_broken_message']))
			{
				$s_dl_broken_cur = true;
			}
		}

		/*
		* Enabled Bug Tracker for this download category?
		*/
		$s_bug_tracker = false;
		if ($index[$cat_id]['bug_tracker'] && !$this->user->data['is_bot'] && $this->user->data['is_registered'])
		{
			$s_bug_tracker = true;
		}

		/*
		* Thumbnails? Okay, getting some thumbs, if they exists...
		*/
		if ($index[$cat_id]['allow_thumbs'] && $this->config['dl_thumb_fsize'])
		{
			$first_thumb_exists	= false;
			$more_thumbs_exists	= false;

			if (@file_exists(DL_EXT_FILEBASE_PATH . 'thumbs/' . $dl_files['thumbnail']) && $dl_files['thumbnail'])
			{
				if (!$total_images)
				{
					$this->template->assign_var('S_DL_POPUPIMAGE', true);
				}

				$first_thumb_exists = true;
			}

			if (!empty($thumbs_ary))
			{
				$more_thumbs_exists = true;
			}

			if ($first_thumb_exists)
			{
				$first_thumbs = [0 => [
					'img_id'	=> 0,
					'dl_id'		=> $df_id,
					'img_name'	=> $dl_files['thumbnail'],
					'img_title'	=> $description,
				]];
	
				if ($more_thumbs_exists)
				{
					$first_thumbs += $thumbs_ary;
				}

				$thumbs_ary = $first_thumbs;
				unset($first_thumbs);
			}

			if ($first_thumb_exists || $more_thumbs_exists)
			{
				$drop_images = [];

				foreach ($thumbs_ary as $key => $value)
				{
					$pic_path = DL_EXT_FILEBASE_PATH . 'thumbs/' . $thumbs_ary[$key]['img_name'];
					if (@file_exists($pic_path))
					{
						$pic_path = base64_encode($pic_path);
						$this->template->assign_block_vars('thumbnail', [
							'THUMBNAIL_LINK'	=> $this->helper->route('oxpus_dlext_thumbnail', ['thumbnail' => $pic_path, 'disp_art' => false]),
							'THUMBNAIL_PIC'		=> $this->helper->route('oxpus_dlext_thumbnail', ['thumbnail' => $pic_path, 'disp_art' => true]),
							'THUMBNAIL_NAME'	=> $thumbs_ary[$key]['img_title'],
						]);
					}
					else
					{
						$drop_images[] = $thumbs_ary[$key]['img_id'];
					}
				}

				if (!empty($drop_images))
				{
					$sql = 'DELETE FROM ' . DL_IMAGES_TABLE . '
						WHERE dl_id = ' . (int) $df_id . '
							AND ' . $this->db->sql_in_set('img_id', array_map('intval', $drop_images));
					$this->db->sql_query($sql);
				}
			}
		}

		/*
		* Urgh, the real filetime..... Heavy information, very important :-D
		*/
		$s_real_filetime = false;
		if ($this->config['dl_show_real_filetime'] && !$dl_files['extern'])
		{
			if (@file_exists(DL_EXT_FILEBASE_PATH. 'downloads/' . $index[$cat_id]['cat_path'] . $real_file))
			{
				$s_real_filetime = true;
			}
		}

		/*
		* Like to rate? Do it!
		*/
		$rating_points = $dl_files['rating'];
		$s_rating_perm = false;

		if ($this->config['dl_enable_rate'])
		{
			if ((!$rating_points || $rating_access) && $this->user->data['is_registered'])
			{
				$s_rating_perm = true;
			}
		}

		/*
		* Some user like to link to each favorite page, download, programm, friend, house friend... ahrrrrrrggggg...
		*/
		if ($this->user->data['is_registered'] && !$this->config['dl_disable_email'])
		{
			$sql = 'SELECT fav_id FROM ' . DL_FAVORITES_TABLE . '
				WHERE fav_dl_id = ' . (int) $df_id . '
					AND fav_user_id = ' . (int) $this->user->data['user_id'];
			$result = $this->db->sql_query($sql);
			$fav_id = $this->db->sql_fetchfield('fav_id');
			$this->db->sql_freeresult($result);

			$this->template->assign_var('S_FAV_BLOCK', true);

			if ($fav_id)
			{
				$l_favorite = $this->language->lang('DL_FAVORITE_DROP');
				$u_favorite = $this->helper->route('oxpus_dlext_unfav', ['df_id' => $df_id, 'cat_id' => $cat_id, 'fav_id' => $fav_id]);
				$this->template->assign_var('S_FAV_ACTIVE', true);
			}
			else
			{
				$l_favorite = $this->language->lang('DL_FAVORITE_ADD');
				$u_favorite = $this->helper->route('oxpus_dlext_fav', ['df_id' => $df_id, 'cat_id' => $cat_id, 'fav_id' => $fav_id]);
			}
		}
		else
		{
			$l_favorite = '';
			$u_favorite = '';
		}

		$file_id	= $dl_files['id'];
		$cat_id		= $dl_files['cat'];

		/*
		* Can we edit the download? Yes we can, or not?
		*/
		$s_edit_button = false;
		$s_edit_thumbs = false;
		if (!$this->user->data['is_bot'] && $this->dlext_auth->user_auth($dl_files['cat'], 'auth_mod') || ($this->config['dl_edit_own_downloads'] && $dl_files['add_user'] == $this->user->data['user_id']))
		{
			$s_edit_button = true;

			if ($index[$cat_id]['allow_thumbs'] && $this->config['dl_thumb_fsize'])
			{
				$s_edit_thumbs = true;
			}
		}

		/*
		* Send the values to the template to be able to read something *g*
		*/
		$this->template->assign_vars([
			'HASH_TAB'			=> $hash_tab,
			'FAVORITE'			=> $l_favorite,
			'EDIT_IMG'			=> $this->language->lang('DL_EDIT_FILE'),
			'CAT_RULE'			=> (isset($cat_rule)) ? $cat_rule : '',
			'CAT_TRAFFIC'		=> (isset($cat_traffic)) ? $this->language->lang('DL_CAT_TRAFFIC_MAIN', $cat_traffic) : '',
			'VER_TAB'			=> ($ver_tab) ? true : false,

			'DESCRIPTION'			=> $description,
			'MINI_IMG'				=> $mini_icon,
			'HACK_VERSION'			=> $hack_version,
			'LONG_DESC'				=> $long_desc,
			'STATUS'				=> $status,
			'FILE_SIZE'				=> $file_size_out,
			'FILE_KLICKS'			=> $file_klicks,
			'FILE_OVERALL_KLICKS'	=> $file_overall_klicks,
			'FILE_NAME'				=> ($dl_files['extern']) ? $this->language->lang('DL_EXTERN') : $file_name,
			'LAST_TIME'				=> $last_time,
			'ADD_USER'				=> ($add_user != '') ? $this->language->lang('DL_ADD_USER', $add_time, $add_user) : '',
			'CHANGE_USER'			=> ($change_user != '') ? $this->language->lang('DL_CHANGE_USER', $change_time, $change_user) : '',
			'REAL_FILETIME'			=> $this->user->format_date(@filemtime(DL_EXT_FILEBASE_PATH. 'downloads/' . $index[$cat_id]['cat_path'] . $real_file)),
			'REAL_FILETIME_RFC'		=> gmdate(DATE_RFC3339, @filemtime(DL_EXT_FILEBASE_PATH. 'downloads/' . $index[$cat_id]['cat_path'] . $real_file)),
			'RATING_IMG'			=> $this->dlext_format->rating_img($rating_points, $s_rating_perm, $df_id, $ratings),
			'DF_ID'					=> $df_id,
			'PHPEX'					=> $this->php_ext,
			'BROKEN'				=> $broken,
			'MOD_TODO'				=> $mod_todo,
			'MOD_TEST'				=> ($s_mod_test) ? $mod_test : false,
			'MOD_DESC'				=> ($s_mod_desc) ? $mod_desc : false,
			'MOD_WARNING'			=> ($s_mod_warning) ? $mod_warning : false,
			'MOD_REQUIRE'			=> ($s_mod_require) ? $mod_require : false,
			'HACK_AUTHOR'			=> ($hack_author_email != '') ? '<a href="mailto:' . $hack_author_email . '">' . $hack_author . '</a>' : $hack_author,
			'HACK_AUTHOR_WEBSITE'	=> ($hack_author_website != '') ? ' [ <a href="' . $hack_author_website . '">' . $this->language->lang('WEBSITE') . '</a> ]' : '',
			'HACK_DL_URL'			=> ($hack_dl_url != '') ? '<a href="' . $hack_dl_url . '">' . $this->language->lang('DL_DOWNLOAD') . '</a>' : 'n/a',

			'S_DL_ACTION'		=> $this->helper->route('oxpus_dlext_details'),
			'S_ENABLE_RATE'		=> (isset($this->config['dl_enable_rate']) && $this->config['dl_enable_rate']) ? true : false,
			'S_SHOW_TOPIC_LINK'	=> ($dl_files['dl_topic']) ? true : false,
			'S_DL_DETAIL_JS'	=> true,

			'S_DL_POPUPIMAGE'	=> $s_dl_popupimage,
			'S_DL_USERBAN'		=> $s_dl_userban,
			'S_CAT_RULE'		=> $s_cat_rule,
			'S_CAT_TRAFFIC'		=> $s_cat_traffic,
			'S_COMMENTS_TAB'	=> $s_comments_tab,
			'S_TRAFFICFREE_DL'	=> $s_trafficfree_dl,
			'S_REPORT_BROKEN'	=> $s_report_broken,
			'S_DL_BROKEN_MOD'	=> $s_dl_broken_mod,
			'S_DL_BROKEN_CUR'	=> $s_dl_broken_cur,
			'S_BUG_TRACKER'		=> $s_bug_tracker,
			'S_REAL_FILETIME'	=> $s_real_filetime,
			'S_EDIT_BUTTON'		=> $s_edit_button,
			'S_EDIT_THUMBS'		=> $s_edit_thumbs,
			'S_MOD_TODO'		=> $s_mod_todo,
			'S_HACKLIST'		=> $s_hacklist,
			'S_OPEN_PANEL'		=> ($view == 'comment' && $s_comments_tab) ? 2 : 0,
			'S_POST_COMMENT'	=> ($s_comments_tab && !$deny_post) ? true : false,

			'U_REPORT'			=> $this->helper->route('oxpus_dlext_unbroken', ['df_id' => $df_id, 'cat_id' => $cat_id]),
			'U_BROKEN_DOWNLOAD' => $this->helper->route('oxpus_dlext_broken', ['df_id' => $df_id, 'cat_id' => $cat_id]),
			'U_BUG_TRACKER'		=> $this->helper->route('oxpus_dlext_tracker', ['df_id' => $df_id]),

			'U_TOPIC'			=> append_sid($this->root_path . 'viewtopic.' . $this->php_ext, 't=' . $dl_files['dl_topic']),
			'U_EDIT'			=> $this->helper->route('oxpus_dlext_mcp_edit', ['df_id' => $file_id, 'cat_id' => $cat_id]),
			'U_EDIT_THUMBS'		=> $this->helper->route('oxpus_dlext_thumbs', ['df_id' => $file_id, 'cat_id' => $cat_id]),
			'U_FAVORITE'		=> $u_favorite,
			'U_DL_SEARCH'		=> $this->helper->route('oxpus_dlext_search', ['view' => 'search']),
			'U_DL_AJAX'			=> $this->helper->route('oxpus_dlext_ajax'),
		]);

		/**
		* Custom Download Fields
		* Taken from memberlist.php phpBB 3.0.7-PL1
		*/
		$dl_fields = [];
		include($this->ext_path . 'phpbb/includes/fields.' . $this->php_ext);
		$cp = new \oxpus\dlext\phpbb\includes\custom_profile();

		$dl_fields = $cp->generate_profile_fields_template('grab', $file_id);
		$dl_fields = (isset($dl_fields[$file_id])) ? $cp->generate_profile_fields_template('show', false, $dl_fields[$file_id]) : [];

		if (!empty($dl_fields['row']))
		{
			$this->template->assign_var('S_DL_FIELDS', true);
			$this->template->assign_vars($dl_fields['row']);

			if (!empty($dl_fields['blockrow']))
			{
				foreach ($dl_fields['blockrow'] as $field_data)
				{
					$this->template->assign_block_vars('custom_fields', $field_data);
				}
			}
		}

		/**
		 * Calculate or Display additional data
		 *
		 * @event oxpus.dlext.details_display_after
		 * @var int		df_id			download ID
		 * @var int		cat_id			download category ID
		 * @var array	dl_files		array of download's data
		 * @since 8.1.0-RC2
		 */
		$vars = array(
			'df_id',
			'cat_id',
			'dl_files',
		);
		extract($this->phpbb_dispatcher->trigger_event('oxpus.dlext.details_display_after', compact($vars)));

		$detail_cat_names = [
			0 => $this->language->lang('DL_DETAIL'),
			1 => ($ver_tab) ? $this->language->lang('DL_VERSIONS') : '',
			2 => ($s_comments_tab) ? $this->language->lang('DL_COMMENTS') : '',
		];

		for ($i = 0; $i < count($detail_cat_names); ++$i)
		{
			if ($detail_cat_names[$i])
			{
				$this->template->assign_block_vars('category', [
					'CAT_NAME'			=> $detail_cat_names[$i],
					'CAT_ID'			=> $i,
					'COMMENTS_COUNT'	=> $real_comment_exists,
				]);
			}
		}

		/**
		* Find similar downloads
		*/
		if ($this->config['dl_similar_dl'])
		{
			$sql = 'SELECT id, description, desc_uid, desc_bitfield, desc_flags FROM ' . DOWNLOADS_TABLE . "
				WHERE MATCH (description) AGAINST ('" . $this->db->sql_escape($description) . "')
					AND id <> " . (int) $df_id . '
				ORDER BY description';
			$result = $this->db->sql_query_limit($sql, $this->config['dl_similar_limit']);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$similar_id		= $row['id'];
				$similar_desc	= $row['description'];
				$desc_uid		= $dl_files['desc_uid'];
				$desc_bitfield	= $dl_files['desc_bitfield'];
				$desc_flags		= (isset($dl_files['desc_flags'])) ? $dl_files['desc_flags'] : 0;
				$similar_desc	= generate_text_for_display($similar_desc, $desc_uid, $desc_bitfield, $desc_flags);

				$this->template->assign_block_vars('similar_dl', [
					'DOWNLOAD'		=> $similar_desc,
					'U_DOWNLOAD'	=> $this->helper->route('oxpus_dlext_details', ['df_id' => $similar_id]),
				]);
			}

			$this->db->sql_freeresult($result);
		}

		$notification->delete_notifications([
			'oxpus.dlext.notification.type.dlext',
			'oxpus.dlext.notification.type.update',
		], $df_id, false, $this->user->data['user_id']);

		/*
		* include the mod footer
		*/
		$dl_footer = $this->phpbb_container->get('oxpus.dlext.footer');
		$dl_footer->set_parameter($nav_view, $cat_id, $df_id, $index);
		$dl_footer->handle();
	}
}
