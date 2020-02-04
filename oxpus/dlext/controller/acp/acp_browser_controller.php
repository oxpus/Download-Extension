<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\controller\acp;

use Symfony\Component\DependencyInjection\Container;

/**
* @package acp
*/
class acp_browser_controller implements acp_browser_interface
{
	public $u_action;
	public $db;
	public $user;
	public $auth;
	public $phpEx;
	public $phpbb_extension_manager;
	public $phpbb_container;
	public $phpbb_path_helper;
	public $phpbb_log;

	public $config;
	public $helper;
	public $language;
	public $request;
	public $template;
	public $pagination;

	public $ext_path;
	public $ext_path_web;
	public $ext_path_ajax;

	/*
	 * @param string								$phpEx
	 * @param Container 							$phpbb_container
	 * @param \phpbb\extension\manager				$phpbb_extension_manager
	 * @param \phpbb\path_helper					$phpbb_path_helper
	 * @param \phpbb\db\driver\driver_interfacer	$db
	 * @param \phpbb\log\log_interface 				$log
	 * @param \phpbb\auth\auth						$auth
	 * @param \phpbb\user							$user
	 */
	public function __construct(
		$phpEx,
		Container $phpbb_container,
		\phpbb\extension\manager $phpbb_extension_manager,
		\phpbb\path_helper $phpbb_path_helper,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\log\log_interface $log,
		\phpbb\auth\auth $auth,
		\phpbb\user $user
	)
	{
		$this->phpEx					= $phpEx;
		$this->phpbb_container			= $phpbb_container;
		$this->phpbb_extension_manager	= $phpbb_extension_manager;
		$this->phpbb_path_helper		= $phpbb_path_helper;
		$this->db						= $db;
		$this->phpbb_log				= $log;
		$this->auth						= $auth;
		$this->user						= $user;

		$this->config					= $this->phpbb_container->get('config');
		$this->helper					= $this->phpbb_container->get('controller.helper');
		$this->language					= $this->phpbb_container->get('language');
		$this->request					= $this->phpbb_container->get('request');
		$this->template					= $this->phpbb_container->get('template');
		$this->pagination				= $this->phpbb_container->get('pagination');

		$this->ext_path					= $this->phpbb_extension_manager->get_extension_path('oxpus/dlext', true);
		$this->ext_path_web				= $this->phpbb_path_helper->update_web_root_path($this->ext_path);
		$this->ext_path_ajax			= $this->ext_path_web . 'assets/javascript/';
	}

	public function set_action($u_action)
	{
		$this->u_action = $u_action;
	}

	public function handle()
	{
		$this->auth->acl($this->user->data);
		if (!$this->auth->acl_get('a_dl_browser'))
		{
			trigger_error('DL_NO_PERMISSION', E_USER_WARNING);
		}

		include_once($this->ext_path . 'phpbb/includes/acm_init.' . $this->phpEx);

		$file_exist = false;
		$data_file = $this->ext_path . 'phpbb/includes/user_agents.inc';

		if (file_exists($data_file))
		{
			include($data_file);
			$file_exist = true;
		}
		else
		{
			trigger_error('DL_NO_USER_AGENTS_FILE', E_USER_WARNING);
		}
		
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
					redirect($this->u_action . '&amp;agent_id=' . $a_id . '&amp;t=' . microtime());
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
		
			$saved = $this->_save_ua_file($agent_title, $agent_strings, $data_file);
		
			if (!$saved)
			{
				trigger_error($this->language->lang('DL_USER_AGENTS_NOT_SAVED'), E_USER_WARNING);
			}
		
			if ($new_agent)
			{
				redirect($this->u_action . '&amp;agent_id=' . $next_agent_id . '&amp;t=' . microtime());
			}
		
			$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_BROWSER_SAVE');
		
			$message = $this->language->lang('DL_USER_AGENTS_SAVED') . adm_back_link($this->u_action);
			trigger_error($message);
		}
		else if ($delete && $sub_a_id)
		{
			if (confirm_box(true))
			{
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
		
				$saved = $this->_save_ua_file($agent_title, $agent_strings, $data_file);
		
				if (!$saved)
				{
					trigger_error($this->language->lang('DL_USER_AGENTS_NOT_SAVED'), E_USER_WARNING);
				}
		
				$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_BROWSER_DEL');
		
				$message = $this->language->lang('DL_USER_AGENTS_DELETED') . adm_back_link($this->u_action);
				trigger_error($message);
			}
			else
			{
				$s_hidden_fields = array(
					'delete'	=> true,
					'agent_id'	=> $sub_a_id,
				);

				confirm_box(false, 'DL_CONFIRM_DEL_UA', build_hidden_fields($s_hidden_fields));
			}
		}
		
