<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\controller;

class version
{
	/* phpbb objects */
	protected $root_path;
	protected $php_ext;
	protected $db;
	protected $config;
	protected $helper;
	protected $request;
	protected $template;
	protected $user;
	protected $language;
	protected $files_factory;
	protected $filesystem;

	/* extension owned objects */
	protected $dlext_auth;
	protected $dlext_files;
	protected $dlext_format;
	protected $dlext_main;
	protected $dlext_physical;
	protected $dlext_status;
	protected $dlext_constants;
	protected $dlext_footer;

	protected $dlext_table_dl_ver_files;
	protected $dlext_table_dl_versions;

	/**
	 * Constructor
	 *
	 * @param string								$root_path
	 * @param string								$php_ext
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\config\config					$config
	 * @param \phpbb\controller\helper				$helper
	 * @param \phpbb\request\request 				$request
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\user							$user
	 * @param \phpbb\language\language				$language
	 * @param \phpbb\files\factory					$files_factory
	 * @param \phpbb\filesystem\filesystem			$filesystem
	 * @param \oxpus\dlext\core\auth				$dlext_auth
	 * @param \oxpus\dlext\core\files				$dlext_files
	 * @param \oxpus\dlext\core\format				$dlext_format
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\physical			$dlext_physical
	 * @param \oxpus\dlext\core\status				$dlext_status
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param \oxpus\dlext\core\helpers\footer		$dlext_footer
	 * @param string								$dlext_table_dl_ver_files
	 * @param string								$dlext_table_dl_versions
	 */
	public function __construct(
		$root_path,
		$php_ext,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\language\language $language,
		\phpbb\files\factory $files_factory,
		\phpbb\filesystem\filesystem $filesystem,
		\oxpus\dlext\core\auth $dlext_auth,
		\oxpus\dlext\core\files $dlext_files,
		\oxpus\dlext\core\format $dlext_format,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\physical $dlext_physical,
		\oxpus\dlext\core\status $dlext_status,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		\oxpus\dlext\core\helpers\footer $dlext_footer,
		$dlext_table_dl_ver_files,
		$dlext_table_dl_versions
	)
	{
		$this->root_path				= $root_path;
		$this->php_ext 					= $php_ext;
		$this->db 						= $db;
		$this->config 					= $config;
		$this->helper 					= $helper;
		$this->request					= $request;
		$this->template 				= $template;
		$this->user 					= $user;
		$this->language					= $language;
		$this->files_factory			= $files_factory;
		$this->filesystem				= $filesystem;

		$this->dlext_table_dl_ver_files	= $dlext_table_dl_ver_files;
		$this->dlext_table_dl_versions	= $dlext_table_dl_versions;

		$this->dlext_auth				= $dlext_auth;
		$this->dlext_files				= $dlext_files;
		$this->dlext_format				= $dlext_format;
		$this->dlext_main				= $dlext_main;
		$this->dlext_physical			= $dlext_physical;
		$this->dlext_status				= $dlext_status;
		$this->dlext_constants			= $dlext_constants;
		$this->dlext_footer				= $dlext_footer;
	}

	public function handle()
	{
		$this->dlext_main->dl_handle_active();

		$submit		= $this->request->variable('submit', '');
		$cancel		= $this->request->variable('cancel', '');
		$action		= $this->request->variable('action', '');
		$modcp		= $this->request->variable('modcp', 0);

		$index		= $this->dlext_main->full_index();

		if ($cancel)
		{
			$action = '';
		}

		$ver_id			= $this->request->variable('ver_id', 0);
		$ver_file_id	= $this->request->variable('ver_file_id', 0);

		$sql = 'SELECT * FROM ' . $this->dlext_table_dl_versions . '
			WHERE ver_id = ' . (int) $ver_id;
		$result = $this->db->sql_query($sql);
		$ver_exists = $this->db->sql_affectedrows();
		$ver_data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$ver_exists)
		{
			redirect($this->helper->route('oxpus_dlext_index'));
		}

		$df_id = $ver_data['dl_id'];

		/*
		* default entry point for download details
		*/
		$dl_file = $this->dlext_files->all_files(0, [], [], $df_id, $modcp, ['*']);

