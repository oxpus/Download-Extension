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

class thumbs
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

		$this->ext_path					= $this->phpbb_extension_manager->get_extension_path('oxpus/dlext', true);
		$this->ext_path_web				= $this->phpbb_path_helper->update_web_root_path($this->ext_path);
		$this->ext_path_ajax			= $this->ext_path_web . 'assets/javascript/';

		$this->dlext_auth				= $dlext_auth;
		$this->dlext_files				= $dlext_files;
		$this->dlext_format				= $dlext_format;
		$this->dlext_main				= $dlext_main;
		$this->dlext_status				= $dlext_status;
	}

	public function handle()
	{
		$nav_view = 'thumbs';

		// Include the default base init script
		include_once($this->ext_path . 'phpbb/includes/base_init.' . $this->php_ext);

		if ($cancel)
		{
			redirect($this->helper->route('oxpus_dlext_details', ['df_id' => $df_id]));
		}

		if (isset($index[$cat_id]['allow_thumbs']) && $index[$cat_id]['allow_thumbs'] && $this->config['dl_thumb_fsize'])
		{
			$cat_auth = [];
			$cat_auth = $this->dlext_auth->dl_cat_auth($cat_id);

			/*
			* default entry point for download details
			*/
			$dl_files = [];
			$dl_files = $this->dlext_files->all_files(0, '', 'ASC', '', $df_id, 0, '*');

			/*
			* check the permissions
			*/
			$check_status = [];
			$check_status = $this->dlext_status->status($df_id);

			if (!$dl_files['id'])
			{
				trigger_error('DL_NO_PERMISSION');
			}

			/*
			* prepare the download for displaying
			*/
			$long_desc			= $dl_files['long_desc'];
			$long_desc_uid		= $dl_files['long_desc_uid'];
			$long_desc_bitfield	= $dl_files['long_desc_bitfield'];
			$long_desc_flags	= (isset($dl_files['long_desc_flags'])) ? $dl_files['long_desc_flags'] : 0;
			$long_desc			= generate_text_for_display($long_desc, $long_desc_uid, $long_desc_bitfield, $long_desc_flags);

			$status				= $check_status['status_detail'];
			$file_name			= $check_status['file_detail'];
			$file_load			= $check_status['auth_dl'];

			$real_file			= $dl_files['real_file'];

			$description		= $dl_files['description'];
			$desc_uid			= $dl_files['desc_uid'];
			$desc_bitfield		= $dl_files['desc_bitfield'];
			$desc_flags			= $dl_files['desc_flags'];
			$description		= generate_text_for_display($description, $desc_uid, $desc_bitfield, $desc_flags);

			$mini_icon			= $this->dlext_status->mini_status_file($cat_id, $df_id);

			$hack_version		= '&nbsp;'.$dl_files['hack_version'];

			// Check saved thumbs
			$sql = 'SELECT * FROM ' . DL_IMAGES_TABLE . '
				WHERE dl_id = ' . (int) $df_id;
			$result = $this->db->sql_query($sql);
			$total_images = $this->db->sql_affectedrows($result);

			if ($total_images)
			{
				$this->template->assign_var('S_DL_POPUPIMAGE', true);

				$thumbs_ary = [];

				while ($row = $this->db->sql_fetchrow($result))
				{
					$thumbs_ary[] = $row;
				}
			}

			$this->db->sql_freeresult($result);

			$inc_module = true;
			page_header($this->language->lang('DOWNLOADS') . ' - ' . $dl_files['description'] . ' - ' . $this->language->lang('DL_EDIT_THUMBS'));

			$img_id			= $this->request->variable('img_id', 0);
			$edit_img_link	= $this->request->variable('edit_img_link', '', true);
			$img_title		= $this->request->variable('img_title', '', true);
			$action			= $this->request->variable('action', '', true);

			$edit_img_title	= '';

			if ($action == 'delete' && $img_id && $df_id)
			{
				$sql = 'SELECT img_name FROM ' . DL_IMAGES_TABLE . '
					WHERE img_id = ' . (int) $img_id . '
						AND dl_id = ' . (int) $df_id;
				$result = $this->db->sql_query($sql);
				$img_link = $this->db->sql_fetchfield('img_name');
				$this->db->sql_freeresult($result);

				@unlink(DL_EXT_FILEBASE_PATH . 'thumbs/' . $img_link);

				$sql = 'DELETE FROM ' . DL_IMAGES_TABLE . '
					WHERE img_id = ' . (int) $img_id . '
						AND dl_id = ' . (int) $df_id;
				$this->db->sql_query($sql);

				$action = '';
			}

			if ($action == 'edit' && $img_id && $df_id)
			{
				$sql = 'SELECT img_name, img_title FROM ' . DL_IMAGES_TABLE . '
					WHERE img_id = ' . (int) $img_id . '
						AND dl_id = ' . (int) $df_id;
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$edit_img_link = $row['img_name'];
				$edit_img_title = $row['img_title'];
				$this->db->sql_freeresult($result);

				$action = '';
			}

			if ($submit && !$action)
			{
				if (!check_form_key('dl_thumbs'))
				{
					trigger_error('FORM_INVALID');
				}

				$this->language->add_lang('posting');

				$factory = $this->phpbb_container->get('files.factory');

				if ($this->config['dl_thumb_fsize'] && $index[$cat_id]['allow_thumbs'])
				{
					$min_pic_width = 10;
					$allowed_imagetypes = ['gif','png','jpg'];

					$upload = $factory->get('upload')
						->set_allowed_extensions($allowed_imagetypes)
						->set_max_filesize($this->config['dl_thumb_fsize'])
						->set_allowed_dimensions(
							$min_pic_width,
							$min_pic_width,
							$this->config['dl_thumb_xsize'],
							$this->config['dl_thumb_ysize'])
						->set_disallowed_content((isset($this->config['mime_triggers']) ? explode('|', $this->config['mime_triggers']) : false));

					$form_name = 'img_link';

					$upload_file = $this->request->file($form_name);
					unset($upload_file['local_mode']);
					$thumb_file = $upload->handle_upload('files.types.form', $form_name);

					$thumb_size = $upload_file['size'];
					$thumb_temp = $upload_file['tmp_name'];
					$thumb_name = $upload_file['name'];

					$error_count = count($thumb_file->error);

					if ($error_count > 1 && $thumb_name)
					{
						$thumb_file->remove();
						trigger_error(implode('<br />', $thumb_file->error), E_USER_ERROR);
					}

					$thumb_file->error = [];

					if ($thumb_name)
					{
						$pic_size = @getimagesize($thumb_temp);
						$pic_width = $pic_size[0];
						$pic_height = $pic_size[1];

						if (!$pic_width || !$pic_height)
						{
							$thumb_file->remove();
							trigger_error($this->language->lang('DL_UPLOAD_ERROR'), E_USER_ERROR);
						}

						if ($pic_width > $this->config['dl_thumb_xsize'] || $pic_height > $this->config['dl_thumb_ysize'] || (sprintf("%u", @filesize($thumb_temp) > $this->config['dl_thumb_fsize'])))
						{
							$thumb_file->remove();
							trigger_error($this->language->lang('DL_THUMB_TO_BIG'), E_USER_ERROR);
						}
					}
				}

				if (isset($thumb_name) && $thumb_name != '')
				{
					$cur_time = time();
					$thumb_tmp_link = $cur_time . '_' . $thumb_name;

					while (!file_exists(DL_EXT_FILEBASE_PATH . 'thumbs/' . $thumb_tmp_link))
					{
						$upload_file['name'] = $thumb_tmp_link;
						$dest_folder = str_replace($this->root_path, '', substr(DL_EXT_FILEBASE_PATH . 'thumbs/', 0, -1));

						$thumb_file->set_upload_ary($upload_file);
						$thumb_file->move_file($dest_folder, false, false, CHMOD_ALL);

						$cur_time = time();
						$thumb_tmp_link = $cur_time . '_' . $thumb_name;
					}

					$img_link = $thumb_tmp_link;

					if ($img_id)
					{
						$sql = 'SELECT img_name FROM ' . DL_IMAGES_TABLE . ' WHERE img_id = ' . (int) $img_id;
						$result = $this->db->sql_query($sql);
						$old_img_link = $this->db->sql_fetchfield('img_name');
						$this->db->sql_freeresult($result);

						if ($old_img_link != '')
						{
							@unlink(DL_EXT_FILEBASE_PATH . 'thumbs/' . $old_img_link);
						}
					}
				}
				else
				{
					$img_link = $edit_img_link;
				}

				$thumb_message = '<br />' . $this->language->lang('DL_THUMB_UPLOAD');

				if ($img_id)
				{
					$sql_array = [
						'img_name'	=> $img_link,
						'img_title'	=> $img_title,
					];

					$sql = 'UPDATE ' . DL_IMAGES_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_array) . ' WHERE img_id = ' . (int) $img_id . ' AND dl_id = ' . (int) $df_id;
					$this->db->sql_query($sql);

					$success_message = true;
				}
				else if (isset($thumb_name) && $thumb_name != '')
				{
					$sql_array = [
						'img_id'	=> $img_id,
						'dl_id'		=> $df_id,
						'img_name'	=> $img_link,
						'img_title'	=> $img_title,
					];

					$sql = 'INSERT INTO ' . DL_IMAGES_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_array);
					$this->db->sql_query($sql);

					$success_message = true;
				}
				else
				{
					$success_message = false;
				}

				if ($success_message)
				{
					meta_refresh(3, $this->helper->route('oxpus_dlext_thumbs', ['df_id' => $df_id, 'cat_id' => $cat_id]));

					$message = $thumb_message . '<br /><br />' . $this->language->lang('CLICK_RETURN_THUMBS', '<a href="' . $this->helper->route('oxpus_dlext_thumbs', ['df_id' => $df_id, 'cat_id' => $cat_id]) . '">', '</a>');

					trigger_error($message);
				}
			}

			$this->template->set_filenames(['body' => 'dl_thumbs_body.html']);

			add_form_key('dl_thumbs');

			$s_hidden_fields = [
				'img_id'		=> $img_id,
				'edit_img_link'	=> $edit_img_link,
				'df_id'			=> $df_id,
			];

			$thumb_max_size = $this->language->lang('DL_THUMB_DIM_SIZE', $this->config['dl_thumb_xsize'], $this->config['dl_thumb_ysize'], $this->dlext_format->dl_size($this->config['dl_thumb_fsize']));

			$sql = 'SELECT * FROM ' . DL_IMAGES_TABLE . '
				WHERE dl_id = ' . (int) $df_id;
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$pic_path = base64_encode(DL_EXT_FILEBASE_PATH . 'thumbs/' . str_replace(" ", "%20", $row['img_name']));
				$this->template->assign_block_vars('thumbnails', [
					'IMG_LINK'	=> $this->helper->route('oxpus_dlext_thumbnail', ['thumbnail' => $pic_path, 'disp_art' => false]),
					'IMG_PIC'	=> $this->helper->route('oxpus_dlext_thumbnail', ['thumbnail' => $pic_path, 'disp_art' => true]),
					'IMG_TITLE'	=> $row['img_title'],

					'U_DELETE'	=> $this->helper->route('oxpus_dlext_thumbs', ['action' => 'delete', 'cat_id' => $cat_id, 'df_id' => $df_id, 'img_id' => $row['img_id']]),
					'U_EDIT'	=> $this->helper->route('oxpus_dlext_thumbs', ['action' => 'edit', 'cat_id' => $cat_id, 'df_id' => $df_id, 'img_id' => $row['img_id']]),
				]);
			}
			$this->db->sql_freeresult($result);

			$this->template->assign_vars([
				'DESCRIPTION'		=> $description,
				'MINI_IMG'			=> $mini_icon,
				'HACK_VERSION'		=> $hack_version,
				'STATUS'			=> $status,
				'DL_THUMB_MAX_SIZE'	=> $thumb_max_size,

				'EDIT_IMG_TITLE'	=> $edit_img_title,

				'ENCTYPE'			=> 'enctype="multipart/form-data"',

				'S_FORM_ACTION'		=> $this->helper->route('oxpus_dlext_thumbs'),
				'S_HIDDEN_FIELDS'	=> build_hidden_fields($s_hidden_fields),
			]);

			/*
			* include the mod footer
			*/
			$dl_footer = $this->phpbb_container->get('oxpus.dlext.footer');
			$dl_footer->set_parameter($nav_view, $cat_id, $df_id, $index);
			$dl_footer->handle();
		}

		trigger_error('DL_NO_PERMISSION');
	}
}
