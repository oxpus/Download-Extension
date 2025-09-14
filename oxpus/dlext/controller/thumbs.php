<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\controller;

class thumbs
{
	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames thumbs_ary sql_array

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
	protected $user;

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
	 * @param string								$root_path
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\config\config					$config
	 * @param \phpbb\controller\helper				$helper
	 * @param \phpbb\request\request 				$request
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\language\language				$language
	 * @param \phpbb\files\factory					$files_factory
	 * @param \phpbb\filesystem\filesystem			$filesystem
	 * @param \phpbb\user							$user
	 * @param \oxpus\dlext\core\auth				$dlext_auth
	 * @param \oxpus\dlext\core\files				$dlext_files
	 * @param \oxpus\dlext\core\format				$dlext_format
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\status				$dlext_status
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param \oxpus\dlext\core\helpers\footer		$dlext_footer
	 * @param string								$dlext_table_dl_images
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
		\phpbb\user $user,
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
		$this->user 					= $user;

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
		$action			= $this->request->variable('action', '');
		$df_id			= $this->request->variable('df_id', 0);
		$cat_id			= $this->request->variable('cat_id', 0);
		$img_id			= $this->request->variable('img_id', 0);

		if ($cancel)
		{
			$action = '';
			$img_id = 0;
			$submit = '';
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

			$hack_version		= '&nbsp;' . $dl_files['hack_version'];

			$img_index		= $this->request->variable('img_index', 0);
			$img_lists		= $this->request->variable('img_lists', 0);
			$edit_img_link	= $this->request->variable('edit_img_link', '', $this->dlext_constants::DL_TRUE);
			$img_title		= $this->request->variable('img_title', '', $this->dlext_constants::DL_TRUE);

			$edit_img_title	= '';
			$edit_img_index	= 0;
			$edit_img_lists	= 0;

			// Check saved thumbs
			$sql = 'SELECT * FROM ' . $this->dlext_table_dl_images . '
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

			if ($action == 'delete' && $img_id && $df_id)
			{
				$sql = 'SELECT img_name FROM ' . $this->dlext_table_dl_images . '
					WHERE img_id = ' . (int) $img_id . '
						AND dl_id = ' . (int) $df_id;
				$result = $this->db->sql_query($sql);
				$img_link = $this->db->sql_fetchfield('img_name');
				$this->db->sql_freeresult($result);

				if ($img_link)
				{
					$this->filesystem->remove($this->dlext_constants->get_value('files_dir') . '/thumbs/' . $img_link);
				}

				$sql = 'DELETE FROM ' . $this->dlext_table_dl_images . '
					WHERE img_id = ' . (int) $img_id . '
						AND dl_id = ' . (int) $df_id;
				$this->db->sql_query($sql);

				redirect($this->helper->route('oxpus_dlext_thumbs', ['df_id' => $df_id, 'cat_id' => $cat_id]));
			}

			if ($action == 'edit' && $img_id && $df_id)
			{
				$sql = 'SELECT img_name, img_title, img_index, img_lists FROM ' . $this->dlext_table_dl_images . '
					WHERE img_id = ' . (int) $img_id . '
						AND dl_id = ' . (int) $df_id;
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$edit_img_link = $row['img_name'];
				$edit_img_title = $row['img_title'];
				$edit_img_index = $row['img_index'];
				$edit_img_lists = $row['img_lists'];
				$this->db->sql_freeresult($result);

				if ($submit)
				{
					$action = '';
				}
			}

			if ($action == 'display' && $img_id && $df_id)
			{
				if ($img_index)
				{
					$sql = 'UPDATE ' . $this->dlext_table_dl_images . ' SET ' . $this->db->sql_build_array('UPDATE', ['img_index' => 0]) . ' WHERE dl_id = ' . (int) $df_id;
					$this->db->sql_query($sql);

					$sql = 'UPDATE ' . $this->dlext_table_dl_images . ' SET ' . $this->db->sql_build_array('UPDATE', ['img_index' => 1]) . ' WHERE img_id = ' . (int) $img_id . ' AND dl_id = ' . (int) $df_id;
					$this->db->sql_query($sql);
				}

				if ($img_lists)
				{
					$sql = 'UPDATE ' . $this->dlext_table_dl_images . ' SET ' . $this->db->sql_build_array('UPDATE', ['img_lists' => 0]) . ' WHERE dl_id = ' . (int) $df_id;
					$this->db->sql_query($sql);

					$sql = 'UPDATE ' . $this->dlext_table_dl_images . ' SET ' . $this->db->sql_build_array('UPDATE', ['img_lists' => 1]) . ' WHERE img_id = ' . (int) $img_id . ' AND dl_id = ' . (int) $df_id;
					$this->db->sql_query($sql);
				}

				$action = '';
			}

			if ($action == 'ajax')
			{
				$cleanupTargetDir = true; // Remove old files
				$maxFileAge = 5 * 3600; // Temp file age in seconds

				// Get a file name
				$files_name	= $this->request->file('file');
				$fileName	= uniqid($df_id . '_') . '.jpg';
				$thumb_name	= $this->dlext_constants->get_value('files_dir') . '/thumbs/' . $fileName;

				// Chunking might be enabled
				$chunk		= $this->request->variable('chunk', 0);
				$chunks		= $this->request->variable('chunks', 0);

				// Remove old temp files
				if ($cleanupTargetDir)
				{
					if (!is_dir($this->dlext_constants->get_value('files_dir') . '/thumbs/') || !$dir = opendir($this->dlext_constants->get_value('files_dir') . '/thumbs/'))
					{
						$return_ary = [
							'jsonrpc'	=> '2.0',
							'error'		=> [
								'code'		=> 100,
								'message'	=> 'Failed to open temp directory.',
							],
							'id'		=> 'id',
						];

						return new Response(json_encode($return_ary));
					}

					while (($file = readdir($dir)) !== false)
					{
						$tmpfilePath = $this->dlext_constants->get_value('files_dir') . '/thumbs/' . $file;

						// If temp file is current file proceed to the next
						if ($tmpfilePath == "{$thumb_name}.part")
						{
							continue;
						}

						// Remove temp file if it is older than the max age and is not the current file
						if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge))
						{
							@unlink($tmpfilePath);
						}
					}
					closedir($dir);
				}