		// Main work here: Handle/Display the user agents ;-(
		if ($sub_a_id)
		{
			// Edit a browser entry
			$cur_agent_id = 0;
			$cur_agent_title = '';
			$cur_agent_browser = '';

			foreach ($agent_title as $key => $value)
			{
				$tmp_ary = explode('|', $agent_title[$key]);
				$agent_id = $tmp_ary[0];
				$agent_name = $tmp_ary[1];
						
				if ($agent_id == $sub_a_id)
				{
					$cur_agent_id = $agent_id;
					$cur_agent_title = $agent_name;
					break;
				}
			}

			if ($cur_agent_id)
			{
				foreach ($agent_strings as $key => $value)
				{
					$tmp_ary = explode('|', $agent_strings[$key]);
					$agent_id = $tmp_ary[0];
					$agent_name = $tmp_ary[1];
		
					if ($agent_id == $cur_agent_id)
					{
						$cur_agent_browser .= $agent_name . "\n";
					}
				}

				$this->template->assign_vars(array(
					'AGENT_ID'		=> $cur_agent_id,
					'AGENT_TITLE'	=> $cur_agent_title,
					'AGENT_BROWSER'	=> $cur_agent_browser,
				));
			}
			else
			{
				redirect($this->u_action . '&amp;t=' . microtime());
			}
		}
		else
		{
			$total_agents = sizeof($agent_title);
		
			if ($total_agents > $this->config['dl_links_per_page'])
			{
				$this->pagination->generate_template_pagination(
					$this->u_action,
					'pagination',
					'start',
					$total_agents,
					$this->config['dl_links_per_page'],
					$start
				);
					
				$this->template->assign_vars(array(
					'PAGE_NUMBER'	=> $this->pagination->on_page($total_agents, $this->config['dl_links_per_page'], $start),
					'TOTAL_DL'		=> $this->language->lang('VIEW_USER_AGENTS', $total_agents),
				));
			}
		
			// Display the user agents
			$latest_agent = $start + $this->config['dl_links_per_page'];
		
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
			
					$u_edit		= $this->u_action . '&amp;agent_id=' . $agent_id;
					$u_delete	= $this->u_action . '&amp;delete=1&amp;agent_id=' . $agent_id;
			
					$this->template->assign_var('S_LIST_AGENTS', true);
			
					$this->template->assign_block_vars('user_agents', array(
						'AGENT_ID'		=> $agent_id,
						'AGENT_NAME'	=> $agent_name,
			
						'U_EDIT'		=> $u_edit,
						'U_DELETE'		=> $u_delete,
					));
				}
			}
		}
		
		$this->template->assign_vars(array(
			'S_FORM_ACTION'		=> $this->u_action,
			'U_BACK'			=> (isset($cur_agent_id)) ? $this->u_action : '',
		));
	}

	public function _save_ua_file($agent_title, $agent_strings, $data_file)
	{
		if (!is_writable($data_file))
		{
			trigger_error('DL_UA_FILE_NOT_WRITABLE', E_USER_WARNING);
		}
	
		natcasesort($agent_strings);
	
		$file_content = "<?php\n\n// @package Download Extension\n\nnamespace oxpus\dlext\phpbb\includes;\n\nif ( !defined('IN_PHPBB') ) { exit; }\n\n";
	
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
}
