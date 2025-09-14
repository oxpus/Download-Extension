<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\controller\ucp;

class ucp_privacy_controller implements ucp_privacy_interface
{
	/* phpbb objects */
	protected $request;
	protected $template;
	protected $user;
	protected $db;

	/* extension owned objects */
	public $u_action;
	protected $ext_path;

	protected $dlext_physical;
	protected $dlext_constants;

	protected $dlext_table_dl_tracker;
	protected $dlext_table_dl_comments;
	protected $dlext_table_dl_stats;

	/**
	 * Constructor
	 *
	 * @param \phpbb\request\request 				$request
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\user							$user
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \oxpus\dlext\core\physical			$dlext_physical
	 * @param \oxpus\dlext\core\helpers\constants 	$dlext_constants
	 * @param string								$dlext_table_dl_tracker
	 * @param string								$dlext_table_dl_comments
	 * @param string								$dlext_table_dl_stats
	 */
	public function __construct(
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\db\driver\driver_interface $db,
		\oxpus\dlext\core\physical $dlext_physical,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		$dlext_table_dl_tracker,
		$dlext_table_dl_comments,
		$dlext_table_dl_stats
	)
	{
		$this->request			= $request;
		$this->template 		= $template;
		$this->user 			= $user;
		$this->db 				= $db;

		$this->dlext_physical	= $dlext_physical;
		$this->dlext_constants	= $dlext_constants;

		$this->dlext_table_dl_tracker	= $dlext_table_dl_tracker;
		$this->dlext_table_dl_comments	= $dlext_table_dl_comments;
		$this->dlext_table_dl_stats		= $dlext_table_dl_stats;
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
					$table = $this->dlext_table_dl_tracker;
					break;
				case 'comments':
					$fields = 'dl_id, username, comment_time, comment_edit_time, comment_text';
					$time_fields = ['comment_time', 'comment_edit_time'];
					$user_field = 'user_id';
					$table = $this->dlext_table_dl_comments;
					break;
				case 'stats':
					$fields = 'dl_id, username, traffic, direction, user_ip, time_stamp';
					$time_fields = ['time_stamp'];
					$user_field = 'user_id';
					$table = $this->dlext_table_dl_stats;
					break;
				default:
					$table = '';
			}

			if ($table)
			{
				$sql = 'SELECT ' . $fields . ' FROM ' . $table . '
						WHERE ' . $user_field . ' = ' . (int) $this->user->data['user_id'];
				$result = $this->db->sql_query($sql);

				$counter = 0;
				$output_row = [];

				while ($row = $this->db->sql_fetchrow($result))
				{
					$output_row[$counter] = [];
					foreach ($row as $field => $value)
					{
						if (in_array($field, $time_fields))
						{
							$output_row[$counter][] .= "'" . str_replace(',', '', date('r', (int) $value)) . "'";
						}
						else
						{
							$output_row[$counter][] .= "'" . str_replace("\n", '<br>', $value) . "'";
						}
					}
					++$counter;
				}

				$this->db->sql_freeresult($result);

				$file_stream = $fields . "\n";

				foreach ($output_row as $data)
				{
					$file_stream .= implode(', ', $data) . "\n";
				}

				$dl_file_data = [
					'physical_file'		=> $file_stream,
					'real_filename'		=> 'my_dl_' . $dl_privacy . '_data_' . date(DATE_RFC3339) . '.csv',
					'mimetype'			=> 'application/octetstream',
					'filesize'			=> sprintf('%u', strlen($file_stream)),
					'filetime'			=> time(),
					'filestream'		=> $this->dlext_constants::DL_TRUE,
				];

				$this->dlext_physical->send_file_to_browser($dl_file_data);
			}
		}

		$this->template->assign_vars([
			'S_DL_UCP_PRIVACY'		=> $this->dlext_constants::DL_TRUE,
			'S_SL_FORM_ACTION'		=> $this->u_action,
			'U_DL_PRIVACY_BUGS'		=> $this->u_action . '&amp;submit=1&amp;privacy=tracker',
			'U_DL_PRIVACY_COMMENTS'	=> $this->u_action . '&amp;submit=1&amp;privacy=comments',
			'U_DL_PRIVACY_STATS'	=> $this->u_action . '&amp;submit=1&amp;privacy=stats',
		]);
	}
}