				// Open temp file
				if (!$out = @fopen("{$thumb_name}.part", $chunks ? "ab" : "wb"))
				{
					$return_ary = [
						'jsonrpc'	=> '2.0',
						'error'		=> [
							'code'		=> 102,
							'message'	=> 'Failed to open output stream.',
						],
						'id'		=> 'id',
					];

					return new Response(json_encode($return_ary));
				}

				if (!empty($_FILES))
				{
					if ($files_name['error'] || !is_uploaded_file($files_name['tmp_name']))
					{
						$return_ary = [
							'jsonrpc'	=> '2.0',
							'error'		=> [
								'code'		=> 103,
								'message'	=> 'Failed to move uploaded file.',
							],
							'id'		=> 'id',
						];

						return new Response(json_encode($return_ary));
					}

					// Read binary input stream and append it to temp file
					if (!$in = @fopen($files_name['tmp_name'], "rb"))
					{
						$return_ary = [
							'jsonrpc'	=> '2.0',
							'error'		=> [
								'code'		=> 101,
								'message'	=> 'Failed to open input stream.',
							],
							'id'		=> 'id',
						];

						return new Response(json_encode($return_ary));
					}
				}
				else
				{
					if (!$in = @fopen("php://input", "rb"))
					{
						$return_ary = [
							'jsonrpc'	=> '2.0',
							'error'		=> [
								'code'		=> 101,
								'message'	=> 'Failed to open input stream.',
							],
							'id'		=> 'id',
						];

						return new Response(json_encode($return_ary));
					}
				}

