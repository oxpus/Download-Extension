<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2021 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\controller;

class thumbs
{
	/* phpbb objects */
	protected $root_path;
	protected $db;
	protected $config;
	protected $helper;
	protected $request;
	protected $template;
	protected $language;
	protected $files_factory;
	protected $filesystem;

	/* extension owned objects */
	protected $dlext_auth;
	protected $dlext_files;
	protected $dlext_format;
	protected $dlext_main;
	protected $dlext_status;
	protected $dlext_constants;
	protected $dlext_footer;

	protected $dlext_table_dl_images;

	/**
	* Constructor
	*
	* @param string									$root_path
	* @param \phpbb\db\driver\driver_interface		$db
	* @param \phpbb\config\config					$config
	* @param \phpbb\controller\helper				$helper
	* @param \phpbb\request\request 				$request
	* @param \phpbb\template\template				$template
	* @param \phpbb\language\language				$language
	* @param \phpbb\files\factory					$files_factory
	* @param \phpbb\filesystem\filesystem			$filesystem
	* @param \oxpus\dlext\core\auth					$dlext_auth
	* @param \oxpus\dlext\core\files				$dlext_files
	* @param \oxpus\dlext\core\format				$dlext_format
	* @param \oxpus\dlext\core\main					$dlext_main
	* @param \oxpus\dlext\core\status				$dlext_status
	* @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	* @param \oxpus\dlext\core\helpers\footer		$dlext_footer
	* @param string									$dlext_table_dl_images
	*/
	public function __construct(
		$root_path,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\language\language $language,
		\phpbb\files\factory $files_factory,
		\phpbb\filesystem\filesystem $filesystem,
		\oxpus\dlext\core\auth $dlext_auth,
		\oxpus\dlext\core\files $dlext_files,
		\oxpus\dlext\core\format $dlext_format,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\status $dlext_status,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		\oxpus\dlext\core\helpers\footer $dlext_footer,
		$dlext_table_dl_images
	)
	{
		$this->root_path				= $root_path;
		$this->db 						= $db;
		$this->config 					= $config;
		$this->helper 					= $helper;
		$this->request					= $request;
		$this->template 				= $template;
		$this->language					= $language;
		$this->files_factory			= $files_factory;
		$this->filesystem				= $filesystem;

		$this->dlext_table_dl_images	= $dlext_table_dl_images;

		$this->dlext_auth				= $dlext_auth;
		$this->dlext_files				= $dlext_files;
		$this->dlext_format				= $dlext_format;
		$this->dlext_main				= $dlext_main;
		$this->dlext_status				= $dlext_status;
		$this->dlext_constants			= $dlext_constants;
		$this->dlext_footer				= $dlext_footer;
	}

