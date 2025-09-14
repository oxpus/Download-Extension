<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\controller;

class details
{
	/* phpbb objects */
	protected $root_path;
	protected $php_ext;
	protected $extension_manager;
	protected $db;
	protected $config;
	protected $helper;
	protected $request;
	protected $template;
	protected $user;
	protected $language;
	protected $dispatcher;
	protected $notification;
	protected $captcha;
	protected $filesystem;

	/* extension owned objects */
	protected $ext_path;

	protected $dlext_auth;
	protected $dlext_comments;
	protected $dlext_files;
	protected $dlext_format;
	protected $dlext_main;
	protected $dlext_status;
	protected $dlext_constants;
	protected $dlext_footer;
	protected $dlext_fields;

	protected $dlext_table_dl_comments;
	protected $dlext_table_dl_favorites;
	protected $dlext_table_dl_hotlink;
	protected $dlext_table_dl_images;
	protected $dlext_table_dl_notraf;
	protected $dlext_table_dl_ratings;
	protected $dlext_table_dl_versions;
	protected $dlext_table_downloads;

	/**
	 * Constructor
	 *
	 * @param string								$root_path
	 * @param string								$php_ext
	 * @param \phpbb\extension\manager				$extension_manager
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\config\config					$config
	 * @param \phpbb\controller\helper				$helper
	 * @param \phpbb\request\request 				$request
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\user							$user
	 * @param \phpbb\language\language				$language
	 * @param \phpbb\event\dispatcher_interface		$dispatcher
	 * @param \phpbb\notification\manager			$notification
	 * @param \phpbb\captcha\factory				$captcha
	 * @param \phpbb\filesystem\filesystem			$filesystem
	 * @param \oxpus\dlext\core\auth				$dlext_auth
	 * @param \oxpus\dlext\core\comments			$dlext_comments
	 * @param \oxpus\dlext\core\files				$dlext_files
	 * @param \oxpus\dlext\core\format				$dlext_format
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\status				$dlext_status
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param \oxpus\dlext\core\helpers\footer		$dlext_footer
	 * @param \oxpus\dlext\core\fields\fields		$dlext_fields
	 * @param string								$dlext_table_dl_favorites
	 * @param string								$dlext_table_dl_hotlink
	 * @param string								$dlext_table_dl_images
	 * @param string								$dlext_table_dl_notraf
	 * @param string								$dlext_table_dl_ratings
	 * @param string								$dlext_table_dl_versions
	 * @param string								$dlext_table_downloads
	 */
	public function __construct(
		$root_path,
		$php_ext,
		\phpbb\extension\manager $extension_manager,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\language\language $language,
		\phpbb\event\dispatcher_interface $dispatcher,
		\phpbb\notification\manager $notification,
		\phpbb\captcha\factory $captcha,
		\phpbb\filesystem\filesystem $filesystem,
		\oxpus\dlext\core\auth $dlext_auth,
		\oxpus\dlext\core\comments $dlext_comments,
		\oxpus\dlext\core\files $dlext_files,
		\oxpus\dlext\core\format $dlext_format,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\status $dlext_status,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		\oxpus\dlext\core\helpers\footer $dlext_footer,
		\oxpus\dlext\core\fields\fields $dlext_fields,
		$dlext_table_dl_favorites,
		$dlext_table_dl_hotlink,
		$dlext_table_dl_images,
		$dlext_table_dl_notraf,
		$dlext_table_dl_ratings,
		$dlext_table_dl_versions,
		$dlext_table_downloads
	)
	{
		$this->root_path				= $root_path;
		$this->php_ext 					= $php_ext;
		$this->extension_manager 		= $extension_manager;
		$this->db 						= $db;
		$this->config 					= $config;
		$this->helper 					= $helper;
		$this->request					= $request;
		$this->template 				= $template;
		$this->user 					= $user;
		$this->language					= $language;
		$this->dispatcher				= $dispatcher;
		$this->notification				= $notification;
		$this->captcha					= $captcha;
		$this->filesystem				= $filesystem;

		$this->ext_path					= $this->extension_manager->get_extension_path('oxpus/dlext', $dlext_constants::DL_TRUE);

		$this->dlext_table_dl_favorites	= $dlext_table_dl_favorites;
		$this->dlext_table_dl_hotlink	= $dlext_table_dl_hotlink;
		$this->dlext_table_dl_images	= $dlext_table_dl_images;
		$this->dlext_table_dl_notraf	= $dlext_table_dl_notraf;
		$this->dlext_table_dl_ratings	= $dlext_table_dl_ratings;
		$this->dlext_table_dl_versions	= $dlext_table_dl_versions;
		$this->dlext_table_downloads	= $dlext_table_downloads;

		$this->dlext_auth				= $dlext_auth;
		$this->dlext_comments			= $dlext_comments;
		$this->dlext_files				= $dlext_files;
		$this->dlext_format				= $dlext_format;
		$this->dlext_main				= $dlext_main;
		$this->dlext_status				= $dlext_status;
		$this->dlext_constants			= $dlext_constants;
		$this->dlext_footer				= $dlext_footer;
		$this->dlext_fields				= $dlext_fields;
	}

