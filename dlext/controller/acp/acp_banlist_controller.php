<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2021 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\controller\acp;

/**
* @package acp
*/
class acp_banlist_controller implements acp_banlist_interface
{
	/* phpbb objects */
	protected $u_action;
	protected $db;
	protected $user;
	protected $log;
	protected $language;
	protected $request;
	protected $template;

	/* extension owned objects */
	protected $dlext_constants;

	protected $dlext_table_dl_banlist;

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language				$language
	 * @param \phpbb\request\request 				$request
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\log\log_interface 				$log
	 * @param \phpbb\user							$user
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param string								$dlext_table_dl_banlist
	 */
	public function __construct(
		\phpbb\language\language $language,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\log\log_interface $log,
		\phpbb\user $user,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		$dlext_table_dl_banlist
	)
	{
		$this->db						= $db;
		$this->log						= $log;
		$this->user						= $user;
		$this->language					= $language;
		$this->request					= $request;
		$this->template					= $template;

		$this->dlext_constants			= $dlext_constants;

		$this->dlext_table_dl_banlist	= $dlext_table_dl_banlist;
	}

	public function set_action($u_action)
	{
		$this->u_action = $u_action;
	}

	public function handle()
	{
		$action				= $this->request->variable('action', '');
		$cancel				= $this->request->variable('cancel', '');
		$tmp_m1				= $this->request->variable('edit_banlist', '');
		$tmp_m2				= $this->request->variable('delete_banlist', '');
		$user_ip			= $this->request->variable('user_ip', '');
		$username			= $this->request->variable('username', '', $this->dlext_constants::DL_TRUE);
		$ban_id				= $this->request->variable('ban_id', 0);
		$guests				= $this->request->variable('guests', 0);
		$user_id			= $this->request->variable('user_id', 0);

		if ($cancel)
		{
			$action = '';
		}

		$action = ($tmp_m1) ? 'edit' : $action;
		$action = ($tmp_m2) ? 'delete' : $action;

		if ($action == 'add')
		{
			$user_ip = ($user_ip != '') ? $user_ip : '';

			if (!check_form_key('dl_adm_ban'))
			{
				trigger_error('FORM_INVALID', E_USER_WARNING);
			}

			if ($ban_id)
			{
				$sql = 'UPDATE ' . $this->dlext_table_dl_banlist . ' SET ' . $this->db->sql_build_array('UPDATE', [
					'user_id'		=> $user_id,
					'user_ip'		=> $user_ip,
					'username'		=> $username,
					'guests'		=> $guests]) . ' WHERE ban_id = ' . (int) $ban_id;

				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_BAN_EDIT', false, [$this->user->data['user_id'] . ' ~ ' . $username, $user_ip, $guests]);
			}
			else
			{
				$sql = 'INSERT INTO ' . $this->dlext_table_dl_banlist . ' ' . $this->db->sql_build_array('INSERT', [
					'user_id'		=> $user_id,
					'user_ip'		=> $user_ip,
					'username'		=> $username,
					'guests'		=> $guests]);

				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_BAN_ADD', false, [$user_id . ' ~ ' . $username, $user_ip, $guests]);
			}

			$this->db->sql_query($sql);

			$action = '';
		}
		else if ($action == 'delete')
		{
			$ban_id_ary = $this->request->variable('ban_id', [0]);

			if (confirm_box($this->dlext_constants::DL_TRUE))
			{
				$sql_ext_in = [];

				for ($i = 0; $i < count($ban_id_ary); ++$i)
				{
					$sql_ext_in[] = intval($ban_id_ary[$i]);
				}

				if (!empty($sql_ext_in))
				{
					$sql = 'DELETE FROM ' . $this->dlext_table_dl_banlist . '
						WHERE ' . $this->db->sql_in_set('ban_id', $sql_ext_in);
					$this->db->sql_query($sql);

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_BAN_DEL', false, [implode(', ', $sql_ext_in)]);

					$message = $this->language->lang('DL_BANLIST_UPDATED') . adm_back_link($this->u_action);

					trigger_error($message);
				}
			}
			else
			{
				$s_hidden_fields = ['action' => 'delete'];

				for ($i = 0; $i < count($ban_id_ary); ++$i)
				{
					$s_hidden_fields += ['ban_id[' . $i . ']' => intval($ban_id_ary[$i])];
				}

				confirm_box($this->dlext_constants::DL_FALSE, 'DL_CONFIRM_DEL_BAN_VALUES', build_hidden_fields($s_hidden_fields), '@oxpus_dlext/dl_confirm_body.html');
			}
		}

		if ($action == '' || $action == 'edit')
		{
			$sql = 'SELECT b.*, u.username AS user2 FROM ' . $this->dlext_table_dl_banlist . ' b
				LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = b.user_id
				ORDER BY ban_id';
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$ban_id = $row['ban_id'];
				$user2 = $row['user2'];
				$user_id = $row['user_id'];
				$user_ip = $row['user_ip'];
				$username = $row['username'];
				$guests = ($row['guests']) ? $this->language->lang('YES') : $this->language->lang('NO');

				$this->template->assign_block_vars('dl_banlist_row', [
					'DL_BAN_ID'		=> $ban_id,
					'DL_USER_ID'	=> $user_id . (($user2) ? ' &raquo; ' . $user2 : ''),
					'DL_USER_IP'	=> ($user_ip != '') ? $user_ip : '',
					'DL_USERNAME'	=> $username,
					'DL_GUESTS'		=> $guests,
				]);
			}

			$s_ban_list = ($this->db->sql_affectedrows()) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;

			$this->db->sql_freeresult($result);

			$ban_id_ary = $this->request->variable('ban_id', [0]);

			$banlist_id = (isset($ban_id_ary[0])) ? intval($ban_id_ary[0]) : 0;

			$s_hidden_fields = ['action' => 'add'];

			if ($action == 'edit' && $banlist_id)
			{
				$sql = 'SELECT * FROM ' . $this->dlext_table_dl_banlist . '
					WHERE ban_id = ' . (int) $banlist_id;
				$result = $this->db->sql_query($sql);

				while ($row = $this->db->sql_fetchrow($result))
				{
					$user_id	= $row['user_id'];
					$user_ip	= ($row['user_ip'] != '') ? $row['user_ip'] : '';
					$username	= $row['username'];
					$guests		= $row['guests'];

					$s_hidden_fields += ['ban_id' => $row['ban_id']];
				}
				$this->db->sql_freeresult($result);
			}
			else
			{
				$user_id	= '';
				$user_ip	= '';
				$username	= '';
				$guests		= '';
			}

			add_form_key('dl_adm_ban');

			$this->template->assign_vars([
				'DL_BANLIST_ACTION'		=> ($action == 'edit') ? $this->language->lang('EDIT') : $this->language->lang('ADD'),

				'DL_USER_ID'			=> $user_id,
				'DL_USER_IP'			=> $user_ip,
				'DL_USERNAME'			=> $username,
				'DL_CHECKED'			=> ($guests) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,

				'S_DL_BAN_LIST'			=> $s_ban_list,
				'S_DL_HIDDEN_FIELDS'	=> build_hidden_fields($s_hidden_fields),
				'S_DL_DOWNLOADS_ACTION'	=> $this->u_action,

				'U_DL_BACK'				=> ($action) ? $this->u_action : '',
			]);
		}
	}
}
