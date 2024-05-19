<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\controller\ucp;

class ucp_config_controller implements ucp_config_interface
{
	/* phpbb objects */
	protected $request;
	protected $db;
	protected $user;
	protected $language;
	protected $config;
	protected $template;
	protected $dispatcher;

	/* extension owmed objects */
	public $u_action;

	protected $dlext_constants;

	/**
	 * Constructor
	 *
	 * @param \phpbb\request\request 				$request
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\user							$user
	 * @param \phpbb\language\language				$language
	 * @param \phpbb\config\config					$config
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\event\dispatcher_interface		$dispatcher
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 */
	public function __construct(
		\phpbb\request\request $request,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\user $user,
		\phpbb\language\language $language,
		\phpbb\config\config $config,
		\phpbb\template\template $template,
		\phpbb\event\dispatcher_interface $dispatcher,
		\oxpus\dlext\core\helpers\constants $dlext_constants
	)
	{
		$this->request			= $request;
		$this->db 				= $db;
		$this->user 			= $user;
		$this->language			= $language;
		$this->config 			= $config;
		$this->template 		= $template;
		$this->dispatcher		= $dispatcher;

		$this->dlext_constants	= $dlext_constants;
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
				'user_dl_auto_fav'				=> $this->request->variable('user_dl_auto_fav', 0),
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
			extract($this->dispatcher->trigger_event('oxpus.dlext.ucp_config_sql_update_before', compact($vars)));

			$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_array) . '
					WHERE user_id = ' . (int) $this->user->data['user_id'];
			$this->db->sql_query($sql);

			$message = $this->language->lang('DL_USER_CONFIG_SAVED', '<a href="' . $this->u_action . '">', '</a>');

			trigger_error($message);
		}

		add_form_key('dl_ucp');

		if (!$this->config['dl_sort_preform'])
		{
			$this->template->assign_var('S_DL_SORT_CONFIG_OPTIONS', $this->dlext_constants::DL_TRUE);
		}

		$this->template->assign_var('S_DL_UCP_CONFIG', $this->dlext_constants::DL_TRUE);

		add_form_key('dl_ucp');

		$template_ary = [
			'DL_MOD_RELEASE'		=> $this->language->lang('DL_MOD_VERSION_PUBLIC'),

			'S_DL_SUB_ON_INDEX'		=> $this->user->data['user_dl_sub_on_index'],
			'S_DL_SORT_USER_OPT'	=> $this->user->data['user_dl_sort_fix'],
			'S_DL_SORT_USER_EXT'	=> $this->user->data['user_dl_sort_opt'],
			'S_DL_SORT_USER_DIR'	=> $this->user->data['user_dl_sort_dir'],
			'S_DL_AUTOADD_TO_FAV'	=> $this->user->data['user_dl_auto_fav'],
			'S_DL_FORM_ACTION'		=> $this->u_action,
		];

		$user_sort_fields = [
			$this->dlext_constants::DL_SORT_DEFAULT		=> $this->language->lang('DL_DEFAULT_SORT'),
			$this->dlext_constants::DL_SORT_DESCRIPTION	=> $this->language->lang('DL_FILE_DESCRIPTION'),
			$this->dlext_constants::DL_SORT_FILE_NAME	=> $this->language->lang('DL_FILE_NAME'),
			$this->dlext_constants::DL_SORT_CLICKS		=> $this->language->lang('DL_KLICKS'),
			$this->dlext_constants::DL_SORT_FREE		=> $this->language->lang('DL_FREE'),
			$this->dlext_constants::DL_SORT_EXTERN		=> $this->language->lang('DL_EXTERN'),
			$this->dlext_constants::DL_SORT_FILE_SIZE	=> $this->language->lang('DL_FILE_SIZE'),
			$this->dlext_constants::DL_SORT_LAST_TIME	=> $this->language->lang('LAST_UPDATED'),
			$this->dlext_constants::DL_SORT_RATING		=> $this->language->lang('DL_RATING'),
		];

		$user_dl_sort_dir = [
			$this->dlext_constants::DL_SORT_ASC		=> $this->language->lang('ASCENDING'),
			$this->dlext_constants::DL_SORT_DESC	=> $this->language->lang('DESCENDING'),
		];

		foreach ($user_sort_fields as $key => $value)
		{
			$this->template->assign_block_vars('dl_sort_fields', [
				'DL_KEY'	=> $key,
				'DL_VALUE'	=> $value,
			]);
		}

		foreach ($user_dl_sort_dir as $key => $value)
		{
			$this->template->assign_block_vars('dl_sort_order', [
				'DL_KEY'	=> $key,
				'DL_VALUE'	=> $value,
			]);
		}

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
		extract($this->dispatcher->trigger_event('oxpus.dlext.ucp_config_template_before', compact($vars)));

		$this->template->assign_vars($template_ary);
	}
}