	public function handle()
	{
		$this->dlext_main->dl_handle_active();

		$index = $this->dlext_main->full_index();

		$file_version	= $this->request->variable('file_version', 0);
		$submit			= $this->request->variable('submit', '');
		$cancel			= $this->request->variable('cancel', '');
		$view			= $this->request->variable('view', '');
		$action			= $this->request->variable('action', '');
		$df_id			= $this->request->variable('df_id', 0);
		$cat_id			= $this->request->variable('cat_id', 0);
		$dl_id			= $this->request->variable('dl_id', 0);
		$start			= $this->request->variable('start', 0);
		$modcp			= $this->request->variable('modcp', 0);

		/*
		* wanna have smilies ;-)
		*/
		if ($action == 'smilies')
		{
			if (!function_exists('generate_smilies'))
			{
				include($this->root_path . '/includes/functions_posting.' . $this->php_ext);
			}
			generate_smilies('window', 0);
		}

		/*
		* set the right values for comments
		*/
		if (in_array($action, ['view', 'save', 'delete', 'edit']))
		{
			$nav_view = 'comment';
		}
		else
		{
			$nav_view = 'details';
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
		$dl_files = $this->dlext_files->all_files(0, [], [], $df_id, $modcp, ['*']);

		if (!$dl_files)
		{
			redirect($this->helper->route('oxpus_dlext_index'));
		}

		if (!$dl_files['id'])
		{
			trigger_error('DL_NO_PERMISSION');
		}

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
		$file_name			= $dl_files['file_name'];

		$file_status		= $check_status['file_status'];
		$file_load			= $check_status['file_auth'];

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

		$mini_icon		= $this->dlext_status->mini_status_file($cat_id, $df_id);

		$cat_auth		= $this->dlext_auth->dl_cat_auth($cat_id);

		if (!$this->dlext_auth->user_auth($cat_id, 'auth_view'))
		{
			trigger_error('DL_NO_PERMISSION');
		}

		/*
		* Prepare all permissions for the current user
		*/
		$user_can_alltimes_load = $this->dlext_constants::DL_FALSE;
		$user_is_mod = $this->dlext_constants::DL_FALSE;

		if (($cat_auth['auth_mod'] || $this->dlext_auth->user_admin()))
		{
			$modcp = ($modcp) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
			$user_can_alltimes_load = $this->dlext_constants::DL_TRUE;
			$user_is_mod = $this->dlext_constants::DL_TRUE;
		}

		if (!$user_is_mod)
		{
			$modcp = $this->dlext_constants::DL_FALSE;
		}

		$captcha_active = $this->dlext_auth->get_captcha_status($this->config['dl_download_vc'], $cat_id);

		$this->language->add_lang('posting');

		/*
		* Check saved thumbs
		*/
		$s_dl_popupimage = $this->dlext_constants::DL_FALSE;

		$sql = 'SELECT * FROM ' . 	$this->dlext_table_dl_images . '
			WHERE dl_id = ' . (int) $df_id;
		$result = $this->db->sql_query($sql);
		$total_images = $this->db->sql_affectedrows();

		$thumbs_ary = [];
		$i = 1;

		if ($total_images)
		{
			$s_dl_popupimage = $this->dlext_constants::DL_TRUE;

			while ($row = $this->db->sql_fetchrow($result))
			{
				$thumbs_ary[$i] = $row;
				$thumbs_ary[$i]['img_type'] = 'more';
				$i++;
			}
		}

		$this->db->sql_freeresult($result);

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

			$s_cat_rule = $this->dlext_constants::DL_TRUE;
		}
		else
		{
			$s_cat_rule = $this->dlext_constants::DL_FALSE;
			$cat_rule = '';
		}

		/*
		* Cat Traffic?
		*/
		$cat_traffic = 0;
		$s_cat_traffic = $this->dlext_constants::DL_FALSE;

		if (!$this->config['dl_traffic_off'])
		{
			if ($this->user->data['is_registered'])
			{
				$cat_overall_traffic = $this->config['dl_overall_traffic'];
				$cat_limit = $this->dlext_constants->get_value('overall_traffics');
			}
			else
			{
				$cat_overall_traffic = $this->config['dl_overall_guest_traffic'];
				$cat_limit = $this->dlext_constants->get_value('guests_traffics');
			}

			if (isset($index[$cat_id]['cat_traffic']) && isset($index[$cat_id]['cat_traffic_use']))
			{
				$cat_traffic = $index[$cat_id]['cat_traffic'] - $index[$cat_id]['cat_traffic_use'];

				if ($index[$cat_id]['cat_traffic'] && $cat_traffic > 0)
				{
					$cat_traffic = ($cat_traffic > $cat_overall_traffic && $cat_limit == $this->dlext_constants::DL_TRUE) ? $cat_overall_traffic : $cat_traffic;
					$cat_traffic = $this->dlext_format->dl_size($cat_traffic);

					$s_cat_traffic = $this->dlext_constants::DL_FALSE;
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
		$ratings = 0;
		$rating_access = $this->dlext_constants::DL_FALSE;
		$user_have_rated = $this->dlext_constants::DL_FALSE;

		if ($this->config['dl_enable_rate'])
		{
			$sql = 'SELECT dl_id, user_id FROM ' . $this->dlext_table_dl_ratings . '
				WHERE dl_id = ' . (int) $df_id;
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				++$ratings;
				$user_have_rated = ($row['user_id'] == $this->user->data['user_id']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
			}

			$this->db->sql_freeresult($result);

			if ($this->user->data['is_registered'] && !$user_have_rated)
			{
				$rating_access = $this->dlext_constants::DL_TRUE;
			}
		}

		/*
		* fetch last comment, if exists
		*/
		$s_comments_tab = $this->dlext_constants::DL_FALSE;

		if (!empty($index[$cat_id]['comments']) && $index[$cat_id]['comments'] && $this->dlext_auth->cat_auth_comment_read($cat_id))
		{
			$allow_manage	= $this->dlext_comments->get_auth_comment_manage($cat_id);
			$deny_post		= $this->dlext_comments->get_auth_comment_post($cat_id);

			$s_comments_tab = $this->dlext_constants::DL_TRUE;

			if ($action == 'save')
			{
				$action = $this->dlext_comments->save_comment($cat_id, $df_id, $dl_id);
			}

			if ($action == 'delete' && $allow_manage)
			{
				$action = $this->dlext_comments->delete_comment($cat_id, $df_id, $dl_id);
			}

			$this->dlext_comments->display_post_form($action, $cat_id, $df_id, $dl_id);

			$real_comment_exists = $this->dlext_comments->display_comments($cat_id, $df_id, $start);

			if ($nav_view == 'comment')
			{
				$this->template->assign_var('S_DL_DISPLAY_COMMENTS', $this->dlext_constants::DL_TRUE);
			}
		}

		/*
		* Check existing hashes and build the hash table if the category allowes it
		*/
		$hash_method = $this->config['dl_file_hash_algo'];
		$hash_table_tmp = [];
		$hash_table = [];
		$hash_ary = [];
		$hash_tab = $this->dlext_constants::DL_FALSE;
		$ver_tab = $this->dlext_constants::DL_FALSE;
		$ver_can_edit = $this->dlext_constants::DL_FALSE;

		if (($user_is_mod || $this->dlext_auth->user_admin() || $this->user->data['user_type'] == USER_FOUNDER) || ($this->config['dl_edit_own_downloads'] && $dl_files['add_user'] == $this->user->data['user_id']))
		{
			$ver_can_edit = $this->dlext_constants::DL_TRUE;
		}

		if (!$dl_files['extern'])
		{
			if (!$dl_files['file_hash'])
			{
				if ($dl_files['real_file'] && $this->filesystem->exists($this->dlext_constants->get_value('files_dir') . '/downloads/' . $index[$cat_id]['cat_path'] . $dl_files['real_file']))
				{
					$dl_files['file_hash'] = $this->dlext_format->dl_hash($this->dlext_constants->get_value('files_dir') . '/downloads/' . $index[$cat_id]['cat_path'] . $dl_files['real_file'], 'file', $hash_method);
					$sql = 'UPDATE ' . $this->dlext_table_downloads . ' SET ' . $this->db->sql_build_array('UPDATE', [
						'file_hash' => $dl_files['file_hash']
					]) . ' WHERE id = ' . (int) $df_id;
					$this->db->sql_query($sql);
				}
			}

			if (!empty($index[$cat_id]['show_file_hash']) && $index[$cat_id]['show_file_hash'])
			{
				$dl_key = $dl_files['description'] . (($dl_files['hack_version']) ? ' ' . $dl_files['hack_version'] : ' (' . $this->language->lang('DL_CURRENT_VERSION') . ')');
				$hash_table_tmp[$dl_key]['hash'] = ($dl_files['file_hash']) ? $dl_files['file_hash'] : '';
				$hash_table_tmp[$dl_key]['file'] = $dl_files['file_name'];
				$hash_table_tmp[$dl_key]['type'] = ($dl_files['file_hash']) ? $hash_method : $this->language->lang('DL_FILE_NOT_FOUND', $dl_files['real_file'], $this->dlext_constants->get_value('files_dir') . '/downloads/' . $index[$cat_id]['cat_path']);
				$hash_ary[] = $dl_key;
			}

			$sql = 'SELECT * FROM ' . $this->dlext_table_dl_versions . '
				WHERE dl_id = ' . (int) $df_id . '
				ORDER BY ver_version DESC, ver_change_time DESC';
			$result = $this->db->sql_query($sql);
			$total_releases = $this->db->sql_affectedrows();

			if ($total_releases)
			{
				$version_array = [];
				$ver_key_ary = [];

				while ($row = $this->db->sql_fetchrow($result))
				{
					$ver_file_hash = $row['ver_file_hash'];

					if (!$ver_file_hash)
					{
						if ($row['ver_real_file'] && $this->filesystem->exists($this->dlext_constants->get_value('files_dir') . '/downloads/' . $index[$cat_id]['cat_path'] . $row['ver_real_file']))
						{
							$ver_file_hash = $this->dlext_format->dl_hash($this->dlext_constants->get_value('files_dir') . '/downloads/' . $index[$cat_id]['cat_path'] . $row['ver_real_file'], 'file', $hash_method);
							$sql = 'UPDATE ' . $this->dlext_table_dl_versions . ' SET ' . $this->db->sql_build_array('UPDATE', [
								'ver_file_hash' => $ver_file_hash
							]) . ' WHERE ver_id = ' . (int) $row['ver_id'];
							$this->db->sql_query($sql);
						}
					}

					$dl_key = $dl_files['description'] . (($row['ver_version']) ? ' ' . $row['ver_version'] : ' (' . $this->user->format_date($row['ver_change_time']) . ')');

					if (!empty($index[$cat_id]['show_file_hash']) && $index[$cat_id]['show_file_hash'] && ($row['ver_active'] || $ver_can_edit))
					{
						$hash_table_tmp[$dl_key]['hash'] = ($ver_file_hash) ? $ver_file_hash : '';
						$hash_table_tmp[$dl_key]['file'] = $row['ver_file_name'];
						$hash_table_tmp[$dl_key]['type'] = ($ver_file_hash) ? $hash_method : $this->language->lang('DL_FILE_NOT_FOUND', $row['ver_real_file'], $this->dlext_constants->get_value('files_dir') . '/downloads/' . $index[$cat_id]['cat_path']);
						$hash_ary[] = $dl_key;
					}

					if ($row['ver_active'] || $ver_can_edit)
					{
						$ver_tab = $this->dlext_constants::DL_TRUE;
						$ver_desc = censor_text($row['ver_text']);
						$flags = (isset($row['ver_flags'])) ? $row['ver_flags'] : 0;
						$ver_desc = generate_text_for_display($ver_desc, $row['ver_uid'], $row['ver_bitfield'], $flags);
						if (strlen($ver_desc) > 150)
						{
							$ver_desc = substr($ver_desc, 0, $this->dlext_constants::DL_VER_DESC_LIMIT) . ' [...]';
						}

						$ver_tmp = ($row['ver_version']) ? $row['ver_version'] : $row['ver_change_time'];
						$ver_key_ary[] = $ver_tmp;

						$version_array[$ver_tmp] = [
							'DL_VER_TITLE'			=> $dl_key,
							'DL_VER_TIME'			=> $this->user->format_date($row['ver_change_time']),
							'DL_VER_TIME_RFC'		=> gmdate(DATE_RFC3339, $row['ver_change_time']),
							'DL_VER_DESC'			=> $ver_desc,
							'DL_VER_ACTIVE'			=> $row['ver_active'],
							'S_DL_USER_PERM'		=> $ver_can_edit,
							'U_DL_VERSION'			=> $this->helper->route('oxpus_dlext_version', ['action' => 'detail', 'ver_id' => $row['ver_id'], 'df_id' => $df_id]),
							'U_DL_VERSION_EDIT'		=> $this->helper->route('oxpus_dlext_version', ['action' => 'edit', 'ver_id' => $row['ver_id'], 'df_id' => $df_id]),
						];
					}
				}

				natsort($ver_key_ary);
				$ver_key_ary = array_reverse($ver_key_ary);
				foreach ($ver_key_ary as $value)
				{
					$this->template->assign_block_vars('dl_ver_cell', $version_array[$value]);
				}
				unset($ver_key_ary);
				unset($version_array);
			}

			natsort($hash_ary);
			$hash_ary = array_unique(array_reverse($hash_ary));
			foreach ($hash_ary as $value)
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
					$this->template->assign_block_vars('dl_hash_row', [
						'DL_VERSION'		=> $key,
						'DL_FILE_NAME'		=> $value['file'],
						'DL_HASH_METHOD'	=> $value['type'],
						'DL_HASH'			=> $value['hash'],
					]);
				}

				$hash_tab = $this->dlext_constants::DL_TRUE;
			}
		}

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
			$file_size_out = $this->language->lang('DL_NOT_AVAILABLE');
		}

		$file_klicks			= $dl_files['klicks'];
		$file_overall_klicks	= $dl_files['overall_klicks'];

		$change_user			= '';
		$change_time			= '';

		$add_user		= get_username_string('full', $dl_files['add_user'], $dl_files['add_username'], $dl_files['add_user_colour']);
		$add_time		= $this->user->format_date($dl_files['add_time']);

		if ($dl_files['add_time'] != $dl_files['change_time'])
		{
			$change_user	= get_username_string('full', $dl_files['change_user'], $dl_files['change_username'], $dl_files['change_user_colour']);
			$change_time	= $this->user->format_date($dl_files['change_time']);
		}

		$last_time_string		= ($dl_files['extern']) ? $this->language->lang('DL_LAST_TIME_EXTERN') : $this->language->lang('DL_LAST_TIME');
		$last_time				= ($dl_files['last_time']) ? sprintf($last_time_string, $this->user->format_date($dl_files['last_time'])) : $this->language->lang('DL_NO_LAST_TIME');

		$hack_author_email		= $dl_files['hack_author_email'];
		$hack_author			= $dl_files['hack_author'];
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
		$s_hacklist = $this->dlext_constants::DL_FALSE;
		if ($dl_files['hacklist'] && $this->config['dl_use_hacklist'])
		{
			$s_hacklist = $this->dlext_constants::DL_TRUE;
		}

		/*
		* Block for extra informations - The MOD Block ;-)
		*/
		$s_mod_test = $this->dlext_constants::DL_FALSE;
		$s_mod_desc = $this->dlext_constants::DL_FALSE;
		$s_mod_warning = $this->dlext_constants::DL_FALSE;
		$s_mod_require = $this->dlext_constants::DL_FALSE;
		$s_mod_list_on = $this->dlext_constants::DL_FALSE;

		if ($dl_files['mod_list'])
		{
			if (!empty($index[$cat_id]['allow_mod_desc']) && $index[$cat_id]['allow_mod_desc'])
			{
				$s_mod_list_on = $this->dlext_constants::DL_TRUE;

				if ($mod_test)
				{
					$s_mod_test = $this->dlext_constants::DL_TRUE;
				}

				if ($mod_desc)
				{
					$s_mod_desc			= $this->dlext_constants::DL_TRUE;
					$mod_desc_uid		= $dl_files['mod_desc_uid'];
					$mod_desc_bitfield	= $dl_files['mod_desc_bitfield'];
					$mod_desc_flags		= (isset($dl_files['mod_desc_flags'])) ? $dl_files['mod_desc_flags'] : 0;
					$mod_desc			= generate_text_for_display($mod_desc, $mod_desc_uid, $mod_desc_bitfield, $mod_desc_flags);
				}

				if ($mod_warning)
				{
					$s_mod_warning		= $this->dlext_constants::DL_TRUE;
					$mod_warn_uid		= $dl_files['warn_uid'];
					$mod_warn_bitfield	= $dl_files['warn_bitfield'];
					$mod_warn_flags		= (isset($dl_files['warn_flags'])) ? $dl_files['warn_flags'] : 0;
					$mod_warning		= generate_text_for_display($mod_warning, $mod_warn_uid, $mod_warn_bitfield, $mod_warn_flags);
				}

				if ($mod_require)
				{
					$s_mod_require = $this->dlext_constants::DL_TRUE;
				}
			}
		}

		/*
		* ToDO's? ToDo's!
		*/
		$mod_todo = $dl_files['todo'];

		if ($mod_todo)
		{
			$s_mod_todo			= $this->dlext_constants::DL_TRUE;
			$mod_todo_uid		= $dl_files['todo_uid'];
			$mod_todo_bitfield	= $dl_files['todo_bitfield'];
			$mod_todo_flags		= (isset($dl_files['todo_flags'])) ? $dl_files['todo_flags'] : 0;
			$mod_todo			= generate_text_for_display($mod_todo, $mod_todo_uid, $mod_todo_bitfield, $mod_todo_flags);
		}
		else
		{
			$s_mod_todo = $this->dlext_constants::DL_FALSE;
		}

		/*
		* Check for recurring downloads
		*/
		$s_trafficfree_dl = $this->dlext_constants::DL_FALSE;

		if ($this->config['dl_user_traffic_once'] && !$file_load && !$dl_files['free'] && !$dl_files['extern'] && ($dl_files['file_size'] > $this->user->data['user_traffic']) && !$this->config['dl_traffic_off'] && $this->dlext_constants->get_value('users_traffics'))
		{
			$sql = 'SELECT * FROM ' . $this->dlext_table_dl_notraf . '
				WHERE user_id = ' . (int) $this->user->data['user_id'] . '
					AND dl_id = ' . (int) $df_id;
			$result = $this->db->sql_query($sql);
			$still_count = $this->db->sql_affectedrows();
			$this->db->sql_freeresult($result);

			if ($still_count)
			{
				$file_load = $this->dlext_constants::DL_TRUE;
				$s_trafficfree_dl = $this->dlext_constants::DL_TRUE;
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
					$hotlink_id = $this->dlext_format->dl_hash($this->user->data['user_id'] . time() . $df_id . $this->user->data['session_id']);

					$sql = 'INSERT INTO ' . $this->dlext_table_dl_hotlink . ' ' . $this->db->sql_build_array('INSERT', [
						'user_id'		=> $this->user->data['user_id'],
						'session_id'	=> $this->user->data['session_id'],
						'hotlink_id'	=> $hotlink_id
					]);
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

				$sql = 'SELECT v.ver_id, v.ver_change_time, v.ver_version, u.username FROM ' . $this->dlext_table_dl_versions . ' v
					LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = v.ver_change_user
					WHERE v.dl_id = ' . (int) $df_id . $sql_ver_where . '
					ORDER BY v.ver_version DESC, v.ver_change_time DESC';
				$result = $this->db->sql_query($sql);
				$total_releases = $this->db->sql_affectedrows();

				$version_array = [];

				if ($total_releases)
				{
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
				}

				$this->db->sql_freeresult($result);

				$this->template->assign_vars([
					'S_DL_BUTTON'	=> $this->dlext_constants::DL_TRUE,
					'S_DL_HIDDEN'	=> build_hidden_fields($s_hidden_fields),
					'S_DL_WINDOW'	=> ($dl_files['extern'] && $this->config['dl_ext_new_window']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
					'S_DL_VERSION'	=> $total_releases,
					'U_DL_DOWNLOAD'	=> $this->helper->route('oxpus_dlext_details'),
				]);

				foreach ($version_array as $key => $value)
				{
					$this->template->assign_block_vars('dl_version_select', [
						'DL_VALUE'	=> $value,
						'DL_KEY'	=> $key,
					]);
				}

				add_form_key('dl_load', '_DOWNLOAD');

				if ($captcha_active)
				{
					$code_match = $this->dlext_constants::DL_FALSE;

					$this->template->assign_var('S_DL_VC', $this->dlext_constants::DL_TRUE);

					$captcha = $this->captcha->get_instance($this->config['captcha_plugin']);
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
							$code_match = $this->dlext_constants::DL_TRUE;
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
							'DL_CAPTCHA_TEMPLATE'	=> $captcha->get_template(),
							'S_DL_HIDDEN_FIELDS'	=> build_hidden_fields($captcha->get_hidden_fields()),
						]);
					}
				}
				else
				{
					$code_match = $this->dlext_constants::DL_TRUE;
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
						$sql = 'INSERT INTO ' . $this->dlext_table_dl_hotlink . ' ' . $this->db->sql_build_array('INSERT', [
							'user_id'		=> $this->user->data['user_id'],
							'session_id'	=> $this->user->data['session_id'],
							'hotlink_id'	=> $code,
							'code'			=> 'dlvc'
						]);
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
					extract($this->dispatcher->trigger_event('oxpus.dlext.details_download_before', compact($vars)));

					$dl_add_fav = $this->request->variable('dl_add_fav', 0);

					redirect($this->helper->route('oxpus_dlext_load', ['dl_add_fav' => $dl_add_fav, 'hotlink_id' => $hotlink_id, 'code' => $code, 'df_id' => $df_id, 'modcp' => $modcp, 'cat_id' => $cat_id, 'file_version' => $file_version]));
				}
			}
		}

		/*
		* Display the link ro report the download as broken
		*/
		$s_report_broken = $this->dlext_constants::DL_FALSE;
		if ($this->config['dl_report_broken'] && !$dl_files['broken'] && !$this->user->data['is_bot'])
		{
			if ($this->user->data['is_registered'] || (!$this->user->data['is_registered'] && $this->config['dl_report_broken'] == $this->dlext_constants::DL_REPORT_ALL))
			{
				$s_report_broken = $this->dlext_constants::DL_TRUE;
			}
		}

		/*
		* Second part of the report link
		*/
		$s_dl_broken_mod = $this->dlext_constants::DL_FALSE;
		$s_dl_broken_cur = $this->dlext_constants::DL_FALSE;
		if ($dl_files['broken'] && !$this->user->data['is_bot'])
		{
			if ($index[$cat_id]['auth_mod'] || $cat_auth['auth_mod'] || $this->dlext_auth->user_admin())
			{
				$s_dl_broken_mod = $this->dlext_constants::DL_TRUE;
			}

			if (!$this->config['dl_report_broken_message'] || ($this->config['dl_report_broken_lock'] && $this->config['dl_report_broken_message']))
			{
				$s_dl_broken_cur = $this->dlext_constants::DL_TRUE;
			}
		}

		/*
		* Enabled Bug Tracker for this download category?
		*/
		$s_bug_tracker = $this->dlext_constants::DL_FALSE;
		if (!empty($index[$cat_id]['bug_tracker']) && $index[$cat_id]['bug_tracker'] && !$this->user->data['is_bot'] && $this->user->data['is_registered'])
		{
			$s_bug_tracker = $this->dlext_constants::DL_TRUE;
		}

		/*
		* Thumbnails? Okay, getting some thumbs, if they exists...
		*/
		if (!empty($index[$cat_id]['allow_thumbs']) && $index[$cat_id]['allow_thumbs'] && $this->config['dl_thumb_fsize'])
		{
			if (!$total_images)
			{
				$this->template->assign_var('S_DL_POPUPIMAGE', $this->dlext_constants::DL_TRUE);
			}

			if (!empty($thumbs_ary))
			{
				$drop_images = [];

				foreach ($thumbs_ary as $key => $value)
				{
					if ($thumbs_ary[$key]['img_name'])
					{
						$this->template->assign_block_vars('dl_thumbnail', [
							'DL_THUMBNAIL_LINK'	=> $this->helper->route('oxpus_dlext_thumbnail', ['pic' => $thumbs_ary[$key]['img_id'], 'img_type' => $thumbs_ary[$key]['img_type'], 'disp_art' => $this->dlext_constants::DL_FALSE]),
							'DL_THUMBNAIL_PIC'	=> $this->helper->route('oxpus_dlext_thumbnail', ['pic' => $thumbs_ary[$key]['img_id'], 'img_type' => $thumbs_ary[$key]['img_type'], 'disp_art' => $this->dlext_constants::DL_TRUE]),
							'DL_THUMBNAIL_NAME'	=> $thumbs_ary[$key]['img_title'],
						]);
					}
					else
					{
						$drop_images[] = $thumbs_ary[$key]['img_id'];
					}
				}

				if (!empty($drop_images))
				{
					$sql = 'DELETE FROM ' . 	$this->dlext_table_dl_images . '
						WHERE dl_id = ' . (int) $df_id . '
							AND ' . $this->db->sql_in_set('img_id', array_map('intval', $drop_images));
					$this->db->sql_query($sql);
				}
			}
		}

		/*
		* Urgh, the real filetime..... Heavy information, very important :-D
		*/
		$s_real_filetime = $this->dlext_constants::DL_FALSE;
		if ($this->config['dl_show_real_filetime'] && !$dl_files['extern'])
		{
			if (!empty($index[$cat_id]['cat_path']) && $this->filesystem->exists($this->dlext_constants->get_value('files_dir') . '/downloads/' . $index[$cat_id]['cat_path'] . $real_file))
			{
				$file_time = @filemtime($this->dlext_constants->get_value('files_dir') . '/downloads/' . $index[$cat_id]['cat_path'] . $real_file);
				$s_real_filetime = $this->dlext_constants::DL_TRUE;
			}
		}
		else
		{
			$file_time = 0;
		}

		/*
		* Like to rate? Do it!
		*/
		$rating_points = $dl_files['rating'];
		$s_rating_perm = $this->dlext_constants::DL_FALSE;

		if ($this->config['dl_enable_rate'])
		{
			if ((!$rating_points || $rating_access) && $this->user->data['is_registered'])
			{
				$s_rating_perm = $this->dlext_constants::DL_TRUE;
			}
		}

		/*
		* Some user like to link to each favorite page, download, programm, friend, house friend... ahrrrrrrggggg...
		*/
		$sql = 'SELECT fav_id FROM ' . $this->dlext_table_dl_favorites . '
				WHERE fav_dl_id = ' . (int) $df_id . '
					AND fav_user_id = ' . (int) $this->user->data['user_id'];
		$result = $this->db->sql_query($sql);
		$fav_id = $this->db->sql_fetchfield('fav_id');
		$this->db->sql_freeresult($result);

		if ($this->user->data['is_registered'] && !$this->config['dl_disable_email'])
		{
			$this->template->assign_var('S_DL_FAV_BLOCK', $this->dlext_constants::DL_TRUE);

			if ($fav_id)
			{
				$l_favorite = $this->language->lang('DL_FAVORITE_DROP');
				$u_favorite = $this->helper->route('oxpus_dlext_unfav', ['df_id' => $df_id, 'cat_id' => $cat_id, 'fav_id' => $fav_id]);
				$c_favorite = $this->dlext_constants::DL_TRUE;
				$this->template->assign_var('S_DL_FAV_ACTIVE', $this->dlext_constants::DL_TRUE);
			}
			else
			{
				$l_favorite = $this->language->lang('DL_FAVORITE_ADD');
				$u_favorite = $this->helper->route('oxpus_dlext_fav', ['df_id' => $df_id, 'cat_id' => $cat_id, 'fav_id' => $fav_id]);
				$c_favorite = $this->dlext_constants::DL_FALSE;
			}
		}
		else
		{
			$l_favorite = '';
			$u_favorite = '';
			$c_favorite = $this->dlext_constants::DL_FALSE;
		}

		if ($this->user->data['user_dl_auto_fav'] == 2 && !$fav_id)
		{
			$this->template->assign_var('S_DL_AUTOADD_FAV', $this->dlext_constants::DL_TRUE);
		}

		$file_id	= $dl_files['id'];
		$cat_id		= $dl_files['cat'];

		/*
		* Can we edit the download? Yes we can, or not?
		*/
		$s_edit_button = $this->dlext_constants::DL_FALSE;
		$s_edit_thumbs = $this->dlext_constants::DL_FALSE;
		if (!$this->user->data['is_bot'] && $this->dlext_auth->user_auth($dl_files['cat'], 'auth_mod') || ($this->config['dl_edit_own_downloads'] && $dl_files['add_user'] == $this->user->data['user_id']))
		{
			$s_edit_button = $this->dlext_constants::DL_TRUE;

			if (!empty($index[$cat_id]['allow_thumbs']) && $index[$cat_id]['allow_thumbs'] && $this->config['dl_thumb_fsize'])
			{
				$s_edit_thumbs = $this->dlext_constants::DL_TRUE;
			}
		}

		/*
		* Build rating imageset
		*/
		$rating_img_data	= $this->dlext_format->rating_img($rating_points, $s_rating_perm, $df_id, $ratings);

		/*
		* Send the values to the template to be able to read something *g*
		*/
		$this->template->assign_vars([
			'DL_MOD_LIST'				=> $this->language->lang('DL_MOD_LIST'),
			'DL_MOD_LIST_CLOSE'			=> $this->language->lang('DL_MOD_LIST_CLOSE'),
			'DL_MOD_FILE_HASH_OPEN'		=> $this->language->lang('DL_MOD_FILE_HASH_OPEN'),
			'DL_MOD_FILE_HASH_CLOSE'	=> $this->language->lang('DL_MOD_FILE_HASH_CLOSE'),
			'DL_FAVORITE'				=> $l_favorite,
			'DL_FAVORITE_COLOR'			=> $c_favorite,
			'DL_EDIT_IMG'				=> $this->language->lang('DL_EDIT_FILE'),
			'DL_CAT_RULE'				=> (isset($cat_rule)) ? $cat_rule : '',
			'DL_CAT_TRAFFIC'			=> (isset($cat_traffic)) ? $this->language->lang('DL_CAT_TRAFFIC_MAIN', $cat_traffic) : '',
			'DL_VER_TAB'				=> ($ver_tab) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'DL_DESCRIPTION'			=> $description,
			'DL_MINI_IMG'				=> $mini_icon,
			'DL_HACK_VERSION'			=> $hack_version,
			'DL_LONG_DESC'				=> $long_desc,
			'DL_FILE_STATUS'			=> $file_status,
			'DL_FILE_SIZE'				=> $file_size_out,
			'DL_FILE_KLICKS'			=> $file_klicks,
			'DL_FILE_OVERALL_KLICKS'	=> $file_overall_klicks,
			'DL_FILE_NAME'				=> ($dl_files['extern']) ? $this->language->lang('DL_EXTERN') : $file_name,
			'DL_LAST_TIME'				=> $last_time,
			'DL_ADD_USER'				=> ($add_user != '') ? $this->language->lang('DL_ADD_USER', $add_time, $add_user) : '',
			'DL_CHANGE_USER'			=> ($change_user != '') ? $this->language->lang('DL_CHANGE_USER', $change_time, $change_user) : '',
			'DL_REAL_FILETIME'			=> ($s_real_filetime) ? $this->user->format_date($file_time) : '',
			'DL_REAL_FILETIME_RFC'		=> ($s_real_filetime) ? gmdate(DATE_RFC3339, $file_time) : '',
			'DL_PHPEX'					=> $this->php_ext,
			'DL_BROKEN'					=> $broken,
			'DL_MOD_TODO'				=> $mod_todo,
			'DL_MOD_TEST'				=> ($s_mod_test) ? $mod_test : $this->dlext_constants::DL_FALSE,
			'DL_MOD_DESC'				=> ($s_mod_desc) ? $mod_desc : $this->dlext_constants::DL_FALSE,
			'DL_MOD_WARNING'			=> ($s_mod_warning) ? $mod_warning : $this->dlext_constants::DL_FALSE,
			'DL_MOD_REQUIRE'			=> ($s_mod_require) ? $mod_require : $this->dlext_constants::DL_FALSE,
			'DL_HACK_AUTHOR'			=> $hack_author,
			'DL_HACK_AUTHOR_MAIL'		=> $hack_author_email,
			'DL_HACK_AUTHOR_WEBSITE'	=> $hack_author_website,
			'DL_HACK_DL_URL'			=> $hack_dl_url,

			'S_DL_ACTION'			=> $this->helper->route('oxpus_dlext_details'),
			'S_DL_ENABLE_RATE'		=> (isset($this->config['dl_enable_rate']) && $this->config['dl_enable_rate']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_SHOW_TOPIC_LINK'	=> ($dl_files['dl_topic']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_DETAIL_JS'		=> $this->dlext_constants::DL_TRUE,
			'S_DL_POPUPIMAGE'		=> $s_dl_popupimage,
			'S_DL_CAT_RULE'			=> $s_cat_rule,
			'S_DL_CAT_TRAFFIC'		=> $s_cat_traffic,
			'S_DL_COMMENTS_TAB'		=> $s_comments_tab,
			'S_DL_TRAFFICFREE_DL'	=> $s_trafficfree_dl,
			'S_DL_REPORT_BROKEN'	=> $s_report_broken,
			'S_DL_BROKEN_MOD'		=> $s_dl_broken_mod,
			'S_DL_BROKEN_CUR'		=> $s_dl_broken_cur,
			'S_DL_BUG_TRACKER'		=> $s_bug_tracker,
			'S_DL_REAL_FILETIME'	=> $s_real_filetime,
			'S_DL_EDIT_BUTTON'		=> $s_edit_button,
			'S_DL_EDIT_THUMBS'		=> $s_edit_thumbs,
			'S_DL_MOD_TODO'			=> $s_mod_todo,
			'S_DL_HACKLIST'			=> $s_hacklist,
			'S_DL_OPEN_PANEL'		=> ($view == 'comment' && $s_comments_tab) ? $this->dlext_constants::DL_DEFAULT_PANEL : $this->dlext_constants::DL_FALSE,
			'S_DL_POST_COMMENT'		=> ($s_comments_tab && !$deny_post) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,

			'U_DL_REPORT'			=> $this->helper->route('oxpus_dlext_unbroken', ['df_id' => $df_id, 'cat_id' => $cat_id]),
			'U_DL_BROKEN_DOWNLOAD' 	=> $this->helper->route('oxpus_dlext_broken', ['df_id' => $df_id, 'cat_id' => $cat_id]),
			'U_DL_FILE_TRACKER'		=> $this->helper->route('oxpus_dlext_tracker_view', ['df_id' => $df_id]),
			'U_DL_TOPIC'			=> append_sid($this->root_path . 'viewtopic.' . $this->php_ext, 't=' . $dl_files['dl_topic']),
			'U_DL_EDIT'				=> $this->helper->route('oxpus_dlext_mcp_edit', ['df_id' => $df_id, 'cat_id' => $cat_id]),
			'U_DL_EDIT_THUMBS'		=> $this->helper->route('oxpus_dlext_thumbs', ['df_id' => $df_id, 'cat_id' => $cat_id]),
			'U_DL_FAVORITE'			=> $u_favorite,
			'U_DL_SEARCH'			=> $this->helper->route('oxpus_dlext_search', ['view' => 'search']),
			'U_DL_AJAX'				=> $this->helper->route('oxpus_dlext_rate'),
		]);

		if ($rating_img_data != $this->dlext_constants::DL_FALSE)
		{
			$this->template->assign_block_vars('downloads', [
				'DL_DF_ID'					=> $df_id,
				'DL_RATE_COUNT'				=> $rating_img_data['count']['count'],
				'DL_RATE_UNDO'				=> $rating_img_data['count']['undo'],
				'DL_RATE_TITLE'				=> $rating_img_data['count']['title'],
			]);

			foreach ($rating_img_data['stars'] as $key => $data)
			{
				$this->template->assign_block_vars('downloads.rating_img', [
					'DL_RATE_STAR' 	=> $rating_img_data['stars'][$key]['icon'],
					'DL_RATE_AJAX'	=> $rating_img_data['stars'][$key]['ajax'],
				]);
			}
		}

		$dl_fields = $this->dlext_fields->generate_profile_fields_template('grab', $file_id);
		$dl_fields = (isset($dl_fields[$file_id])) ? $this->dlext_fields->generate_profile_fields_template('show', $this->dlext_constants::DL_FALSE, $dl_fields[$file_id]) : [];
		$s_dl_fields = $this->dlext_constants::DL_FALSE;

		if (!empty($dl_fields['row']))
		{
			$s_dl_fields = $this->dlext_constants::DL_TRUE;
			$this->template->assign_vars($dl_fields['row']);

			if (!empty($dl_fields['blockrow']))
			{
				foreach ($dl_fields['blockrow'] as $field_data)
				{
					$this->template->assign_block_vars('dl_custom_fields', $field_data);
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
		extract($this->dispatcher->trigger_event('oxpus.dlext.details_display_after', compact($vars)));

		$detail_cat_names = [
			0 => $this->language->lang('DL_DETAIL'),
			1 => ($ver_tab) ? $this->language->lang('DL_VERSIONS') : '',
			2 => ($s_comments_tab) ? $this->language->lang('DL_COMMENTS') : '',
			3 => ($s_mod_list_on || $s_mod_todo || $s_dl_fields) ? $this->language->lang('DL_MOD_LIST_SHORT') : '',
			4 => ($hash_tab) ? $this->language->lang('DL_MOD_FILE_HASH_TABLE') : '',
		];

		/**
		 * Additional detail pages by Add Ons
		 *
		 * @event oxpus.dlext.details_append_options
		 * @var int		df_id				download ID
		 * @var array	dl_files			array of download's data
		 * @var array	detail_cat_names	array of option pages
		 * @since 8.2.11
		 */
		$vars = array(
			'df_id',
			'dl_files',
			'detail_cat_names',
		);
		extract($this->dispatcher->trigger_event('oxpus.dlext.details_append_options', compact($vars)));

		for ($i = 0; $i < count($detail_cat_names); ++$i)
		{
			if ($detail_cat_names[$i])
			{
				$this->template->assign_block_vars('dl_category', [
					'DL_CAT_NAME'		=> $detail_cat_names[$i],
					'DL_CAT_ID'			=> $i,
					'DL_COMMENTS_COUNT'	=> $real_comment_exists,
				]);
			}
		}

		$this->template->assign_vars([
			'S_DL_DETAIL_EXTRA_TAB'	=> ($s_mod_list_on  || $s_mod_todo || $s_dl_fields) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_DETAIL_HASH_TAB'	=> ($hash_tab) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
		]);

		/**
		 * Find similar downloads
		 */
		if ($this->config['dl_similar_dl'])
		{
			$search_matches = [];
			$search_ids = [];

			$search_words = array_unique(explode(' ', $description));

			foreach ($search_words as $key => $value)
			{
				if (strlen($value) > 3)
				{
					$search_matches[] = $search_words[$key];
				}
			}

			$sql = 'SELECT id, description FROM ' . $this->dlext_table_downloads . '
				WHERE id <> ' . (int) $df_id . '
				ORDER BY description';
			$result = $this->db->sql_query($sql);

			if ($this->db->sql_affectedrows())
			{
				while ($row = $this->db->sql_fetchrow($result))
				{
					for ($i = 0; $i < count($search_matches); ++$i)
					{
						if (preg_match_all('/' . preg_quote($search_matches[$i], '/') . '/iu', $row['description'], $matches))
						{
							$search_ids[] = $row['id'];
						}
					}

					$search_ids = array_unique($search_ids);

					if (count($search_ids) == $this->config['dl_similar_limit'])
					{
						break;
					}
				}
			}

			$this->db->sql_freeresult($result);

			if (count($search_ids))
			{
				$sql = 'SELECT id, description, desc_uid, desc_bitfield,desc_flags FROM ' . $this->dlext_table_downloads . '
					WHERE ' . $this->db->sql_in_set('id', $search_ids) . '
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

					$this->template->assign_block_vars('dl_similar_dl', [
						'DL_DOWNLOAD'	=> $similar_desc,
						'U_DL_DOWNLOAD'	=> $this->helper->route('oxpus_dlext_details', ['df_id' => $similar_id]),
					]);
				}

				$this->db->sql_freeresult($result);
			}
		}

		$this->notification->delete_notifications([
			'oxpus.dlext.notification.type.dlext',
			'oxpus.dlext.notification.type.update',
		], $df_id, $this->dlext_constants::DL_FALSE, $this->user->data['user_id']);

		/*
		* include the mod footer
		*/
		$this->dlext_footer->set_parameter($nav_view, $cat_id, $df_id, $index);
		$this->dlext_footer->handle();

		/*
		* generate page
		*/
		return $this->helper->render('@oxpus_dlext/view_dl_body.html', strip_tags($dl_files['description']));
	}
}
