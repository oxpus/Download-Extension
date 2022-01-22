<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\controller\acp;

/**
 * @package acp
 */
class acp_ext_blacklist_controller implements acp_ext_blacklist_interface
{
	/* phpbb objects */
	protected $db;
	protected $user;
	protected $log;
	protected $language;
	protected $request;
	protected $template;
	protected $cache;

	/* extension owned objects */
	public $u_action;

	protected $dlext_main;
	protected $dlext_constants;

	protected $dlext_table_dl_ext_blacklist;

	/**
	 * Constructor
	 *
	 * @param \phpbb\cache\service					$cache
	 * @param \phpbb\language\language				$language
	 * @param \phpbb\request\request 				$request
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\log\log_interface 				$log
	 * @param \phpbb\user							$user
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param string								$dlext_table_dl_ext_blacklist
	 */
	public function __construct(
		\phpbb\cache\service $cache,
		\phpbb\language\language $language,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\log\log_interface $log,
		\phpbb\user $user,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		$dlext_table_dl_ext_blacklist
	)
	{
		$this->cache					= $cache;
		$this->db						= $db;
		$this->log						= $log;
		$this->user						= $user;

		$this->language					= $language;
		$this->request					= $request;
		$this->template					= $template;

		$this->dlext_main				= $dlext_main;
		$this->dlext_constants			= $dlext_constants;

		$this->dlext_table_dl_ext_blacklist	= $dlext_table_dl_ext_blacklist;
	}

	public function set_action($u_action)
	{
		$this->u_action = $u_action;
	}

	public function handle()
	{
		$action				= $this->request->variable('action', '');
		$cancel				= $this->request->variable('cancel', '');
		$extension_ary		= $this->request->variable('extension', [''], $this->dlext_constants::DL_TRUE);

		$index = $this->dlext_main->full_index();

		if (empty($index))
		{
			$this->u_action = str_replace('mode=ext_blacklist', 'mode=assistant', $this->u_action);
			redirect($this->u_action);
		}

		unset($index);

		if ($cancel)
		{
			$action = '';
		}

		if ($action == 'add')
		{
			if (!check_form_key('dl_adm_ext'))
			{
				trigger_error('FORM_INVALID', E_USER_WARNING);
			}

			$extension = $this->request->variable('extension', '', $this->dlext_constants::DL_TRUE);

			if ($extension)
			{
				$sql = 'SELECT * FROM ' . $this->dlext_table_dl_ext_blacklist . "
					WHERE extention = '" . $this->db->sql_escape($extension) . "'";
				$result = $this->db->sql_query($sql);
				$ext_exist = $this->db->sql_affectedrows();
				$this->db->sql_freeresult($result);

				if (!$ext_exist)
				{
					$sql = 'INSERT INTO ' . $this->dlext_table_dl_ext_blacklist . ' ' . $this->db->sql_build_array('INSERT', [
						'extention' => $extension
					]);
					$this->db->sql_query($sql);

					// Purge the blacklist cache
					$this->cache->destroy('_dlext_black');

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_EXT_ADD', false, [$extension]);
				}
			}

			$action = '';
		}

		if ($action == 'delete')
		{
			if (confirm_box($this->dlext_constants::DL_TRUE))
			{
				$sql_ext_in = [];

				for ($i = 0; $i < count($extension_ary); ++$i)
				{
					$sql_ext_in[] = $extension_ary[$i];
				}

				if (!empty($sql_ext_in))
				{
					$sql = 'DELETE FROM ' . $this->dlext_table_dl_ext_blacklist . '
						WHERE ' . $this->db->sql_in_set('extention', $sql_ext_in);
					$this->db->sql_query($sql);

					// Purge the blacklist cache
					$this->cache->destroy('_dlext_black');

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_EXT_DEL', false, [implode(', ', $sql_ext_in)]);
				}

				$message = ((count($extension_ary) == 1) ? $this->language->lang('DL_EXTENSION_REMOVED') : $this->language->lang('DL_EXTENSIONS_REMOVED')) . adm_back_link($this->u_action);

				trigger_error($message);
			}
			else
			{
				$s_hidden_fields = ['action' => 'delete'];

				for ($i = 0; $i < count($extension_ary); ++$i)
				{
					$s_hidden_fields += ['extension[' . $i . ']' => $extension_ary[$i]];
				}

				$confirm_title = (count($extension_ary) == 1) ? $this->language->lang('DL_CONFIRM_DELETE_EXTENSION', $extension_ary[0]) : $this->language->lang('DL_CONFIRM_DELETE_EXTENSIONS', implode(', ', $extension_ary));

				confirm_box($this->dlext_constants::DL_FALSE, $confirm_title, build_hidden_fields($s_hidden_fields), '@oxpus_dlext/dl_confirm_body.html');
			}
		}

		if ($action == '')
		{
			$sql = 'SELECT extention FROM ' . $this->dlext_table_dl_ext_blacklist . '
				ORDER BY extention';
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->template->assign_block_vars('dl_extension_row', [
					'DL_EXTENSION' => $row['extention'],
				]);
			}

			$ext_yes = ($this->db->sql_affectedrows()) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;

			$this->db->sql_freeresult($result);

			add_form_key('dl_adm_ext');

			$this->template->assign_vars([
				'S_DL_EXT_YES'			=> $ext_yes,
				'S_DL_DOWNLOADS_ACTION'	=> $this->u_action,
			]);
		}
	}
}