		if (!$dl_file)
		{
			redirect($this->helper->route('oxpus_dlext_index'));
		}

		$cat_id = $dl_file['cat'];

		$cat_auth = $this->dlext_auth->dl_cat_auth($cat_id);

		$auth_view = $this->dlext_auth->user_auth($cat_id, 'auth_view');
		$auth_dl = $this->dlext_auth->user_auth($cat_id, 'auth_dl');

		/*
		* check the permissions
		*/
		if ($cat_auth['auth_mod'] || $this->dlext_auth->user_admin())
		{
			$user_is_mod = $this->dlext_constants::DL_TRUE;
		}
		else
		{
			$user_is_mod = $this->dlext_constants::DL_FALSE;
		}

		$user_is_admin = $this->dlext_constants::DL_FALSE;
		$user_is_founder = $this->dlext_constants::DL_FALSE;

		if ($this->dlext_auth->user_admin())
		{
			$user_is_admin = $this->dlext_constants::DL_TRUE;
		}

		if ($this->user->data['user_type'] == USER_FOUNDER)
		{
			$user_is_founder = $this->dlext_constants::DL_TRUE;
		}

		if (!$dl_file['id'] || !$auth_view)
		{
			trigger_error('DL_NO_PERMISSION');
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

			$this->template->assign_var('S_DL_CAT_RULE', $this->dlext_constants::DL_TRUE);
		}
		else
		{
			$cat_rule = '';
		}

		$ver_can_edit = $this->dlext_constants::DL_FALSE;

		if (($user_is_mod || $user_is_admin || $user_is_founder) || ($this->config['dl_edit_own_downloads'] && $dl_file['add_user'] == $this->user->data['user_id']))
		{
			$ver_can_edit = $this->dlext_constants::DL_TRUE;
		}

		/*
		* prepare the download version for displaying
		*/
		$description	= generate_text_for_display($dl_file['description'], $dl_file['desc_uid'], $dl_file['desc_bitfield'], $dl_file['desc_flags']) . '&nbsp;' . $dl_file['hack_version'];
		$mini_icon		= $this->dlext_status->mini_status_file($cat_id, $df_id);
		$ver_version	= '&nbsp;' . $ver_data['ver_version'];
		$ver_desc		= generate_text_for_display($ver_data['ver_text'], $ver_data['ver_uid'], $ver_data['ver_bitfield'], $ver_data['ver_flags']);
		$check_status	= $this->dlext_status->status($df_id);
		$file_status	= $check_status['file_status'];
		$file_load		= $check_status['file_auth'];
		$file_name		= $ver_data['ver_file_name'];

		if ($ver_data['ver_file_size'])
		{
			$file_size_out = $this->dlext_format->dl_size($ver_data['ver_file_size'], 2);
		}
		else
		{
			$file_size_out = $this->language->lang('DL_NOT_AVAILABLE');
		}

		/*
		* Download an attached file?
		*/
		if ($action == 'dl' && $ver_id && $ver_file_id && $auth_dl)
		{
			$sql = 'SELECT ver_id, real_name, file_name, file_type FROM ' . $this->dlext_table_dl_ver_files . '
				WHERE ver_file_id = ' . (int) $ver_file_id;
			$result = $this->db->sql_query($sql);
			$row_ver = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($row_ver['file_type'] == 0 && $row_ver['ver_id'] == $ver_id)
			{
				$this->language->add_lang('viewtopic');

				$dl_file_url = $this->dlext_constants->get_value('files_dir') . '/version/files/';

				$dl_ver_data = [
					'physical_file'		=> $dl_file_url . $row_ver['real_name'],
					'real_filename'		=> $row_ver['file_name'],
					'mimetype'			=> 'application/octetstream',
					'filesize'			=> sprintf('%u', filesize($dl_file_url . $row_ver['real_name'])),
					'filetime'			=> filemtime($dl_file_url . $row_ver['real_name']),
				];

				if ($this->filesystem->exists($dl_file_url . $row_ver['real_name']))
				{
					$this->dlext_physical->send_file_to_browser($dl_ver_data);
				}
				else
				{
					trigger_error($this->language->lang('FILE_NOT_FOUND_404', $row_ver['file_name']));
				}
			}

			trigger_error('DL_NO_ACCESS');
		}
		else if ($action == 'load')
		{
			$action = $submit = '';
		}

