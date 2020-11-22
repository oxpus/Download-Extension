<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\controller\ucp;

use Symfony\Component\DependencyInjection\Container;

class ucp_config_controller implements ucp_config_interface
{
	protected $u_action;

	/* @var \phpbb\request\request_interface */
	protected $request;

	/* @var \phpbb\db\driver\driver_interface */
	protected $db;

	/* @var \phpbb\user */
	protected $user;

	/* @var \phpbb\language\language */
	protected $language;

	/* @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\template\template */
	protected $template;

	protected $phpbb_dispatcher;

	/**
	* Constructor
	*
	* @param \phpbb\request\request_interface 		$request
	* @param \phpbb\db\driver\driver_interfacer		$db
	* @param \phpbb\user							$user
	* @param \phpbb\language\language				$language
	* @param \phpbb\config\config					$config
	* @param \phpbb\template\template				$template
	* @param \phpbb\event\dispatcher_interface		$phpbb_dispatcher
	*/
	public function __construct(
		\phpbb\request\request_interface $request,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\user $user,
		\phpbb\language\language $language,
		\phpbb\config\config $config,
		\phpbb\template\template $template,
		\phpbb\event\dispatcher_interface $phpbb_dispatcher
	)
	{
		$this->request			= $request;
		$this->db 				= $db;
		$this->user 			= $user;
		$this->language			= $language;
		$this->config 			= $config;
		$this->template 		= $template;
		$this->phpbb_dispatcher	= $phpbb_dispatcher;
	}

	public function set_action($u_action)
	{
		$this->u_action = $u_action;
	}

	public function handle()
	{
		/*
		* init and get various values
		*/
		$submit = $this->request->variable('submit', '');

		if ($submit)
		{
			if (!check_form_key('dl_ucp'))
			{
				trigger_error('FORM_INVALID');
			}

			$sql_array = [
				'user_dl_sort_fix'				=> $this->request->variable('user_dl_sort_fix', 0),
				'user_dl_sort_opt'				=> $this->request->variable('user_dl_sort_opt', 0),
				'user_dl_sort_dir'				=> $this->request->variable('user_dl_sort_dir', 0),
				'user_dl_sub_on_index'			=> $this->request->variable('user_dl_sub_on_index', 0),
			];

			/**
			 * Save additional data for user download settings
			 *
			 * @event oxpus.dlext.ucp_config_sql_update_before
			 * @var array	sql_array		array of user's data for storage
			 * @since 8.1.0-RC2
			 */
			$vars = array(
				'sql_array',
			);
			extract($this->phpbb_dispatcher->trigger_event('oxpus.dlext.ucp_config_sql_update_before', compact($vars)));

			$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_array) . '
					WHERE user_id = ' . (int) $this->user->data['user_id'];
			$this->db->sql_query($sql);

			$message = $this->language->lang('DL_USER_CONFIG_SAVED', '<a href="' . $this->u_action . '">', '</a>');

			trigger_error($message);
		}

		add_form_key('dl_ucp');

		$s_user_dl_sort_fix = '<select name="user_dl_sort_fix">';
		$s_user_dl_sort_fix .= '<option value="0">' . $this->language->lang('DL_DEFAULT_SORT') . '</option>';
		$s_user_dl_sort_fix .= '<option value="1">' . $this->language->lang('DL_FILE_DESCRIPTION') . '</option>';
		$s_user_dl_sort_fix .= '<option value="2">' . $this->language->lang('DL_FILE_NAME') . '</option>';
		$s_user_dl_sort_fix .= '<option value="3">' . $this->language->lang('DL_KLICKS') . '</option>';
		$s_user_dl_sort_fix .= '<option value="4">' . $this->language->lang('DL_FREE') . '</option>';
		$s_user_dl_sort_fix .= '<option value="5">' . $this->language->lang('DL_EXTERN') . '</option>';
		$s_user_dl_sort_fix .= '<option value="6">' . $this->language->lang('DL_FILE_SIZE') . '</option>';
		$s_user_dl_sort_fix .= '<option value="7">' . $this->language->lang('LAST_UPDATED') . '</option>';
		$s_user_dl_sort_fix .= '<option value="8">' . $this->language->lang('DL_RATING') . '</option>';
		$s_user_dl_sort_fix .= '</select>';
		$s_user_dl_sort_fix = str_replace('value="' . $this->user->data['user_dl_sort_fix'] . '">', 'value="' . $this->user->data['user_dl_sort_fix'] . '" selected="selected">', $s_user_dl_sort_fix);

		$s_user_dl_sort_dir = '<select name="user_dl_sort_dir">';
		$s_user_dl_sort_dir .= '<option value="0">'.$this->language->lang('ASCENDING').'</option>';
		$s_user_dl_sort_dir .= '<option value="1">'.$this->language->lang('DESCENDING').'</option>';
		$s_user_dl_sort_dir .= '</select>';
		$s_user_dl_sort_dir = str_replace('value="' . $this->user->data['user_dl_sort_dir'] . '">', 'value="' . $this->user->data['user_dl_sort_dir'] . '" selected="selected">', $s_user_dl_sort_dir);

		if (!$this->config['dl_sort_preform'])
		{
			$this->template->assign_var('S_SORT_CONFIG_OPTIONS', true);
		}

		$this->template->assign_var('S_DL_UCP_CONFIG', true);

		add_form_key('dl_ucp');

		$template_ary = [
			'DL_MOD_RELEASE'			=> $this->language->lang('DL_MOD_VERSION_PUBLIC'),

			'S_USER_DL_SUB_ON_INDEX'	=> $this->user->data['user_dl_sub_on_index'],
			'S_DL_SORT_USER_OPT'		=> $s_user_dl_sort_fix,
			'S_DL_SORT_USER_EXT'		=> $this->user->data['user_dl_sort_opt'],
			'S_DL_SORT_USER_DIR'		=> $s_user_dl_sort_dir,
			'S_FORM_ACTION'				=> $this->u_action,
		];

		/**
		 * Display additional data for user download settings
		 *
		 * @event oxpus.dlext.ucp_config_template_before
		 * @var array	template_ary		template data for displaying
		 * @since 8.1.0-RC2
		 */
		$vars = array(
			'template_ary',
		);
		extract($this->phpbb_dispatcher->trigger_event('oxpus.dlext.ucp_config_template_before', compact($vars)));

		$this->template->assign_vars($template_ary);
	}
}
