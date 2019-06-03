<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/*
* connect to phpBB
*/
if ( !defined('IN_PHPBB') )
{
	exit;
}

if (!$this->config['dl_todo_onoff'])
{
	trigger_error($this->language->lang('DL_NO_PERMISSION'), E_USER_WARNING);
}

$todo	= $this->request->variable('todo', '', true);

add_form_key('dl_todo');

// Save or delete a todo
if ($submit && !$cancel)
{
	if ($delete)
	{
		if (!$confirm)
		{
			$s_hidden_fields = array(
				'view'		=> 'todo',
				'action'	=> 'edit',
				'df_id'		=> $df_id,
				'submit'	=> true,
				'delete'	=> true,
			);

			$this->language->add_lang('posting');

			$this->template->set_filenames(array(
				'body' => 'dl_confirm_body.html')
			);

			page_header();

			$this->template->assign_vars(array(
				'MESSAGE_TITLE' => $this->language->lang('DELETE_MESSAGE'),
				'MESSAGE_TEXT' => $this->language->lang('DELETE_MESSAGE_CONFIRM'),

				'S_CONFIRM_ACTION' => $this->helper->route('oxpus_dlext_controller'),
				'S_HIDDEN_FIELDS' => build_hidden_fields($s_hidden_fields))
			);

			page_footer();
		}
		else
		{
			$todo = '';
		}
	}

	if ($df_id)
	{
		if (!check_form_key('dl_todo'))
		{
			trigger_error('FORM_INVALID');
		}

		$allow_bbcode		= ($this->config['allow_bbcode']) ? true : false;
		$allow_urls			= true;
		$allow_smilies		= ($this->config['allow_smilies']) ? true : false;
		$todo_uid			= '';
		$todo_bitfield		= '';
		$todo_flags			= 0;

		generate_text_for_storage($todo, $todo_uid, $todo_bitfield, $todo_flags, $allow_bbcode, $allow_urls, $allow_smilies);

		$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', array(
			'todo'			=> $todo,
			'todo_uid'		=> $todo_uid,
			'todo_bitfield'	=> $todo_bitfield,
			'todo_flags'	=> $todo_flags)) . ' WHERE id = ' . (int) $df_id . ' AND ' . $this->db->sql_in_set('cat', $todo_access_ids);
		$this->db->sql_query($sql);

		$meta_url	= $this->helper->route('oxpus_dlext_controller', array('view' => 'todo', 'action' => 'edit'));
		$message	= $this->language->lang('DOWNLOAD_UPDATED') . '<br /><br />' . $this->language->lang('CLICK_RETURN_TODO_EDIT', '<a href="' . $meta_url . '">', '</a>');

		meta_refresh(3, $meta_url);

		trigger_error($message);
	}
}

// Will we edit a todo??
if ($edit && $df_id)
{
	$dl_file = array();
	$dl_file = \oxpus\dlext\phpbb\classes\ dl_files::all_files(0, '', 'ASC', '', $df_id, 0, 'description, desc_uid, desc_flags, todo, todo_uid, todo_flags');

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
		'view'		=> 'todo',
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
	'S_FORM_ACTION'		=> $this->helper->route('oxpus_dlext_controller'),
));

// Build todo edit list for existing entries
$dl_todo = array();
$dl_todo = \oxpus\dlext\phpbb\classes\ dl_extra::get_todo();

if (isset($dl_todo['file_name'][0]))
{
	for ($i = 0; $i < sizeof($dl_todo['file_name']); $i++)
	{
		$df_id = $dl_todo['df_id'][$i];

		$this->template->assign_block_vars('todolist_row', array(
			'FILENAME'		=> $dl_todo['file_name'][$i],
			'FILE_LINK'		=> $this->helper->route('oxpus_dlext_controller', array('view' => 'detail', 'df_id' => $df_id)),
			'HACK_VERSION'	=> $dl_todo['hack_version'][$i],
			'TODO'			=> $dl_todo['todo'][$i],

			'U_TODO_EDIT'	=> $this->helper->route('oxpus_dlext_controller', array('view' => 'todo', 'action' => 'edit', 'edit' => true, 'df_id' => $df_id)),
			'U_TODO_DELETE'	=> $this->helper->route('oxpus_dlext_controller', array('view' => 'todo', 'action' => 'edit', 'delete' => true, 'submit' => true, 'df_id' => $df_id)),
		));
	}
}
else
{
	$this->template->assign_var('S_NO_TODOLIST', true);
}

page_header();
