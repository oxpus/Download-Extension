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

class ucp_privacy_controller implements ucp_privacy_interface
{
	protected $u_action;

	/* @var string phpEx */
	protected $php_ext;

	/* @var \phpbb\request\request_interface */
	protected $request;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\user */
	protected $user;

	/* @var \phpbb\db\driver\driver_interface */
	protected $db;

	/* @var Container */
	protected $phpbb_container;

	/** @var extension owned objects */
	protected $ext_path;

	/**
	* Constructor
	*
	* @param \phpbb\extension\manager				$phpbb_extension_manager
	* @param string									$php_ext
	* @param \phpbb\request\request_interface 		$request
	* @param \phpbb\template\template				$template
	* @param \phpbb\user							$user
	* @param \phpbb\db\driver\driver_interfacer		$db
	* @param Container 								$phpbb_container
	*/
	public function __construct(
		\phpbb\extension\manager $phpbb_extension_manager,
		$php_ext,
		\phpbb\request\request_interface $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\db\driver\driver_interface $db,
		Container $phpbb_container
	)
	{
		$this->php_ext 			= $php_ext;
		$this->request			= $request;
		$this->template 		= $template;
		$this->user 			= $user;
		$this->db 				= $db;
		$this->phpbb_container 	= $phpbb_container;

		$this->ext_path			= $phpbb_extension_manager->get_extension_path('oxpus/dlext', true);
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
		$submit		= $this->request->variable('submit', '');
		$dl_privacy	= $this->request->variable('privacy', '');

		if ($submit && $dl_privacy)
		{
			switch ($dl_privacy)
			{
				case 'tracker':
					$fields = 'df_id, report_title, report_text, report_file_ver, report_date, report_status, report_status_date, report_php, report_db, report_forum';
					$time_fields = ['report_date', 'report_status_date'];
					$user_field = 'report_author_id';
					$table = DL_BUGS_TABLE;
				break;
				case 'comments':
					$fields = 'dl_id, username, comment_time, comment_edit_time, comment_text';
					$time_fields = ['comment_time', 'comment_edit_time'];
					$user_field = 'user_id';
					$table = DL_COMMENTS_TABLE;
				break;
				case 'stats':
					$fields = 'dl_id, username, traffic, direction, user_ip, time_stamp';
					$time_fields = ['time_stamp'];
					$user_field = 'user_id';
					$table = DL_STATS_TABLE;
				break;
				default:
					$table = '';
			}

			if ($table)
			{
				$sql = 'SELECT ' . $fields . ' FROM ' . $table . '
						WHERE ' . $user_field . ' = ' . (int) $this->user->data['user_id'];
				$result = $this->db->sql_query($sql);

				$output_row = [];
				$counter = 0;

				while ($row = $this->db->sql_fetchrow($result))
				{
					$output_row[$counter] = [];
					foreach($row as $field => $value)
					{
						if (in_array($field, $time_fields))
						{
							$output_row[$counter][] .= "'" . str_replace(',', '', date('r', (int) $value)) . "'";
						}
						else
						{
							$output_row[$counter][] .= "'" . str_replace("\n", "<br />", $value) . "'";
						}
					}
					++$counter;
				}

				$this->db->sql_freeresult($result);

				header("Content-type: text/csv");
				header("Content-Disposition: attachment; filename=my_dl_" . $dl_privacy . "_data.csv");
				header("Pragma: no-cache");
				header("Expires: 0");

				$this->template->set_filenames(['body' => 'dl_privacy.html']);

				$this->template->assign_var('FIELDS', $fields);

				foreach($output_row as $key => $data)
				{
					$this->template->assign_block_vars('fields_row', [
						'DATA'	=> implode(', ', $data),
					]);
				}

				$this->template->display('body');

				garbage_collection();
				exit_handler();
			}
		}

		$this->template->assign_vars([
			'S_DL_UCP_PRIVACY'		=> true,
			'S_FORM_ACTION'			=> $this->u_action,
			'U_DL_PRIVACY_BUGS'		=> $this->u_action . '&amp;submit=1&amp;privacy=tracker',
			'U_DL_PRIVACY_COMMENTS'	=> $this->u_action . '&amp;submit=1&amp;privacy=comments',
			'U_DL_PRIVACY_STATS'	=> $this->u_action . '&amp;submit=1&amp;privacy=stats',
		]);
	}
}