	public function handle()
	{
		$this->dlext_main->dl_handle_active();

		$index = $this->dlext_main->full_index();

		$submit			= $this->request->variable('submit', '');
		$cancel			= $this->request->variable('cancel', '');
		$df_id			= $this->request->variable('df_id', 0);
		$cat_id			= $this->request->variable('cat_id', 0);

		if ($cancel)
		{
			redirect($this->helper->route('oxpus_dlext_details', ['df_id' => $df_id]));
		}

		if (isset($index[$cat_id]['allow_thumbs']) && $index[$cat_id]['allow_thumbs'] && $this->config['dl_thumb_fsize'])
		{
			/*
			* default entry point for download details
			*/
			$dl_files = $this->dlext_files->all_files(0, [], [], $df_id, 0, ['*']);

			/*
			* check the permissions
			*/
			$check_status = $this->dlext_status->status($df_id);

			if (!$dl_files['id'])
			{
				trigger_error('DL_NO_PERMISSION');
			}

			/*
			* prepare the download for displaying
			*/
			$file_status		= $check_status['file_status'];

			$description		= $dl_files['description'];
			$desc_uid			= $dl_files['desc_uid'];
			$desc_bitfield		= $dl_files['desc_bitfield'];
			$desc_flags			= $dl_files['desc_flags'];
			$description		= generate_text_for_display($description, $desc_uid, $desc_bitfield, $desc_flags);

			$mini_icon			= $this->dlext_status->mini_status_file($cat_id, $df_id);

			$hack_version		= '&nbsp;'.$dl_files['hack_version'];

			// Check saved thumbs
			$sql = 'SELECT * FROM ' . 	$this->dlext_table_dl_images . '
				WHERE dl_id = ' . (int) $df_id;
			$result = $this->db->sql_query($sql);
			$total_images = $this->db->sql_affectedrows();

			if ($total_images)
			{
				$this->template->assign_var('S_DL_POPUPIMAGE', $this->dlext_constants::DL_TRUE);

				while ($row = $this->db->sql_fetchrow($result))
				{
					$thumbs_ary[] = $row;
				}
			}

			$this->db->sql_freeresult($result);

			$img_id			= $this->request->variable('img_id', 0);
			$edit_img_link	= $this->request->variable('edit_img_link', '', $this->dlext_constants::DL_TRUE);
			$img_title		= $this->request->variable('img_title', '', $this->dlext_constants::DL_TRUE);
			$action			= $this->request->variable('action', '', $this->dlext_constants::DL_TRUE);

			$edit_img_title	= '';

			if ($action == 'delete' && $img_id && $df_id)
			{
				$sql = 'SELECT img_name FROM ' . 	$this->dlext_table_dl_images . '
					WHERE img_id = ' . (int) $img_id . '
						AND dl_id = ' . (int) $df_id;
				$result = $this->db->sql_query($sql);
				$img_link = $this->db->sql_fetchfield('img_name');
				$this->db->sql_freeresult($result);

				if ($img_link)
				{
					$this->filesystem->remove($this->dlext_constants->get_value('files_dir') . '/thumbs/' . $img_link);
				}

				$sql = 'DELETE FROM ' . 	$this->dlext_table_dl_images . '
					WHERE img_id = ' . (int) $img_id . '
						AND dl_id = ' . (int) $df_id;
				$this->db->sql_query($sql);

				$action = '';
			}

			if ($action == 'edit' && $img_id && $df_id)
			{
				$sql = 'SELECT img_name, img_title FROM ' . 	$this->dlext_table_dl_images . '
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

				if ($this->config['dl_thumb_fsize'] && $index[$cat_id]['allow_thumbs'])
				{
					$min_pic_width = $this->dlext_constants::DL_PIC_MIN_SIZE;
					$allowed_imagetypes = ['gif','png','jpg'];

					$upload = $this->files_factory->get('upload')
						->set_allowed_extensions($allowed_imagetypes)
						->set_max_filesize($this->config['dl_thumb_fsize'])
						->set_allowed_dimensions(
							$min_pic_width,
							$min_pic_width,
							$this->config['dl_thumb_xsize'],
							$this->config['dl_thumb_ysize'])
						->set_disallowed_content((isset($this->config['mime_triggers']) ? explode('|', $this->config['mime_triggers']) : $this->dlext_constants::DL_FALSE));

					$form_name = 'img_link';

					$upload_file = $this->request->file($form_name);
					unset($upload_file['local_mode']);
					$thumb_file = $upload->handle_upload('files.types.form', $form_name);

					$thumb_temp = $upload_file['tmp_name'];
					$thumb_name = $upload_file['name'];

					$error_count = count($thumb_file->error);

					if ($error_count > 1 && $thumb_name)
					{
						$thumb_file->remove();
						trigger_error(implode('<br />', $thumb_file->error), E_USER_ERROR);
					}

					if ($thumb_name)
					{
						$pic_size = getimagesize($thumb_temp);
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
					$thumb_pic_extension = trim(strrchr(strtolower($thumb_name), '.'));
					$thumb_tmp_link = $df_id . '_' . unique_id() . $thumb_pic_extension;

					while ($this->filesystem->exists($this->dlext_constants->get_value('files_dir') . '/thumbs/' . $thumb_tmp_link))
					{
						$thumb_tmp_link = $df_id . '_' . unique_id() . $thumb_pic_extension;
					}

					$upload_file['name'] = $thumb_tmp_link;
					$dest_folder = str_replace($this->root_path, '', substr($this->dlext_constants->get_value('files_dir') . '/thumbs/', 0, -1));

					$thumb_file->set_upload_ary($upload_file);
					$thumb_file->move_file($dest_folder, $this->dlext_constants::DL_FALSE, $this->dlext_constants::DL_FALSE);

					$img_link = $thumb_tmp_link;

					if ($img_id)
					{
						$sql = 'SELECT img_name FROM ' . 	$this->dlext_table_dl_images . ' WHERE img_id = ' . (int) $img_id;
						$result = $this->db->sql_query($sql);
						$old_img_link = $this->db->sql_fetchfield('img_name');
						$this->db->sql_freeresult($result);

						if ($old_img_link)
						{
							$this->filesystem->remove($this->dlext_constants->get_value('files_dir') . '/thumbs/' . $old_img_link);
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

					$sql = 'UPDATE ' . 	$this->dlext_table_dl_images . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_array) . ' WHERE img_id = ' . (int) $img_id . ' AND dl_id = ' . (int) $df_id;
					$this->db->sql_query($sql);

					$success_message = $this->dlext_constants::DL_TRUE;
				}
				else if (isset($thumb_name) && $thumb_name)
				{
					$sql_array = [
						'img_id'	=> $img_id,
						'dl_id'		=> $df_id,
						'img_name'	=> $img_link,
						'img_title'	=> $img_title,
					];

					$sql = 'INSERT INTO ' . 	$this->dlext_table_dl_images . ' ' . $this->db->sql_build_array('INSERT', $sql_array);
					$this->db->sql_query($sql);

					$success_message = $this->dlext_constants::DL_TRUE;
				}
				else
				{
					$success_message = $this->dlext_constants::DL_FALSE;
				}

				if ($success_message)
				{
					meta_refresh(3, $this->helper->route('oxpus_dlext_thumbs', ['df_id' => $df_id, 'cat_id' => $cat_id]));

					$message = $thumb_message . '<br /><br />' . $this->language->lang('CLICK_RETURN_THUMBS', '<a href="' . $this->helper->route('oxpus_dlext_thumbs', ['df_id' => $df_id, 'cat_id' => $cat_id]) . '">', '</a>');

					trigger_error($message);
				}
			}

			add_form_key('dl_thumbs');

			$s_hidden_fields = [
				'img_id'		=> $img_id,
				'edit_img_link'	=> $edit_img_link,
				'df_id'			=> $df_id,
			];

			$thumb_max_size = $this->language->lang('DL_THUMB_DIM_SIZE', $this->config['dl_thumb_xsize'], $this->config['dl_thumb_ysize'], $this->dlext_format->dl_size($this->config['dl_thumb_fsize']));

			$sql = 'SELECT * FROM ' . 	$this->dlext_table_dl_images . '
				WHERE dl_id = ' . (int) $df_id;
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$pic_path = base64_encode($this->dlext_constants->get_value('files_dir') . '/thumbs/' . $row['img_name']);

				$this->template->assign_block_vars('dl_thumbnails', [
					'DL_IMG_LINK'	=> $this->helper->route('oxpus_dlext_thumbnail', ['pic' => $pic_path, 'disp_art' => $this->dlext_constants::DL_FALSE]),
					'DL_IMG_PIC'	=> $this->helper->route('oxpus_dlext_thumbnail', ['pic' => $pic_path, 'disp_art' => $this->dlext_constants::DL_TRUE]),
					'DL_IMG_TITLE'	=> $row['img_title'],

					'U_DL_DELETE'	=> $this->helper->route('oxpus_dlext_thumbs', ['action' => 'delete', 'cat_id' => $cat_id, 'df_id' => $df_id, 'img_id' => $row['img_id']]),
					'U_DL_EDIT'	=> $this->helper->route('oxpus_dlext_thumbs', ['action' => 'edit', 'cat_id' => $cat_id, 'df_id' => $df_id, 'img_id' => $row['img_id']]),
				]);
			}

			$this->db->sql_freeresult($result);

			$this->template->assign_vars([
				'DL_DESCRIPTION'		=> $description,
				'DL_MINI_IMG'			=> $mini_icon,
				'DL_HACK_VERSION'		=> $hack_version,
				'DL_FILE_STATUS'		=> $file_status,
				'DL_THUMB_MAX_SIZE'		=> $thumb_max_size,

				'DL_EDIT_IMG_TITLE'		=> $edit_img_title,

				'S_DL_FORM_ACTION'		=> $this->helper->route('oxpus_dlext_thumbs'),
				'S_DL_HIDDEN_FIELDS'	=> build_hidden_fields($s_hidden_fields),
			]);

			/*
			* include the mod footer
			*/
			$this->dlext_footer->set_parameter('thumbs', $cat_id, $df_id, $index);
			$this->dlext_footer->handle();

			/*
			* generate page
			*/
			return $this->helper->render('@oxpus_dlext/dl_thumbs_body.html', $this->language->lang('DL_EDIT_THUMBS_TITLE', $dl_files['description']));
		}

		trigger_error('DL_NO_PERMISSION');
	}
}
