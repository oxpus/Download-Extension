<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2026 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\controller;

class todo_ext
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
	protected $auth;

	/* extension owned objects */
	protected $dlext_extra;
	protected $dlext_files;
	protected $dlext_main;
	protected $dlext_footer;
	protected $dlext_constants;
	protected $todo_access_ids;

	protected $dlext_table_downloads;
	protected $dlext_table_dl_cat;
	protected $dlext_table_dl_todo_cat;
	protected $dlext_table_dl_todo;

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
	 * @param \phpbb\auth\auth						$auth
	 * @param \oxpus\dlext\core\extra				$dlext_extra
	 * @param \oxpus\dlext\core\files				$dlext_files
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\helpers\footer		$dlext_footer
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param string								$dlext_table_downloads
	 * @param string								$dlext_table_dl_cat
	 * @param string								$dlext_table_dl_todo_cat
	 * @param string								$dlext_table_dl_todo
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
		\phpbb\auth\auth $auth,
		\oxpus\dlext\core\extra $dlext_extra,
		\oxpus\dlext\core\files $dlext_files,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\helpers\footer $dlext_footer,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		$dlext_table_downloads,
		$dlext_table_dl_cat,
		$dlext_table_dl_todo_cat,
		$dlext_table_dl_todo
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
		$this->auth 					= $auth;

		$this->dlext_table_downloads	= $dlext_table_downloads;
		$this->dlext_table_dl_cat		= $dlext_table_dl_cat;
		$this->dlext_table_dl_todo_cat	= $dlext_table_dl_todo_cat;
		$this->dlext_table_dl_todo		= $dlext_table_dl_todo;

		$this->dlext_extra				= $dlext_extra;
		$this->dlext_files				= $dlext_files;
		$this->dlext_main				= $dlext_main;
		$this->dlext_footer				= $dlext_footer;
		$this->dlext_constants			= $dlext_constants;
	}

	public function handle()
	{
		$this->dlext_main->dl_handle_active();

		$action			= $this->request->variable('action', 'entry');
		$submit			= $this->request->variable('submit', '');
		$cancel			= $this->request->variable('cancel', '');
		$delete			= $this->request->variable('delete', 0);
		$edit			= $this->request->variable('edit', 0);
		$save			= $this->request->variable('save', '');
		$sort			= $this->request->variable('sort', 0);
		$df_id			= $this->request->variable('df_id', 0);
		$todo_cat_id	= $this->request->variable('todo_cat_id', 0);
		$todo_catname	= $this->request->variable('todo_catname', '');
		$todo_cat_color	= $this->request->variable('todo_cat_color', '');
		$todo_cat_sort	= $this->request->variable('todo_cat_sort', 0);
		$todo_id		= $this->request->variable('todo_id', 0);
		$todo_sort		= $this->request->variable('todo_sort', 0);
		$todo_title		= $this->request->variable('todo_title', '');

		$index 			= $this->dlext_main->index();

		if ($cancel)
		{
			$todo_id = 0;
			$submit = '';
			$save = '';
		}

		/*
		* create todo list
		*/
		if (!$this->config['dl_todo_onoff'])
		{
			redirect($this->helper->route('oxpus_dlext_index'));
		}

		if ($this->config['dl_todo_onoff'] == 1)
		{
			redirect($this->helper->route('oxpus_dlext_todo'));
		}

		if ($action == 'cat' && !$this->auth->acl_get('a_'))
		{
			$action = 'entry';
		}

		$this->todo_access_ids = $this->dlext_main->full_index(0, 0, 0, $this->dlext_constants::DL_AUTH_CHECK_MOD);
		$can_edit = $this->dlext_constants::DL_FALSE;

		if ($action == 'cat')
		{
			if ($sort && $todo_cat_id)
			{
				$sql = 'SELECT todo_cat_sort FROM ' . $this->dlext_table_dl_todo_cat . ' WHERE todo_cat_id = ' . (int) $todo_cat_id;
				$result = $this->db->sql_query($sql);
				$todo_cat_sort = $this->db->sql_fetchfield('todo_cat_sort');
				$this->db->sql_freeresult($result);

				if ($sort == -1)
				{
					if ($todo_cat_sort <= 10)
					{
						$todo_cat_sort = 0;
					}
					else
					{
						$todo_cat_sort -= 15;
					}
				}
				else if ($sort == 1)
				{
					$todo_cat_sort += 15;
				}

				$sql = 'UPDATE ' . $this->dlext_table_dl_todo_cat . ' SET ' . $this->db->sql_build_array('UPDATE', [
					'todo_cat_sort' => $todo_cat_sort,
				]) . ' WHERE todo_cat_id = ' . (int) $todo_cat_id;
				$this->db->sql_query($sql);

				$this->cat_resort();
			}

			if ($delete && $todo_cat_id)
			{
				if (confirm_box($this->dlext_constants::DL_TRUE))
				{
					$sql = 'DELETE FROM ' . $this->dlext_table_dl_todo_cat . ' WHERE todo_cat_id = ' . (int) $todo_cat_id;
					$this->db->sql_query($sql);

					$this->cat_resort();
				}
				else
				{
					$s_hidden_fields = [
						'view'			=> 'todo',
						'action'		=> 'cat',
						'todo_cat_id'	=> $todo_cat_id,
						'submit'		=> $this->dlext_constants::DL_FALSE,
						'delete'		=> $this->dlext_constants::DL_TRUE,
					];

					confirm_box($this->dlext_constants::DL_FALSE, $this->language->lang('DL_DELETE_CAT'), build_hidden_fields($s_hidden_fields), '@oxpus_dlext/helpers/dl_confirm_body.html');
				}
			}

			if ($submit && !$cancel && !$edit)
			{
				if (!check_form_key('dl_todo') && $todo_cat_id)
				{
					trigger_error('FORM_INVALID');
				}

				$sql_ary = [
					'todo_catname'		=> $todo_catname,
					'todo_cat_color'	=> $todo_cat_color,
				];

				if ($todo_cat_id)
				{
					$sql = 'UPDATE ' . $this->dlext_table_dl_todo_cat . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE todo_cat_id = ' . (int) $todo_cat_id;

					$message = $this->user->lang('DL_CATEGORY_UPDATED');
				}
				else
				{
					$sql = 'INSERT INTO ' . $this->dlext_table_dl_todo_cat . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);

					$message = $this->user->lang('DL_CATEGORY_ADDED');
				}

				$this->db->sql_query($sql);

				$this->cat_resort();

				$meta_url = $this->helper->route('oxpus_dlext_todo_ext', ['action' => 'cat']);
				$message .= '<br><br>' . $this->user->lang('CLICK_RETURN_TODO_EDIT', '<a href="' . $meta_url . '">', '</a>');

				meta_refresh(3, $meta_url);

				trigger_error($message);
			}

			if ($edit)
			{
				if ($todo_cat_id)
				{
					$sql = 'SELECT * FROM ' . $this->dlext_table_dl_todo_cat . ' WHERE todo_cat_id = ' . (int) $todo_cat_id;
					$result = $this->db->sql_query($sql);
					$todo_cat_data = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);
				}
				else
				{
					$todo_cat_data = [
						'todo_catname'		=> $todo_catname,
						'todo_cat_color'	=> $todo_cat_color,
					];
				}

				$s_hidden_fields = [
					'view'			=> 'todo',
					'action'		=> 'cat',
					'edit'			=> $this->dlext_constants::DL_FALSE,
					'todo_cat_id'	=> $todo_cat_id,
				];

				add_form_key('dl_todo');

				$this->template->assign_vars([
					'TODO_CATNAME'		=> $todo_cat_data['todo_catname'],
					'TODO_CAT_COLOR'	=> $todo_cat_data['todo_cat_color'],

					'S_TODO_CAT_HIDDEN'	=> build_hidden_fields($s_hidden_fields),
					'S_TODO_CAT_FORM'	=> $this->helper->route('oxpus_dlext_todo_ext', ['action' => 'cat']),
					'S_TODO_CAT_ACTION'	=> 'cat_edit',
				]);
			}
			else
			{
				$sql_array = [];

				$sql_array['SELECT'] = 'c.todo_catname, c.todo_cat_color, c.todo_cat_id, count(e.todo_id) as anztodo';
				$sql_array['FROM'][$this->dlext_table_dl_todo_cat] = 'c';
				$sql_array['LEFT_JOIN'][] = [
					'FROM'	=>[$this->dlext_table_dl_todo => 'e'],
					'ON'	=> 'e.todo_cat_id = c.todo_cat_id',
				];
				$sql_array['GROUP_BY'] = 'c.todo_catname, c.todo_cat_color, c.todo_cat_id';
				$sql_array['ORDER_BY'] = 'c.todo_cat_sort';

				$sql = $this->db->sql_build_query('SELECT', $sql_array);
				$result = $this->db->sql_query($sql);
				$total_cats = $this->db->sql_affectedrows();

				if ($total_cats)
				{
					while ($row = $this->db->sql_fetchrow($result))
					{
						$cat_id = $row['todo_cat_id'];

						$this->template->assign_block_vars('todo_row', [
							'TODO_CATNAME'		=> $row['todo_catname'],
							'TODO_CAT_COLOR'	=> $row['todo_cat_color'],

							'U_CAT_SORT_UP'		=> $this->helper->route('oxpus_dlext_todo_ext', ['action' => 'cat', 'todo_cat_id' => $cat_id, 'sort' => -1]),
							'U_CAT_SORT_DOWN'	=> $this->helper->route('oxpus_dlext_todo_ext', ['action' => 'cat', 'todo_cat_id' => $cat_id, 'sort' => 1]),
							'U_CAT_EDIT'		=> $this->helper->route('oxpus_dlext_todo_ext', ['action' => 'cat', 'todo_cat_id' => $cat_id, 'edit' => 1]),
							'U_CAT_DELETE'		=> ($row['anztodo'] == 0) ? $this->helper->route('oxpus_dlext_todo_ext', ['action' => 'cat', 'todo_cat_id' => $cat_id, 'delete' => 1]) : '',
						]);
					}
				}
				else
				{
					$this->template->assign_var('S_NO_TODO_CATS', true);
				}

				$this->db->sql_freeresult($result);

				add_form_key('dl_todo');

				$this->template->assign_vars([
					'DL_MOD_TODO_MODE'		=> $this->user->lang('DL_ACP_CATEGORIES_MANAGEMENT'),
					'S_FORM_TODO_CAT_ADD'	=> $this->helper->route('oxpus_dlext_todo_ext', ['action' => 'cat', 'edit' => 1]),
					'S_TODO_EDIT_ENTRY'		=> $this->helper->route('oxpus_dlext_todo_ext'),
				]);
			}
		}
		else
		{
			if (count($this->todo_access_ids) > 0 && $this->user->data['is_registered'])
			{
				// Save or delete a todo
				if ($submit && !$cancel)
				{
					if ($save && $df_id)
					{
						if (!check_form_key('dl_todo'))
						{
							trigger_error('FORM_INVALID');
						}

						if ($todo_id)
						{
							$sql = 'UPDATE ' . $this->dlext_table_dl_todo . ' SET ' . $this->db->sql_build_array('UPDATE', [
								'todo_cat_id'	=> $todo_cat_id,
								'df_id'			=> $df_id,
								'todo_title'	=> $todo_title
							]) . ' WHERE todo_id = ' . (int) $todo_id;
						}
						else
						{
							$sql = 'INSERT INTO ' . $this->dlext_table_dl_todo . ' ' . $this->db->sql_build_array('INSERT', [
								'todo_cat_id'	=> $todo_cat_id,
								'df_id'			=> $df_id,
								'todo_title'	=> $todo_title,
								'todo_sort'		=> 0
							]);
						}

						$this->db->sql_query($sql);

						$this->todo_resort($df_id);

						$meta_url	= $this->helper->route('oxpus_dlext_todo_ext', ['df_id' => $df_id]);
						$message = $this->language->lang('DL_DOWNLOAD_UPDATED') . '<br><br>' . $this->language->lang('CLICK_RETURN_TODO_EDIT', '<a href="' . $meta_url . '">', '</a>');

						meta_refresh(3, $meta_url);

						trigger_error($message);
					}

					// Delete or not delete?
					if ($delete && $df_id && $todo_id)
					{
						if (confirm_box($this->dlext_constants::DL_TRUE))
						{
							$sql = 'DELETE FROM ' . $this->dlext_table_dl_todo . ' WHERE df_id = ' . (int) $df_id . ' AND todo_id = ' . (int) $todo_id;
							$this->db->sql_query($sql);

							$this->todo_resort($df_id);
						}
						else
						{
							$s_hidden_fields = [
								'view'		=> 'todo',
								'todo_id'	=> $todo_id,
								'df_id'		=> $df_id,
								'submit'	=> $this->dlext_constants::DL_TRUE,
								'delete'	=> $this->dlext_constants::DL_TRUE,
							];

							confirm_box($this->dlext_constants::DL_FALSE, $this->language->lang('DELETE_POST'), build_hidden_fields($s_hidden_fields), '@oxpus_dlext/helpers/dl_confirm_body.html');
						}
					}
				}

				// Some sorting...
				if ($sort && $df_id && $todo_id)
				{
					$sql = 'SELECT todo_sort FROM ' . $this->dlext_table_dl_todo . '
							WHERE todo_id = ' . (int) $todo_id . '
								AND df_id = ' . (int) $df_id;
					$result = $this->db->sql_query($sql);
					$todo_sort = $this->db->sql_fetchfield('todo_sort');
					$this->db->sql_freeresult($result);
					if ($sort == -1)
					{
						if ($todo_sort <= 10)
						{
							$todo_sort = 0;
						}
						else
						{
							$todo_sort -= 15;
						}
					}
					else if ($sort == 1)
					{
						$todo_sort += 15;
					}

					$sql = 'UPDATE ' . $this->dlext_table_dl_todo . ' SET ' . $this->db->sql_build_array('UPDATE', [
						'todo_sort' => $todo_sort,
					]) . ' WHERE todo_id = ' . (int) $todo_id . ' AND df_id = ' . (int) $df_id;
					$this->db->sql_query($sql);

					$this->todo_resort($df_id);
				}

				// Will we edit a todo??
				if ($edit && $df_id)
				{
					add_form_key('dl_todo');

					$fields			= ['description', 'desc_uid', 'desc_flags', 'hack_version', 'cat'];
					$dl_file		= $this->dlext_files->all_files(0, [], [], $df_id, 0, $fields);

					$description	= $dl_file['description'];
					$desc_uid		= $dl_file['desc_uid'];
					$desc_flags		= $dl_file['desc_flags'];
					$hack_version	= $dl_file['hack_version'];

					$text_ary		= generate_text_for_edit($description, $desc_uid, $desc_flags);
					$description	= $text_ary['text'];

					$todo = [];

					if ($todo_id)
					{
						$sql = 'SELECT todo_cat_id, todo_title
								FROM ' . $this->dlext_table_dl_todo . '
								WHERE df_id = ' . (int) $df_id . '
								  AND todo_id = ' . (int) $todo_id;
						$result = $this->db->sql_query($sql);
						$row = $this->db->sql_fetchrow($result);
						$todo['todo_cat_id']	= $row['todo_cat_id'];
						$todo['todo_title']		= $row['todo_title'];
						$this->db->sql_freeresult($result);
					}
					else
					{
						$todo['todo_cat_id']	= 0;
						$todo['todo_title']		= '';
					}

					$this->select_todo_cat($todo['todo_cat_id']);

					$this->select_dl_cat($df_id);

					if (in_array($dl_file['cat'], $this->todo_access_ids))
					{
						$can_edit = $this->dlext_constants::DL_TRUE;
					}

					$this->template->assign_vars([
						'DL_DOWNLOAD_NAME'	=> $description,
						'DL_HACK_VERSION'	=> $hack_version,
						'DL_TODO_TITLE'		=> $todo['todo_title'],

						'S_FORM_TODO_ADD'	=> $this->helper->route('oxpus_dlext_todo_ext', ['df_id' => $df_id, 'todo_id' => $todo_id, 'submit' => 1, 'save' => 1]),
						'S_TODO_EDIT'		=> $this->dlext_constants::DL_TRUE,
						'S_CAN_EDIT'		=> $can_edit,
					]);
				}
				else
				{
					$sql = 'SELECT	c.cat_name,
							d.id, d.description, d.desc_uid, d.desc_flags, d.hack_version, d.cat,
							tc.todo_catname, tc.todo_cat_color, tc.todo_cat_id,
							te.todo_title, te.todo_id
					FROM ' . $this->dlext_table_downloads . ' d,
						' . $this->dlext_table_dl_cat . ' c,
						' . $this->dlext_table_dl_todo_cat . ' tc,
						' . $this->dlext_table_dl_todo . ' te
					WHERE d.cat = c.id
						AND ' . $this->db->sql_in_set('d.cat', $this->todo_access_ids) . '
						AND te.df_id = d.id
						AND tc.todo_cat_id = te.todo_cat_id
					ORDER BY c.parent, c.sort, d.description, tc.todo_cat_sort, te.todo_sort';
					$result = $this->db->sql_query($sql);

					$total_possible_todo = $this->db->sql_affectedrows();
					$cur_dl = 0;
					$cur_cat = 0;

					while ($row = $this->db->sql_fetchrow($result))
					{
						$df_id			= $row['id'];
						$cat_id			= $row['cat'];
						$todo_id		= $row['todo_id'];
						$todo_cat_id	= $row['todo_cat_id'];

						$description	= $row['description'];
						$desc_uid		= $row['desc_uid'];
						$desc_flags		= $row['desc_flags'];
						$hack_version	= $row['hack_version'];

						$text_ary		= generate_text_for_edit($description, $desc_uid, $desc_flags);
						$description	= $text_ary['text'];

						if ($cur_dl <> $df_id)
						{
							$this->template->assign_block_vars('todolist_row', [
								'DL_FILENAME'		=> $description,
								'DL_FILE_LINK'		=> $this->helper->route('oxpus_dlext_details', ['df_id' => $df_id]),
								'DL_HACK_VERSION'	=> $hack_version,
								'DL_CAT_NAME'		=> $row['cat_name'],
							]);

							$cur_dl = $df_id;
						}

						if ($cur_cat <> $todo_cat_id)
						{
							$this->template->assign_block_vars('todolist_row.todolist_cat', [
								'TODO_CATNAME'		=> $row['todo_catname'],
								'TODO_CAT_COLOR'	=> $row['todo_cat_color'],
							]);

							$cur_cat = $todo_cat_id;
						}

						if (in_array($cat_id, $this->todo_access_ids))
						{
							$can_edit = $this->dlext_constants::DL_TRUE;
						}

						$this->template->assign_block_vars('todolist_row.todolist_cat.todolist_entry', [
							'TODO_TITLE'		=> $row['todo_title'],

							'U_TODO_SORT_UP'	=> $this->helper->route('oxpus_dlext_todo_ext', ['sort' => -1, 'df_id' => $df_id, 'todo_id' => $todo_id]),
							'U_TODO_SORT_DOWN'	=> $this->helper->route('oxpus_dlext_todo_ext', ['sort' => 1, 'df_id' => $df_id, 'todo_id' => $todo_id]),
							'U_DL_TODO_EDIT'	=> $this->helper->route('oxpus_dlext_todo_ext', ['edit' => 1, 'df_id' => $df_id, 'todo_id' => $todo_id]),
							'U_DL_TODO_DELETE'	=> $this->helper->route('oxpus_dlext_todo_ext', ['delete' => 1, 'submit' => 1, 'df_id' => $df_id, 'todo_id' => $todo_id]),
						]);
					}

					$this->db->sql_freeresult($result);

					$this->select_dl_cat(0);

					$this->select_todo_cat(0);

					add_form_key('dl_todo');

					$this->template->assign_vars([
						'S_FORM_TODO_ADD'	=> $this->helper->route('oxpus_dlext_todo_ext', ['save' => 1]),
						'S_DL_NO_TODOLIST'	=> ($total_possible_todo) ? $this->dlext_constants::DL_FALSE : $this->dlext_constants::DL_TRUE,
						'S_TODO_CATS'		=> ($this->auth->acl_get('a_')) ? $this->helper->route('oxpus_dlext_todo_ext', ['action' => 'cat']) : '',
						'S_CAN_EDIT'		=> $can_edit,
					]);
				}
			}
			else
			{
				trigger_error($this->language->lang('DL_NO_PERMISSION'), E_USER_WARNING);
			}
		}

		/*
		* include the mod footer
		*/
		$this->dlext_footer->set_parameter('todo', 0, $df_id, $index);
		$this->dlext_footer->handle();

		/*
		* generate page
		*/
		return $this->helper->render('@oxpus_dlext/dl_todo_ext_body.html', $this->language->lang('DL_MOD_TODO'));
	}

	private function cat_resort()
	{
		$sql = 'SELECT todo_cat_id FROM ' . $this->dlext_table_dl_todo_cat . ' ORDER BY todo_cat_sort';
		$result = $this->db->sql_query($sql);

		$sort = 10;
		$todo_sort_ary = [];

		while ($row = $this->db->sql_fetchrow($result))
		{
			$todo_sort_ary[$row['todo_cat_id']] = $sort;
			$sort += 10;
		}

		$this->db->sql_freeresult($result);

		$this->db->sql_transaction('begin');

		foreach ($todo_sort_ary as $cat_id => $sort_value)
		{
			$sql = 'UPDATE ' . $this->dlext_table_dl_todo_cat . ' SET ' . $this->db->sql_build_array('UPDATE', [
				'todo_cat_sort' => $sort_value,
			]) . ' WHERE todo_cat_id = ' . (int) $cat_id;
			$this->db->sql_query($sql);
		}

		$this->db->sql_transaction('commit');
	}

	private function todo_resort($df_id)
	{
		$sql = 'SELECT e.todo_id
				FROM ' . $this->dlext_table_dl_todo . ' e, ' . $this->dlext_table_dl_todo_cat . ' c
				WHERE c.todo_cat_id = e.todo_cat_id
				  AND e.df_id = ' . (int) $df_id . '
				ORDER BY c.todo_cat_sort, e.todo_sort';
		$result = $this->db->sql_query($sql);

		$sort = 10;
		$todo_sort_ary = [];

		while ($row = $this->db->sql_fetchrow($result))
		{
			$todo_sort_ary[$row['todo_id']] = $sort;
			$sort += 10;
		}

		$this->db->sql_freeresult($result);

		$this->db->sql_transaction('begin');

		foreach ($todo_sort_ary as $todo_id => $sort_value)
		{
			$sql = 'UPDATE ' . $this->dlext_table_dl_todo . ' SET ' . $this->db->sql_build_array('UPDATE', [
				'todo_sort' => $sort_value,
			]) . ' WHERE todo_id = ' . (int) $todo_id;
			$this->db->sql_query($sql);
		}

		$this->db->sql_transaction('commit');
	}

	private function select_todo_cat($cat_id)
	{
		$sql = 'SELECT todo_cat_id, todo_catname, todo_cat_color
				FROM ' . $this->dlext_table_dl_todo_cat . '
				ORDER BY todo_cat_sort';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('todo_cat_select', [
				'TODO_CAT_ID'		=> $row['todo_cat_id'],
				'TODO_CATNAME'		=> $row['todo_catname'],
				'TODO_CAT_COLOR'	=> $row['todo_cat_color'],
				'TODO_CAT_SELECT'	=> ($cat_id == $row['todo_cat_id']) ? ' selected ' : '',
			]);
		}

		$this->db->sql_freeresult($result);
	}

	private function select_dl_cat($df_id)
	{
		$sql = 'SELECT c.cat_name, d.id, d.description, d.desc_uid, d.desc_flags FROM ' . $this->dlext_table_downloads . ' d, ' . $this->dlext_table_dl_cat . ' c
			WHERE d.cat = c.id
				AND ' . $this->db->sql_in_set('d.cat', $this->todo_access_ids) . "
			ORDER BY c.parent, c.sort, d.description";
		$result = $this->db->sql_query($sql);

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
						'DL_SELECT'	=> ($df_id == $row['id']) ? ' selected' : '',
					]);
				}

				$this->template->assign_block_vars('dl_todo_select', [
					'DL_TYPE'	=> 'optend',
				]);

				$cur_cat = $category;
			}
		}
	}
}