				while ($buff = fread($in, 4096))
				{
					fwrite($out, $buff);
				}

				@fclose($out);
				@fclose($in);

				// Check if file has been uploaded
				if (!$chunks || $chunk == $chunks - 1)
				{
					// Strip the temp .part suffix off
					rename("{$thumb_name}.part", $thumb_name);
				}

				$submit = true;
			}

			if ($submit)
			{
				$this->language->add_lang('posting');

				if ($this->config['dl_thumb_fsize'] && $index[$cat_id]['allow_thumbs'] )
				{
					$img_link = (!$fileName) ? $edit_img_link : $fileName;

					if ($img_id && $fileName)
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

				if ($img_index)
				{
					$sql = 'UPDATE ' . $this->dlext_table_dl_images . ' SET ' . $this->db->sql_build_array('UPDATE', ['img_index' => 0]) . ' WHERE dl_id = ' . (int) $df_id;
					$this->db->sql_query($sql);
				}

				if ($img_lists)
				{
					$sql = 'UPDATE ' . $this->dlext_table_dl_images . ' SET ' . $this->db->sql_build_array('UPDATE', ['img_lists' => 0]) . ' WHERE dl_id = ' . (int) $df_id;
					$this->db->sql_query($sql);
				}

				if ($img_id)
				{
					if ($fileName)
					{
						$sql_array['img_name'] = $img_link;
					}
					else
					{
						$sql_array = [
							'img_name'	=> $img_link,
							'img_title'	=> $img_title,
							'img_index'	=> $img_index,
							'img_lists'	=> $img_lists,
						];
					}

					$sql = 'UPDATE ' . 	$this->dlext_table_dl_images . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_array) . ' WHERE img_id = ' . (int) $img_id . ' AND dl_id = ' . (int) $df_id;
					$this->db->sql_query($sql);
				}
				else if (isset($thumb_name) && $thumb_name && $total_images < $index[$cat_id]['max_thumbs'])
				{
					$sql_array = [
						'img_name'	=> $img_link,
						'img_title'	=> $img_title,
						'img_index'	=> $img_index,
						'img_lists'	=> $img_lists,
						'dl_id'		=> $df_id
					];

					$sql = 'INSERT INTO ' . $this->dlext_table_dl_images . ' ' . $this->db->sql_build_array('INSERT', $sql_array);
					$this->db->sql_query($sql);
					$img_id = $this->db->sql_last_inserted_id();
				}

				if ($action == 'ajax')
				{
					$return_ary = [
						'jsonrpc'	=> '2.0',
						'result'	=> null,
						'id'		=> 'id',
					];

					return new Response(json_encode($return_ary));
				}
				else
				{
					redirect($this->helper->route('oxpus_dlext_thumbs', ['df_id' => $df_id, 'cat_id' => $cat_id]));
				}
			}

			$thumb_max_size = $this->language->lang('DL_THUMB_DIM_SIZE', $this->config['dl_thumb_xsize'], $this->config['dl_thumb_ysize'], $this->dlext_format->dl_size($this->config['dl_thumb_fsize']));

			$sql = 'SELECT * FROM ' . 	$this->dlext_table_dl_images . '
				WHERE dl_id = ' . (int) $df_id . '
				ORDER BY img_id';
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->template->assign_block_vars('dl_thumbnails', [
					'DL_IMG_LINK'	=> $this->helper->route('oxpus_dlext_thumbnail', ['pic' => $row['img_id'], 'img_type' => 'more', 'disp_art' => $this->dlext_constants::DL_FALSE]),
					'DL_IMG_PIC'	=> $this->helper->route('oxpus_dlext_thumbnail', ['pic' => $row['img_id'], 'img_type' => 'more', 'disp_art' => $this->dlext_constants::DL_TRUE]),
					'DL_IMG_TITLE'	=> $row['img_title'],
					'DL_IMG_INDEX'	=> $row['img_index'],
					'DL_IMG_LISTS'	=> $row['img_lists'],

					'U_DL_DELETE'	=> $this->helper->route('oxpus_dlext_thumbs', ['action' => 'delete', 'cat_id' => $cat_id, 'df_id' => $df_id, 'img_id' => $row['img_id']]),
					'U_DL_EDIT'		=> $this->helper->route('oxpus_dlext_thumbs', ['action' => 'edit', 'cat_id' => $cat_id, 'df_id' => $df_id, 'img_id' => $row['img_id']]),
					'U_DL_INDEX'	=> $this->helper->route('oxpus_dlext_thumbs', ['action' => 'display', 'cat_id' => $cat_id, 'df_id' => $df_id, 'img_id' => $row['img_id'], 'img_index' => 1]),
					'U_DL_LISTS'	=> $this->helper->route('oxpus_dlext_thumbs', ['action' => 'display', 'cat_id' => $cat_id, 'df_id' => $df_id, 'img_id' => $row['img_id'], 'img_lists' => 1]),
				]);
			}

			$this->db->sql_freeresult($result);

			$this->user->add_lang('posting');

			$max_file_size = $this->config['dl_thumb_fsize'];
			$max_thumbs = $index[$cat_id]['max_thumbs'];

			$hidden_fields = [
				'df_id'			=> $df_id,
				'cat_id'		=> $cat_id,
				'img_id'		=> $img_id,
				'edit_img_link'	=> $edit_img_link,
			];

			$this->template->assign_vars([
				'DL_DESCRIPTION'		=> $description,
				'DL_MINI_IMG'			=> $mini_icon,
				'DL_HACK_VERSION'		=> $hack_version,
				'DL_FILE_STATUS'		=> $file_status,
				'DL_THUMB_MAX_SIZE'		=> $thumb_max_size,

				'DL_THUMB_HINTS'		=> $this->language->lang('DL_THUMB_HINTS', $total_images, $max_thumbs),
				'DL_EDIT_IMG_PIC'		=> ($action == 'edit') ? $this->helper->route('oxpus_dlext_thumbnail', ['pic' => $img_id, 'img_type' => 'more', 'disp_art' => $this->dlext_constants::DL_TRUE]) : '',
				'DL_EDIT_IMG_TITLE'		=> $edit_img_title,
				'DL_EDIT_IMG_INDEX'		=> $edit_img_index,
				'DL_EDIT_IMG_LISTS'		=> $edit_img_lists,

				'S_DL_TOTAL_IMAGES'		=> ($total_images < $max_thumbs || $action == 'edit'),
				'S_DL_FORMS_ACTION'		=> $this->helper->route('oxpus_dlext_thumbs'),
				'S_DL_HIDDEN_FIELDS'	=> build_hidden_fields($hidden_fields),
				'S_DL_ACTION'			=> $action,

				'U_DL_PLUPLOAD_URL'		=> $this->helper->route('oxpus_dlext_thumbs', ['df_id' => $df_id, 'cat_id' => $cat_id], false),
				'U_BACK'				=> ($action == 'edit') ? $this->helper->route('oxpus_dlext_details', ['df_id' => $df_id]) : '',

				'PLUPLOAD_PARAM_1'		=> $df_id,
				'PLUPLOAD_PARAM_2'		=> $cat_id,
				'PLUPLOAD_PARAM_3'		=> $img_id,
				'PLUPLOAD_PARAM_4'		=> $edit_img_link,
				'PLUPLOAD_MAX_SIZE'		=> $max_file_size,
				'PLUPLOAD_USER_LANG'	=> $this->user->data['user_lang'],

				'UPLOADPIC_MAX_FIELDS'	=> $max_thumbs - $total_images,
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

	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames
}
