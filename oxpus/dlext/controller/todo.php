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

class todo
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
	protected $dlext_extra;
	protected $dlext_files;
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
		\phpbb\request\request_interface $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\language\language $language,
		$dlext_auth,
		$dlext_extra,
		$dlext_files,
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
		$this->request					= $request;
		$this->template 				= $template;
		$this->user 					= $user;
		$this->language					= $language;

		$this->ext_path					= $this->phpbb_extension_manager->get_extension_path('oxpus/dlext', true);
		$this->ext_path_web				= $this->phpbb_path_helper->update_web_root_path($this->ext_path);
		$this->ext_path_ajax			= $this->ext_path_web . 'assets/javascript/';

		$this->dlext_auth				= $dlext_auth;
		$this->dlext_extra				= $dlext_extra;
		$this->dlext_files				= $dlext_files;
		$this->dlext_main				= $dlext_main;
	}

	public function handle()
	{
		$nav_view = 'todo';

		// Include the default base init script
		include_once($this->ext_path . 'phpbb/includes/base_init.' . $this->php_ext);

		/*
		* create todo list
		*/
		if (!$this->config['dl_todo_onoff'])
		{
			trigger_error($this->language->lang('DL_NO_PERMISSION'), E_USER_WARNING);
		}

		$todo_access_ids = $this->dlext_main->full_index(0, 0, 0, 2);
		$total_todo_ids = sizeof($todo_access_ids);

		if ($action == 'edit')
		{
			if ($total_todo_ids > 0 && $this->user->data['is_registered'])
			{
				$todo	= $this->request->variable('todo', '', true);

				add_form_key('dl_todo');
				
				// Save or delete a todo
				if ($submit && !$cancel)
				{
					if ($delete)
					{
						if (confirm_box(true))
						{
							$todo = '';
						}
						else
						{
							$s_hidden_fields = array(
								'view'		=> 'todo',
								'action'	=> 'edit',
								'df_id'		=> $df_id,
								'submit'	=> true,
								'delete'	=> true,
							);
				
							confirm_box(false, $this->language->lang('DELETE_POST'), build_hidden_fields($s_hidden_fields));
						}
					}
				
					if ($df_id)
					{
						if (!check_form_key('dl_todo') && $todo)
						{
							trigger_error('FORM_INVALID');
						}
				
						$allow_bbcode		= ($this->config['allow_bbcode']) ? true : false;
						$allow_urls			= true;
						$allow_smilies		= ($this->config['allow_smilies']) ? true : false;
						$todo_uid			= '';
						$todo_bitfield		= '';
						$todo_flags			= 0;
				
						if ($todo)
						{
							generate_text_for_storage($todo, $todo_uid, $todo_bitfield, $todo_flags, $allow_bbcode, $allow_urls, $allow_smilies);
						}
				
						$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', array(
							'todo'			=> $todo,
							'todo_uid'		=> $todo_uid,
							'todo_bitfield'	=> $todo_bitfield,
							'todo_flags'	=> $todo_flags)) . ' WHERE id = ' . (int) $df_id . ' AND ' . $this->db->sql_in_set('cat', $todo_access_ids);
						$this->db->sql_query($sql);
				
						$meta_url	= $this->helper->route('oxpus_dlext_todo', array('action' => 'edit'));
						$message	= $this->language->lang('DOWNLOAD_UPDATED') . '<br /><br />' . $this->language->lang('CLICK_RETURN_TODO_EDIT', '<a href="' . $meta_url . '">', '</a>');
				
						meta_refresh(3, $meta_url);
				
						trigger_error($message);
					}
				}
				
				// Will we edit a todo??
				if ($edit && $df_id)
				{
					$dl_file = array();
					$dl_file = $this->dlext_files->all_files(0, '', 'ASC', '', $df_id, 0, 'description, desc_uid, desc_flags, todo, todo_uid, todo_flags');
				
					$description	= $dl_file['description'];
					$desc_uid		= $dl_file['desc_uid'];
					$desc_flags		= $dl_file['desc_flags'];
					$todo			= $dl_file['todo'];
					$todo_uid		= $dl_file['todo_uid'];
					$todo_flags		= $dl_file['todo_flags'];
				
					$text_ary		= generate_text_for_edit($description, $desc_uid, $desc_flags);
					$s_downloads	= $text_ary['text'];
				
					$text_ary		= generate_text_for_edit($todo, $todo_uid, $todo_flags);
					$todo			= $text_ary['text'];
				
					$s_hidden_fields = array(
						'view'		=> 'todo',
						'action'	=> 'edit',
						'df_id'		=> $df_id,
					);
				
					$total_possible_todo = true;
				}
				else
				{
					$todo = '';
				
					$s_downloads = '<select name="df_id" class="select autowidth">';
				
					$sql = 'SELECT c.cat_name, d.id, d.description, d.desc_uid, d.desc_flags, d.todo_uid, d.todo_flags FROM ' . DOWNLOADS_TABLE . ' d, ' . DL_CAT_TABLE . ' c
						WHERE d.cat = c.id
							AND ' . $this->db->sql_in_set('d.cat', $todo_access_ids) . "
							AND (todo = '' OR todo IS NULL)
						ORDER BY c.parent, c.sort, c.id, d.description";
					 $result = $this->db->sql_query($sql);
				
					$total_possible_todo = $this->db->sql_affectedrows($result);
					$dl_select = array();
				
					while ($row = $this->db->sql_fetchrow($result))
					{
						$dl_select[$row['cat_name']][] = $row;
					}
				
					$this->db->sql_freeresult($result);
				
					$cur_cat = '';
				
					foreach($dl_select as $category => $row)
					{
						if ($cur_cat <> $category)
						{
							$s_downloads .= '<optgroup label="' . $category . '">';
				
							foreach($dl_select[$category] as $row)
							{
								$description	= $row['description'];
								$desc_uid		= $row['desc_uid'];
								$desc_flags		= $row['desc_flags'];
						
								$text_ary		= generate_text_for_edit($description, $desc_uid, $desc_flags);
								$description	= $text_ary['text'];
						
								$s_downloads .= '<option value="' . $row['id'] . '">' . $description . '</option>';
							}
				
							$s_downloads .= '</optgroup>';
							$cur_cat = $category;
						}
					}
				
				
					$s_downloads .= '</select>';
				
					$s_hidden_fields = array(
						'action'	=> 'edit',
					);
				}
				
				// Initiate todo list management page
				$this->template->set_filenames(array(
					'body' => 'dl_todo_edit_body.html')
				);
				
				$this->template->assign_vars(array(
					'TODO_TEXT'			=> $todo,
				
					'S_ADD_TODO'		=> ($edit) ? false : true,
					'S_TODO_ADD'		=> $total_possible_todo,
					'S_DOWNLOAD'		=> $s_downloads,
					'S_HIDDEN_FIELDS'	=> build_hidden_fields($s_hidden_fields),
					'S_FORM_ACTION'		=> $this->helper->route('oxpus_dlext_todo'),
				));
				
				// Build todo edit list for existing entries
				$dl_todo = array();
				$dl_todo = $this->dlext_extra->get_todo();
				
				if (isset($dl_todo['file_name'][0]))
				{
					for ($i = 0; $i < sizeof($dl_todo['file_name']); $i++)
					{
						$df_id = $dl_todo['df_id'][$i];
				
						$this->template->assign_block_vars('todolist_row', array(
							'FILENAME'		=> $dl_todo['file_name'][$i],
							'FILE_LINK'		=> $this->helper->route('oxpus_dlext_details', array('df_id' => $df_id)),
							'HACK_VERSION'	=> $dl_todo['hack_version'][$i],
							'TODO'			=> $dl_todo['todo'][$i],
				
							'U_TODO_EDIT'	=> $this->helper->route('oxpus_dlext_todo', array('action' => 'edit', 'edit' => true, 'df_id' => $df_id)),
							'U_TODO_DELETE'	=> $this->helper->route('oxpus_dlext_todo', array('action' => 'edit', 'delete' => true, 'submit' => true, 'df_id' => $df_id)),
						));
					}
				}
				else
				{
					$this->template->assign_var('S_NO_TODOLIST', true);
				}
				
				page_header($this->language->lang('DL_MOD_TODO'));
			}
			else
			{
				trigger_error($this->language->lang('DL_NO_PERMISSION'), E_USER_WARNING);
			}
		}
		else
		{
			$dl_todo = array();
			$dl_todo = $this->dlext_extra->get_todo();

			page_header($this->language->lang('DL_MOD_TODO'));

			$this->template->set_filenames(array(
				'body' => 'dl_todo_body.html')
			);

			$this->template->assign_vars(array(
				'U_TODO_ADD'	=> $this->helper->route('oxpus_dlext_todo', array('action' => 'edit')),
				'U_DL_TOP'		=> $this->ext_path,
			));

			if ($total_todo_ids > 0 && $this->user->data['is_registered'])
			{
				$this->template->assign_var('S_TODO_EDIT', true);
			}

			if (isset($dl_todo['file_name'][0]) && sizeof($dl_todo['file_name']))
			{
				for ($i = 0; $i < sizeof($dl_todo['file_name']); $i++)
				{
					$this->template->assign_block_vars('todolist_row', array(
						'FILENAME'		=> $dl_todo['file_name'][$i],
						'FILE_LINK'		=> $this->helper->route('oxpus_dlext_details', array('df_id' => $dl_todo['df_id'][$i])),
						'HACK_VERSION'	=> $dl_todo['hack_version'][$i],
						'TODO'			=> $dl_todo['todo'][$i],

						'U_TODO_EDIT'	=> $this->helper->route('oxpus_dlext_todo', array('action' => 'edit', 'edit' => true, 'df_id' => $dl_todo['df_id'][$i])),
						'U_TODO_DELETE'	=> $this->helper->route('oxpus_dlext_todo', array('action' => 'edit', 'delete' => true, 'submit' => true, 'df_id' => $dl_todo['df_id'][$i])),
					));
				}
			}
			else
			{
				$this->template->assign_var('S_NO_TODOLIST', true);
			}
		}

		/*
		* include the mod footer
		*/
		$dl_footer = $this->phpbb_container->get('oxpus.dlext.footer');
		$dl_footer->set_parameter($nav_view, $cat_id, $df_id, $index);
		$dl_footer->handle();
	}
}
