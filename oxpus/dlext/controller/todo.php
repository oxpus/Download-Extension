<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\controller;

class todo
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

	/* extension owned objects */
	protected $dlext_extra;
	protected $dlext_files;
	protected $dlext_main;
	protected $dlext_footer;
	protected $dlext_constants;

	protected $dlext_table_downloads;
	protected $dlext_table_dl_cat;

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
	 * @param \oxpus\dlext\core\extra				$dlext_extra
	 * @param \oxpus\dlext\core\files				$dlext_files
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\helpers\footer		$dlext_footer
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param string								$dlext_table_downloads
	 * @param string								$dlext_table_dl_cat
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
		\oxpus\dlext\core\extra $dlext_extra,
		\oxpus\dlext\core\files $dlext_files,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\helpers\footer $dlext_footer,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		$dlext_table_downloads,
		$dlext_table_dl_cat
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

		$this->dlext_table_downloads	= $dlext_table_downloads;
		$this->dlext_table_dl_cat		= $dlext_table_dl_cat;

		$this->dlext_extra				= $dlext_extra;
		$this->dlext_files				= $dlext_files;
		$this->dlext_main				= $dlext_main;
		$this->dlext_footer				= $dlext_footer;
		$this->dlext_constants			= $dlext_constants;
	}

	public function handle()
	{
		$this->dlext_main->dl_handle_active();

		$cat			= $this->request->variable('cat', 0);
		$submit			= $this->request->variable('submit', '');
		$preview		= $this->request->variable('preview', '');
		$cancel			= $this->request->variable('cancel', '');
		$delete			= $this->request->variable('delete', '');
		$edit			= $this->request->variable('edit', '');
		$df_id			= $this->request->variable('df_id', 0);
		$cat_id			= $this->request->variable('cat_id', 0);
		$todo			= $this->request->variable('message', '', $this->dlext_constants::DL_TRUE);

		$index 			= ($cat) ? $this->dlext_main->index($cat) : $this->dlext_main->index();

		if ($cancel)
		{
			$df_id = 0;
			$submit = '';
			$preview = '';
		}

		/*
		* create todo list
		*/
		if (!$this->config['dl_todo_onoff'])
		{
			redirect($this->helper->route('oxpus_dlext_index'));
		}

		$todo_access_ids = $this->dlext_main->full_index(0, 0, 0, $this->dlext_constants::DL_AUTH_CHECK_MOD);

		if (count($todo_access_ids) > 0 && $this->user->data['is_registered'])
		{

			add_form_key('dl_todo');

			$allow_bbcode		= ($this->config['allow_bbcode']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
			$allow_urls			= $this->dlext_constants::DL_TRUE;
			$allow_smilies		= ($this->config['allow_smilies']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
			$todo_uid			= '';
			$todo_bitfield		= '';
			$todo_flags			= 0;
			$preview_todo		= '';

			// Preview a todo
			if ($preview && $todo)
			{
				$preview_todo	= $todo;
				generate_text_for_storage($preview_todo, $todo_uid, $todo_bitfield, $todo_flags, $allow_bbcode, $allow_urls, $allow_smilies);
				$text_ary			= generate_text_for_edit($preview_todo, $todo_uid, $todo_flags);
				$preview_todo_tmp	= $text_ary['text'];
				$preview_todo	= generate_text_for_display($preview_todo, $todo_uid, $todo_bitfield, $todo_flags);

				$this->template->assign_vars([
					'DL_PREVIEW_TODO'	=> $preview_todo,

					'S_DL_PREVIEW_TODO'	=> $this->dlext_constants::DL_TRUE,
				]);

				$preview_todo = $preview_todo_tmp;
				$submit = '';
				$delete = '';
			}

			// Save or delete a todo
			if ($submit && !$cancel)
			{
				if ($delete)
				{
					if (confirm_box($this->dlext_constants::DL_TRUE))
					{
						$todo = '';
					}
					else
					{
						$s_hidden_fields = [
							'view'		=> 'todo',
							'df_id'		=> $df_id,
							'submit'	=> $this->dlext_constants::DL_TRUE,
							'delete'	=> $this->dlext_constants::DL_TRUE,
						];

						confirm_box($this->dlext_constants::DL_FALSE, $this->language->lang('DELETE_POST'), build_hidden_fields($s_hidden_fields), '@oxpus_dlext/helpers/dl_confirm_body.html');
					}
				}

				if ($df_id)
				{
					if (!check_form_key('dl_todo') && $todo)
					{
						trigger_error('FORM_INVALID');
					}

					if ($todo)
					{
						generate_text_for_storage($todo, $todo_uid, $todo_bitfield, $todo_flags, $allow_bbcode, $allow_urls, $allow_smilies);
					}

					$sql = 'UPDATE ' . $this->dlext_table_downloads . ' SET ' . $this->db->sql_build_array('UPDATE', [
						'todo'			=> $todo,
						'todo_uid'		=> $todo_uid,
						'todo_bitfield'	=> $todo_bitfield,
						'todo_flags'	=> $todo_flags
					]) . ' WHERE id = ' . (int) $df_id . ' AND ' . $this->db->sql_in_set('cat', $todo_access_ids);
					$this->db->sql_query($sql);

					$meta_url	= $this->helper->route('oxpus_dlext_todo');
					$message	= $this->language->lang('DL_DOWNLOAD_UPDATED') . '<br><br>' . $this->language->lang('CLICK_RETURN_TODO_EDIT', '<a href="' . $meta_url . '">', '</a>');

					meta_refresh(3, $meta_url);

					trigger_error($message);
				}
			}

			// Will we edit a todo??
			if ($edit && $df_id)
			{
				$fields			= ['description', 'desc_uid', 'desc_flags', 'todo', 'todo_uid', 'todo_flags', 'hack_version'];
				$dl_file		= $this->dlext_files->all_files(0, [], [], $df_id, 0, $fields);

				$description	= $dl_file['description'];
				$desc_uid		= $dl_file['desc_uid'];
				$desc_flags		= $dl_file['desc_flags'];
				$hack_version	= $dl_file['hack_version'];
				$todo			= $dl_file['todo'];
				$todo_uid		= $dl_file['todo_uid'];
				$todo_flags		= $dl_file['todo_flags'];

				$text_ary		= generate_text_for_edit($description, $desc_uid, $desc_flags);
				$hack_version	= $text_ary['text'] . ' ' . $hack_version;

				$text_ary		= generate_text_for_edit($todo, $todo_uid, $todo_flags);
				$todo			= $text_ary['text'];

				$s_hidden_fields = [
					'view'		=> 'todo',
					'df_id'		=> $df_id,
					'edit'		=> $edit,
				];

				$total_possible_todo = $this->dlext_constants::DL_TRUE;
			}
			else
			{
				$todo = '';
				$hack_version = '';

				$sql = 'SELECT c.cat_name, d.id, d.description, d.desc_uid, d.desc_flags, d.todo_uid, d.todo_flags FROM ' . $this->dlext_table_downloads . ' d, ' . $this->dlext_table_dl_cat . ' c
					WHERE d.cat = c.id
						AND ' . $this->db->sql_in_set('d.cat', $todo_access_ids) . "
						AND (todo = '' OR todo IS NULL)
					ORDER BY c.parent, c.sort, c.id, d.description";
				$result = $this->db->sql_query($sql);

				$total_possible_todo = $this->db->sql_affectedrows();

				if ($df_id)
				{
					$this->db->sql_freeresult($result);
				}
				else
				{
					$dl_select = [];

					while ($row = $this->db->sql_fetchrow($result))
					{
						$dl_select[$row['cat_name']][] = $row;
					}

					$this->db->sql_freeresult($result);

					$cur_cat = '';

					foreach ($dl_select as $category => $row)
					{
						if ($cur_cat != $category)
						{
							$this->template->assign_block_vars('dl_todo_select', [
								'DL_TYPE'	=> 'optgrp',
								'DL_VALUE'	=> $category,
							]);

							foreach ($dl_select[$category] as $row)
							{
								$description	= $row['description'];
								$desc_uid		= $row['desc_uid'];
								$desc_flags		= $row['desc_flags'];

								$text_ary		= generate_text_for_edit($description, $desc_uid, $desc_flags);
								$description	= $text_ary['text'];

								$this->template->assign_block_vars('dl_todo_select', [
									'DL_TYPE'	=> 'option',
									'DL_KEY'	=> $row['id'],
									'DL_VALUE'	=> $description,
								]);
							}

							$this->template->assign_block_vars('dl_todo_select', [
								'DL_TYPE'	=> 'optend',
							]);

							$cur_cat = $category;
						}
					}
				}

				$s_hidden_fields = [
					'view'		=> 'todo',
					'edit'		=> $this->dlext_constants::DL_FALSE,
				];

				if ($df_id)
				{
					$s_hidden_fields['df_id'] = $df_id;
				}
			}

			// Status for HTML, BBCode, Smilies, Images and Flash,
			$bbcode_status	= ($this->config['allow_bbcode']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
			$smilies_status	= ($bbcode_status && $this->config['allow_smilies']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
			$img_status		= $this->dlext_constants::DL_TRUE;
			$url_status		= ($this->config['allow_post_links']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
			$flash_status	= $this->dlext_constants::DL_FALSE;
			$quote_status	= $this->dlext_constants::DL_TRUE;

			$this->language->add_lang('posting');

			// Smilies Block
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
				'DL_HACK_VERSION'		=> $hack_version,
				'DL_TODO_TEXT'			=> ($preview) ? $preview_todo : $todo,

				'S_BBCODE_ALLOWED'		=> $bbcode_status,
				'S_BBCODE_IMG'			=> $img_status,
				'S_BBCODE_URL'			=> $url_status,
				'S_BBCODE_FLASH'		=> $flash_status,
				'S_BBCODE_QUOTE'		=> $quote_status,
				'S_LINKS_ALLOWED'		=> $url_status,

				'S_DL_ADD_TODO'			=> ($edit || $preview) ? $this->dlext_constants::DL_FALSE : $this->dlext_constants::DL_TRUE,
				'S_DL_TODO_ADD'			=> $total_possible_todo,
				'S_DL_HIDDEN_FIELDS'	=> build_hidden_fields($s_hidden_fields),
				'S_DL_FORM_ACTION'		=> $this->helper->route('oxpus_dlext_todo'),

				'U_DL_MORE_SMILIES'		=> append_sid($this->root_path . 'posting.' . $this->php_ext, 'mode=smilies'),
			]);

			// Build todo edit list for existing entries
			$dl_todo = $this->dlext_extra->get_todo();

			if (!empty($dl_todo['file_name'][0]))
			{
				for ($i = 0; $i < count($dl_todo['file_name']); ++$i)
				{
					$df_id = $dl_todo['df_id'][$i];

					$this->template->assign_block_vars('todolist_row', [
						'DL_FILENAME'		=> $dl_todo['file_name'][$i],
						'DL_FILE_LINK'		=> $this->helper->route('oxpus_dlext_details', ['df_id' => $df_id]),
						'DL_HACK_VERSION'	=> $dl_todo['hack_version'][$i],
						'DL_TODO'			=> $dl_todo['todo'][$i],

						'U_DL_TODO_EDIT'	=> $this->helper->route('oxpus_dlext_todo', ['edit' => $this->dlext_constants::DL_TRUE, 'df_id' => $df_id]),
						'U_DL_TODO_DELETE'	=> $this->helper->route('oxpus_dlext_todo', ['delete' => $this->dlext_constants::DL_TRUE, 'submit' => $this->dlext_constants::DL_TRUE, 'df_id' => $df_id]),
					]);
				}
			}
			else
			{
				$this->template->assign_var('S_DL_NO_TODOLIST', $this->dlext_constants::DL_TRUE);
			}
		}
		else
		{
			trigger_error($this->language->lang('DL_NO_PERMISSION'), E_USER_WARNING);
		}

		/*
		* include the mod footer
		*/
		$this->dlext_footer->set_parameter('todo', $cat_id, $df_id, $index);
		$this->dlext_footer->handle();

		/*
		* generate page
		*/
		return $this->helper->render('@oxpus_dlext/dl_todo_body.html', $this->language->lang('DL_MOD_TODO'));
	}
}
