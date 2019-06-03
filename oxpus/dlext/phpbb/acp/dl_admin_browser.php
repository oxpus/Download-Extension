<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

$file_exist = false;
$data_file = $ext_path . 'phpbb/helpers/dl_user_agents.' . $phpEx;

if (file_exists($data_file))
{
	include($data_file);
	$file_exist = true;
}
else
{
	trigger_error('DL_NO_USER_AGENTS_FILE', E_USER_WARNING);
}

function save_ua_file($agent_title, $agent_strings, $data_file)
{
	if (!is_writable($data_file))
	{
		trigger_error('DL_UA_FILE_NOT_WRITABLE', E_USER_WARNING);
	}

	natcasesort($agent_strings);

	$file_content = "<?php\n\n// @package Download Extension\n\nnamespace oxpus\dlext\phpbb\helpers;\n\nif ( !defined('IN_PHPBB') ) { exit; }\n\n";

	$new_sort_ary_id = array();
	$new_sort_ary_txt = array();

	foreach ($agent_title AS $key => $value)
	{
		$tmp_ary = explode('|', $agent_title[$key]);
		$new_sort_ary_id[] = $tmp_ary[0];
		$new_sort_ary_txt[] = $tmp_ary[1];
	}

	array_multisort($new_sort_ary_txt, $new_sort_ary_id);

	foreach ($new_sort_ary_id AS $key => $value)
	{
		$file_content .= '$agent_title[] = "' . $new_sort_ary_id[$key] . '|' . $new_sort_ary_txt[$key] . "\";\n";
	}

	foreach ($agent_strings AS $key => $value)
	{
		$file_content .= '$agent_strings[] = "' . $agent_strings[$key] . "\";\n";
	}

	$df = fopen($data_file, 'w');
	fwrite($df, $file_content);
	fclose($df);

	if (file_exists($data_file))
	{
		chmod($data_file, 0755);
		return true;
	}
	else
	{
		return false;
	}
}

$sub_a_id		= $request->variable('agent_id', '');
$sub_a_txt		= $request->variable('agent_title', '', true);
$sub_a_br		= $request->variable('agent_browser', '', true);
$cancel			= $request->variable('cancel', '');
$delete			= $request->variable('delete', 0);
$submit			= $request->variable('submit', '');
$new_browser	= $request->variable('new_browser', 0);
$start			= $request->variable('start', 0);

$confirm_delete = false;

if ($cancel)
{
	$delete = 0;
	$submit = '';
	$sub_a_id = 0;
}

add_form_key('dl_adm_browser');

if ($submit)
{
	if (!check_form_key('dl_adm_browser'))
	{
		trigger_error('FORM_INVALID', E_USER_WARNING);
	}

	$agent_check = false;
	$agent_text = 0;
	$new_agent = false;

	for ($i = 0; $i < sizeof($agent_title); $i++)
	{
		$tmp_ary = explode('|', $agent_title[$i]);
		$a_id = $tmp_ary[0];
		$a_txt = $tmp_ary[1];

		if ($sub_a_txt == $a_txt && $new_browser)
		{
			redirect($basic_link . '&amp;agent_id=' . $a_id);
		}

		if ($a_id == $sub_a_id)
		{
			$agent_title[$i] = $sub_a_id . '|' . $sub_a_txt;
			$agent_check = true;
		}
	}

	if ($agent_check)
	{
		for ($i = 0; $i < sizeof($agent_strings); $i++)
		{
			$tmp_ary = explode('|', $agent_strings[$i]);
			$a_id = $tmp_ary[0];

			if ($a_id == $sub_a_id)
			{
				unset($agent_strings[$i]);
				$agent_text = $sub_a_id;
			}
		}

		$agents = array_unique(explode("\n", $sub_a_br));

		foreach($agents AS $key => $value)
		{
			$agent_strings[] = $sub_a_id . '|' . $agents[$key];
		}
	}
	else
	{
		$last_agent = explode('|', $agent_title[sizeof($agent_title) - 1]);
		$next_agent_id = $last_agent[0] + 1;
		$agent_title[] = $next_agent_id . '|' . $sub_a_txt;
		$new_agent = true;
	}

	$saved = save_ua_file($agent_title, $agent_strings, $data_file);

	if (!$saved)
	{
		trigger_error($language->lang('DL_USER_AGENTS_NOT_SAVED'), E_USER_WARNING);
	}

	if ($new_agent)
	{
		redirect($basic_link . '&amp;agent_id=' . $next_agent_id);
	}

	$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_BROWSER_SAVE');

	$message = $language->lang('DL_USER_AGENTS_SAVED') . "<br /><br />" . $language->lang('CLICK_RETURN_BROWSERADMIN', '<a href="' . $basic_link . '">', '</a>') . adm_back_link($this->u_action);
	trigger_error($message);
}
else if ($delete && $sub_a_id)
{
	if (!$confirm)
	{
		$template->set_filenames(array(
			'confirm_body' => 'dl_confirm_body.html')
		);

		$s_hidden_fields = array(
			'delete'	=> true,
			'agent_id'	=> $sub_a_id,
		);

		$template->assign_vars(array(
			'MESSAGE_TITLE' => $language->lang('INFORMATION'),
			'MESSAGE_TEXT' => $language->lang('DL_CONFIRM_DELETE_USER_AGENTS'),

			'S_CONFIRM_ACTION' => $basic_link,
			'S_HIDDEN_FIELDS' => build_hidden_fields($s_hidden_fields))
		);

		$template->assign_var('S_DL_CONFIRM', true);

		$template->assign_display('confirm_body');

		$confirm_delete = true;
	}
	else
	{
		if (!check_form_key('dl_adm_browser'))
		{
			trigger_error('FORM_INVALID', E_USER_WARNING);
		}

		$agent_check = 0;

		for ($i = 0; $i < sizeof($agent_title); $i++)
		{
			$tmp_ary = explode('|', $agent_title[$i]);
			$a_id = $tmp_ary[0];

			if ($a_id == $sub_a_id)
			{
				unset($agent_title[$i]);
				$agent_check = $a_id;
			}
		}

		if ($agent_check)
		{
			for ($i = 0; $i < sizeof($agent_strings); $i++)
			{
				$tmp_ary = explode('|', $agent_strings[$i]);

				if ($tmp_ary[0] == $agent_check)
				{
					unset($agent_strings[$i]);
				}
			}
		}

		$saved = save_ua_file($agent_title, $agent_strings, $data_file);

		if (!$saved)
		{
			trigger_error($language->lang('DL_USER_AGENTS_NOT_SAVED'), E_USER_WARNING);
		}

		$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_BROWSER_DEL');

		$message = $language->lang('DL_USER_AGENTS_DELETED') . "<br /><br />" . $language->lang('CLICK_RETURN_BROWSERADMIN', '<a href="' . $basic_link . '">', '</a>') . adm_back_link($this->u_action);
		trigger_error($message);
	}
}