		/*
		* Save the version - update existing one or insert new given
		*/
		if ($submit && $action == 'save' && $ver_can_edit && $ver_id)
		{
			$this->language->add_lang('posting');

			// Drop attachments
			$del_files = $this->request->variable('ver_title_del', [0 => 0]);

			$dropped_files = [0];

			foreach ($del_files as $value)
			{
				$sql = 'SELECT file_type, real_name FROM ' . $this->dlext_table_dl_ver_files . '
					WHERE ver_file_id = ' . (int) $value;
				$result = $this->db->sql_query($sql);

				while ($row = $this->db->sql_fetchrow($result))
				{
					if ($row['real_name'])
					{
						switch ($row['file_type'])
						{
							case $this->dlext_constants::DL_FILE_TYPE_IMAGE:
								$this->filesystem->remove($this->dlext_constants->get_value('files_dir') . '/version/images/' . $row['real_name']);
								break;
							default:
								$this->filesystem->remove($this->dlext_constants->get_value('files_dir') . '/version/files/' . $row['real_name']);
						}
					}
				}

				$this->db->sql_freeresult($result);

				$dropped_files[] = $value;
			}

			$sql = 'DELETE FROM ' . $this->dlext_table_dl_ver_files . '
				WHERE ' . $this->db->sql_in_set('ver_file_id', $dropped_files);
			$this->db->sql_query($sql);

			// Update file titles
			$ver_title = $this->request->variable('ver_title', [0 => '']);

			foreach ($ver_title as $key => $value)
			{
				if (!in_array($key, $dropped_files))
				{
					$sql = 'UPDATE ' . $this->dlext_table_dl_ver_files . ' SET ' . $this->db->sql_build_array('UPDATE', [
						'file_title'	=> $value,
					]) . ' WHERE ver_file_id = ' . (int) $key;
					$this->db->sql_query($sql);
				}
			}

			// Upload new file
			$form_name = 'ver_new_file';
			$file = $this->request->file($form_name);
			$extension = str_replace('.', '', trim(strrchr(strtolower($file['name']), '.')));
			$allowed_extensions = [$extension];

			$upload = $this->files_factory->get('upload')
				->set_allowed_extensions($allowed_extensions)
				->set_disallowed_content((isset($this->config['mime_triggers']) ? explode('|', $this->config['mime_triggers']) : $this->dlext_constants::DL_FALSE));

			unset($file['local_mode']);
			$ver_file = $upload->handle_upload('files.types.form', $form_name);

			$ver_file_name = $file['name'];

			$error_count = count($ver_file->error);

			if ($error_count > 1 && $ver_file_name)
			{
				$ver_file->remove();
				trigger_error(implode('<br>', $ver_file->error), E_USER_WARNING);
			}

			if ($ver_file_name)
			{
				do
				{
					$file_name = $df_id . '_' . $ver_id . '_' . ($this->dlext_format->dl_hash($ver_file_name) . '.' . $extension);
				} while ($this->filesystem->exists($this->dlext_constants->get_value('files_dir') . '/version/files/' . $file_name));

				$file['name'] = $file_name;
				$dest_folder = str_replace($this->root_path, '', substr($this->dlext_constants->get_value('files_dir') . '/version/files/', 0, -1));

				$ver_file->set_upload_ary($file);
				$ver_file->move_file($dest_folder, $this->dlext_constants::DL_FALSE, $this->dlext_constants::DL_FALSE);

				$ver_file_title = $this->request->variable('ver_new_file_title', '', $this->dlext_constants::DL_TRUE);

				$sql = 'INSERT INTO ' . $this->dlext_table_dl_ver_files . ' ' . $this->db->sql_build_array('INSERT', [
					'dl_id'			=> $df_id,
					'ver_id'		=> $ver_id,
					'real_name'		=> $file_name,
					'file_name'		=> $ver_file_name,
					'file_title'	=> $ver_file_title,
					'file_type'		=> 0,
				]);
				$this->db->sql_query($sql);
			}
			else
			{
				$ver_file->remove();
			}

			// Upload new image
			$form_name = 'ver_new_image';
			$file = $this->request->file($form_name);
			$extension = str_replace('.', '', trim(strrchr(strtolower($file['name']), '.')));
			$allowed_extensions = [$extension];

			$upload_image = $this->files_factory->get('upload')
				->set_allowed_extensions($allowed_extensions)
				->set_disallowed_content((isset($this->config['mime_triggers']) ? explode('|', $this->config['mime_triggers']) : $this->dlext_constants::DL_FALSE));

			unset($file['local_mode']);
			$ver_image = $upload_image->handle_upload('files.types.form', $form_name);

			$ver_image_temp = $file['tmp_name'];
			$ver_image_name = $file['name'];

			$error_count = count($ver_image->error);

			if ($error_count > 1 && $ver_image_name)
			{
				$ver_image->remove();
				trigger_error(implode('<br>', $ver_image->error), E_USER_WARNING);
			}

			if ($ver_image_name)
			{
				$pic_size = getimagesize($ver_image_temp);
				$pic_width = $pic_size[0];
				$pic_height = $pic_size[1];

				if (!$pic_width || !$pic_height)
				{
					$ver_image->remove();
					trigger_error($this->language->lang('DL_UPLOAD_ERROR'), E_USER_WARNING);
				}

				if ($pic_width > $this->config['dl_thumb_xsize'] || $pic_height > $this->config['dl_thumb_ysize'] || (sprintf('%u', filesize($ver_image_temp) > $this->config['dl_thumb_fsize'])))
				{
					$ver_image->remove();
					trigger_error($this->language->lang('DL_THUMB_TO_BIG'), E_USER_WARNING);
				}

				do
				{
					$img_name = $df_id . '_' . $ver_id . '_' . ($this->dlext_format->dl_hash($ver_image_name)) . '.' . $extension;
				} while ($this->filesystem->exists($this->dlext_constants->get_value('files_dir') . '/version/images/' . $img_name));

				$file['name'] = $img_name;
				$dest_folder = str_replace($this->root_path, '', substr($this->dlext_constants->get_value('files_dir') . '/version/images/', 0, -1));

				$ver_image->set_upload_ary($file);
				$ver_image->move_file($dest_folder, $this->dlext_constants::DL_FALSE, $this->dlext_constants::DL_FALSE);

				$ver_file_title = $this->request->variable('ver_new_image_title', '', $this->dlext_constants::DL_TRUE);

				$sql = 'INSERT INTO ' . $this->dlext_table_dl_ver_files . ' ' . $this->db->sql_build_array('INSERT', [
					'dl_id'			=> $df_id,
					'ver_id'		=> $ver_id,
					'real_name'		=> $img_name,
					'file_name'		=> $ver_image_name,
					'file_title'	=> $ver_file_title,
					'file_type'		=> 1,
				]);
				$this->db->sql_query($sql);
			}
			else
			{
				$ver_image->remove();
			}

			// Update release itself
			$ver_version	= $this->request->variable('ver_version', '', $this->dlext_constants::DL_TRUE);
			$ver_text		= $this->request->variable('message', '', $this->dlext_constants::DL_TRUE);
			$ver_active		= $this->request->variable('ver_active', 0);

			$allow_bbcode		= ($this->config['allow_bbcode']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
			$allow_urls			= $this->dlext_constants::DL_TRUE;
			$allow_smilies		= ($this->config['allow_smilies']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
			$ver_uid			= '';
			$ver_bitfield		= '';
			$ver_flags			= 0;

			generate_text_for_storage($ver_text, $ver_uid, $ver_bitfield, $ver_flags, $allow_bbcode, $allow_urls, $allow_smilies);

			$sql = 'UPDATE ' . $this->dlext_table_dl_versions . ' SET ' . $this->db->sql_build_array('UPDATE', [
				'ver_version'		=> $ver_version,
				'ver_text'			=> $ver_text,
				'ver_uid'			=> $ver_uid,
				'ver_bitfield'		=> $ver_bitfield,
				'ver_flags'			=> $ver_flags,
				'ver_active'		=> $ver_active,
				'ver_change_time'	=> time(),
				'ver_change_user'	=> $this->user->data['user_id'],
			]) . ' WHERE dl_id = ' . (int) $df_id . ' AND ver_id = ' . (int) $ver_id;

			$this->db->sql_query($sql);

			$s_redirect_params = [
				'action'	=> 'save',
				'ver_id'	=> $ver_id,
				'df_id'		=> $df_id,
			];

			$s_redirect = $this->helper->route('oxpus_dlext_version', $s_redirect_params);
			redirect($s_redirect);
		}

		/*
		* Edit existing version or add new one
		*/

		if ($action == 'edit' && $ver_can_edit)
		{
			$text_ary				= generate_text_for_edit($ver_data['ver_text'], $ver_data['ver_uid'], $ver_data['ver_flags']);
			$ver_data['ver_text']	= $text_ary['text'];

			// Fetch all attachments for this release
			$sql = 'SELECT ver_file_id, file_type, real_name, file_title, file_name FROM ' . $this->dlext_table_dl_ver_files . '
				WHERE ver_id = ' . (int) $ver_id . '
				ORDER BY file_title';
			$result = $this->db->sql_query($sql);

			$images_exists = $this->dlext_constants::DL_FALSE;

			while ($row = $this->db->sql_fetchrow($result))
			{
				switch ($row['file_type'])
				{
					case $this->dlext_constants::DL_FILE_TYPE_IMAGE:
						$file_path		= $this->helper->route('oxpus_dlext_thumbnail', ['pic' => $row['ver_file_id'], 'img_type' => 'version', 'disp_art' => $this->dlext_constants::DL_TRUE]);
						$image			= $this->helper->route('oxpus_dlext_thumbnail', ['pic' => $row['ver_file_id'], 'img_type' => 'version', 'disp_art' => $this->dlext_constants::DL_FALSE]);
						$tpl_block		= 'images';
						$images_exists	= $this->dlext_constants::DL_TRUE;
						break;
					default:
						$file_path		= $row['real_name'];
						$tpl_block		= 'files';
						$image			= '';
				}

				$this->template->assign_block_vars($tpl_block, [
					'DL_LINK'			=> $file_path,
					'DL_FILE_NAME'		=> $row['file_name'],
					'DL_NAME'			=> $row['file_title'],
					'DL_IMAGE'			=> $image,
					'DL_VER_FILE_ID'	=> $row['ver_file_id'],
				]);
			}

			$this->db->sql_freeresult($result);

			$s_form_ary = [
				'action'	=> 'save',
				'ver_id'	=> $ver_id,
				'df_id'		=> $df_id,
			];

			$s_form_action = $this->helper->route('oxpus_dlext_version', $s_form_ary);

			// Status for HTML, BBCode, Smilies, Images and Flash,
			$bbcode_status	= ($this->config['allow_bbcode']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
			$smilies_status	= ($bbcode_status && $this->config['allow_smilies']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
			$img_status		= $this->dlext_constants::DL_TRUE;
			$url_status		= ($this->config['allow_post_links']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
			$flash_status	= $this->dlext_constants::DL_FALSE;
			$quote_status	= $this->dlext_constants::DL_TRUE;

			$this->language->add_lang('posting');

			// Smilies Block,
			if ($smilies_status)
			{
				if (!function_exists('generate_smilies'))
				{
					include($this->root_path . 'includes/functions_posting.' . $this->php_ext);
				}

				generate_smilies('inline', 0);
			}

			// Display functions
			if (!function_exists('display_custom_bbcodes'))
			{
				include($this->root_path . 'includes/functions_display.' . $this->php_ext);
			}

			display_custom_bbcodes();

			$this->template->assign_vars([
				'DL_CAT_RULE'			=> $cat_rule,
				'DL_DESCRIPTION'		=> $description,
				'DL_MINI_IMG'			=> $mini_icon,
				'DL_FILE_STATUS'		=> $file_status,
				'DL_VER_ACTIVE'			=> ($ver_data['ver_active']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
				'DL_VER_TEXT'			=> $ver_data['ver_text'],
				'DL_VER_VERSION'		=> $ver_data['ver_version'],

				'S_BBCODE_ALLOWED'		=> $bbcode_status,
				'S_BBCODE_IMG'			=> $img_status,
				'S_BBCODE_URL'			=> $url_status,
				'S_BBCODE_FLASH'		=> $flash_status,
				'S_BBCODE_QUOTE'		=> $quote_status,
				'S_LINKS_ALLOWED'		=> $url_status,

				'S_DL_CAT_RULE'			=> ($cat_rule) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
				'S_DL_POPUPIMAGE'		=> $images_exists,
				'S_DL_FORM_ACTION'		=> $s_form_action,
			]);

			/*
			* include the mod footer
			*/
			$this->dlext_footer->set_parameter('version', $cat_id, $df_id, $index);
			$this->dlext_footer->handle();

			/*
			* generate page
			*/
			return $this->helper->render('@oxpus_dlext/dl_version_edit.html', strip_tags($dl_file['description']));
		}

		/*
		* Fetch all attachments for this release
		*/
		$sql = 'SELECT * FROM ' . $this->dlext_table_dl_ver_files . '
			WHERE ver_id = ' . (int) $ver_id . '
			ORDER BY file_title';
		$result = $this->db->sql_query($sql);

		$images_exists = $this->dlext_constants::DL_FALSE;

		while ($row = $this->db->sql_fetchrow($result))
		{
			switch ($row['file_type'])
			{
				case $this->dlext_constants::DL_FILE_TYPE_IMAGE:
					$tpl_block		= 'images';
					$images_exists	= $this->dlext_constants::DL_TRUE;
					$file_path		= $this->helper->route('oxpus_dlext_thumbnail', ['pic' => $row['ver_file_id'], 'img_type' => 'version', 'disp_art' => $this->dlext_constants::DL_TRUE]);
					$image			= $this->helper->route('oxpus_dlext_thumbnail', ['pic' => $row['ver_file_id'], 'img_type' => 'version', 'disp_art' => $this->dlext_constants::DL_FALSE]);
					break;
				default:
					$load_link_ary = [
						'action'		=> 'dl',
						'ver_id'		=> $ver_id,
						'ver_file_id'	=> $row['ver_file_id'],
						'df_id'			=> $df_id,
					];
					$file_path	= $this->helper->route('oxpus_dlext_version', $load_link_ary);
					$tpl_block	= 'files';
					$image		= '';
			}

			$this->template->assign_block_vars($tpl_block, [
				'DL_NAME'		=> ($row['file_title']) ? $row['file_title'] : $row['file_name'],
				'DL_LINK'		=> $file_path,
				'DL_IMAGE'		=> $image,
				'S_DL_AUTH'		=> $file_load,
			]);
		}

		$this->db->sql_freeresult($result);

		/*
		* Send the release values themselves to the template to be able to read something *g*
		*/
		$this->template->assign_vars([
			'DL_DESCRIPTION'		=> $description,
			'DL_MINI_IMG'			=> $mini_icon,
			'DL_VER_VERSION'		=> $ver_version,
			'DL_VER_DESC'			=> ($ver_desc) ? $ver_desc : $this->language->lang('DL_NOT_AVAILABLE'),
			'DL_FILE_STATUS'		=> $file_status,
			'DL_FILE_SIZE'			=> $file_size_out,
			'DL_FILE_NAME'			=> ($dl_file['extern']) ? $this->language->lang('DL_EXTERN') : $file_name,
			'DL_CAT_RULE'			=> $cat_rule,

			'S_DL_ACTIVE'			=> $ver_data['ver_active'],
			'S_DL_CAT_RULE'			=> ($cat_rule) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_POPUPIMAGE'		=> $images_exists,

			'U_DL_VER_EDIT'			=> ($ver_can_edit) ? $this->helper->route('oxpus_dlext_version', ['action' => 'edit', 'ver_id' => $ver_id, 'df_id' => $df_id]) : '',
		]);

		/*
		* include the mod footer
		*/
		$this->dlext_footer->set_parameter('version', $cat_id, $df_id, $index);
		$this->dlext_footer->handle();

		/*
		* generate page
		*/
		return $this->helper->render('@oxpus_dlext/dl_version.html', strip_tags($dl_file['description']));
	}
}