// Main work here: Handle/Display the user agents ;-(
if ($sub_a_id)
{
	// Edit a browser entry
	$cur_agent_id = 0;
	$cur_agent_title = '';
	$cur_agent_browser = '';

	for ($i = 0; $i < sizeof($agent_title); $i++)
	{
		$tmp_ary = explode('|', $agent_title[$i]);
		$agent_id = $tmp_ary[0];
		$agent_name = $tmp_ary[1];

		if ($agent_id == $sub_a_id)
		{
			$cur_agent_id = $agent_id;
			$cut_agent_title = $agent_name;
			break;
		}
	}

	if ($cur_agent_id)
	{
		for ($i = 0; $i < sizeof($agent_strings); $i++)
		{
			$tmp_ary = explode('|', $agent_strings[$i]);
			$agent_id = $tmp_ary[0];
			$agent_name = $tmp_ary[1];

			if ($agent_id == $sub_a_id)
			{
				$cur_agent_browser .= $agent_name . "\n";
			}
		}

		$template->assign_vars(array(
			'AGENT_ID'		=> $cur_agent_id,
			'AGENT_TITLE'	=> $cut_agent_title,
			'AGENT_BROWSER'	=> $cur_agent_browser,
		));
	}
	else
	{
		redirect($basic_link);
	}
}
else
{
	$total_agents = sizeof($agent_title);

	if ($total_agents > $config['dl_links_per_page'])
	{
		$pagination = $phpbb_container->get('pagination');
		$pagination->generate_template_pagination(
			$this->u_action,
			'pagination', 'start', $total_agents, $config['dl_links_per_page'], $start);
			
		$template->assign_vars(array(
			'PAGE_NUMBER'	=> $pagination->on_page($total_agents, $config['dl_links_per_page'], $start),
			'TOTAL_DL'		=> $language->lang('VIEW_USER_AGENTS', $total_agents),
		));
	}

	// Display the user agents
	$latest_agent = $start + $config['dl_links_per_page'];

	if ($latest_agent > $total_agents)
	{
		$latest_agent = $total_agents;
	}

	for ($i = $start; $i < $latest_agent; $i++)
	{
		if (isset($agent_title[$i]))
		{
			$tmp_ary = explode('|', $agent_title[$i]);
			$agent_id = $tmp_ary[0];
			$agent_name = $tmp_ary[1];
	
			$u_edit		= $basic_link . '&amp;agent_id=' . $agent_id;
			$u_delete	= $basic_link . '&amp;delete=1&amp;agent_id=' . $agent_id;
	
			$template->assign_var('S_LIST_AGENTS', true);
	
			$template->assign_block_vars('user_agents', array(
				'AGENT_ID'		=> $agent_id,
				'AGENT_NAME'	=> $agent_name,
	
				'U_EDIT'		=> $u_edit,
				'U_DELETE'		=> $u_delete,
			));
		}
	}
}

$template->assign_vars(array(
	'S_FORM_ACTION'		=> $basic_link,
	'U_BACK'			=> (isset($cur_agent_id)) ? $this->u_action : '',
));

if (!$confirm_delete)
{
	$template->set_filenames(array(
		'browser' => 'dl_browser_body.html')
	);

	$template->assign_var('S_DL_BROWSER', true);
	$template->assign_display('browser');
}
