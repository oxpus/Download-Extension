<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\controller;

class feed
{
	/* phpbb objects */
	protected $root_path;
	protected $php_ext;
	protected $db;
	protected $config;
	protected $helper;
	protected $template;
	protected $user;
	protected $language;

	/* extension owned objects */
	protected $dlext_files;
	protected $dlext_main;
	protected $dlext_status;
	protected $dlext_format;
	protected $dlext_constants;

	/**
	 * Constructor
	 *
	 * @param string								$root_path
	 * @param string								$php_ext
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\config\config					$config
	 * @param \phpbb\controller\helper				$helper
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\user							$user
	 * @param \phpbb\language\language				$language
	 * @param \oxpus\dlext\core\files				$dlext_files
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\status				$dlext_status
	 * @param \oxpus\dlext\core\format				$dlext_format
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 */
	public function __construct(
		$root_path,
		$php_ext,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\language\language $language,
		\oxpus\dlext\core\files $dlext_files,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\status $dlext_status,
		\oxpus\dlext\core\format $dlext_format,
		\oxpus\dlext\core\helpers\constants $dlext_constants
	)
	{
		$this->root_path		= $root_path;
		$this->php_ext			= $php_ext;
		$this->db				= $db;
		$this->config			= $config;
		$this->helper			= $helper;
		$this->template 		= $template;
		$this->user				= $user;
		$this->language			= $language;

		$this->dlext_files		= $dlext_files;
		$this->dlext_main		= $dlext_main;
		$this->dlext_status		= $dlext_status;
		$this->dlext_format		= $dlext_format;
		$this->dlext_constants	= $dlext_constants;
	}

	public function handle()
	{
		$this->dlext_main->dl_handle_active();

		// disable the feed until it is enabled and contains at least one entry
		$display_feed = $this->dlext_constants::DL_FALSE;

		if ($this->config['dl_rss_enable'])
		{
			// Switch user to anonymous to prepare the correct permissions, if wanted
			if (!$this->config['dl_rss_perms'])
			{
				$perm_backup = ['user_backup' => $this->user->data];

				// sql to get the users info
				$sql = 'SELECT *
					FROM ' . USERS_TABLE . '
					WHERE user_id = ' . ANONYMOUS;
				$result	= $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				// reset the current users info to that of the bot
				$this->user->data = array_merge($this->user->data, $row);
				$this->user->data['user_permissions'] = '';
				$this->user->data['session_user_id'] = ANONYMOUS;
				$this->user->data['session_admin'] = 0;
				$this->user->data['is_registered'] = $this->dlext_constants::DL_FALSE;
				$this->user->data['user_perm_from'] = ANONYMOUS;

				unset($row);
			}

			// Get the possible categories
			$access_cats = $this->dlext_main->full_index(0, 0, 0, $this->dlext_constants::DL_AUTH_CHECK_VIEW);

			// Does we have some cats? miau ...
			if (!empty($access_cats))
			{
				$where_cats = ['{cat_perm}' => ['AND', 'IN', $this->db->sql_in_set('cat', $access_cats)]];

				$rss_cats_ary = array_map('intval', explode(',', $this->config['dl_rss_cats_select']));

				switch ($this->config['dl_rss_cats'])
				{
					case $this->dlext_constants::DL_RSS_CATS_SELECTED:
						$where_cats += ['cat' => ['AND', 'IN', $this->db->sql_in_set('cat', $rss_cats_ary)]];
						break;

					case $this->dlext_constants::DL_RSS_CATS_OTHER:
						$where_cats += ['cat' => ['AND', 'NOT IN', $this->db->sql_in_set('cat', $rss_cats_ary, $this->dlext_constants::DL_TRUE)]];
						break;
				}

				$sort_by = ($this->config['dl_rss_select']) ? 'rand()' : 'change_time';
				$order_by = ($this->config['dl_rss_select']) ? '' : 'DESC';
				$sort_ary = [$sort_by => $order_by];

				$fields = ['id', 'cat', 'description', 'desc_uid', 'hack_version', 'add_time', 'change_time', 'long_desc', 'long_desc_uid', 'long_desc_bitfield', 'long_desc_flags'];

				$rss = $this->dlext_constants::DL_TRUE;

				$dl_files = $this->dlext_files->all_files(0, $sort_ary, $where_cats, 0, 0, $fields, $this->config['dl_rss_number']);

				if (!empty($dl_files))
				{
					for ($i = 0; $i < count($dl_files); ++$i)
					{
						$dl_id			= $dl_files[$i]['id'];
						$dl_cat			= $dl_files[$i]['cat'];
						$hack_version	= $dl_files[$i]['hack_version'];
						$last_time		= date('r', $dl_files[$i]['change_time']);

						if ($i == 0)
						{
							$timetmp	= $last_time;
						}

						$description		= $dl_files[$i]['description'];
						$desc_uid			= $dl_files[$i]['desc_uid'];

						$description	= censor_text($description);
						strip_bbcode($description, $desc_uid);
						$description	.= ' ' . $hack_version;

						$long_desc = $this->dlext_format->dl_shorten_string($dl_files[$i]['long_desc'], 'feed', $dl_files[$i]['long_desc_uid'], $dl_files[$i]['long_desc_bitfield'], $dl_files[$i]['long_desc_flags']);

						if ($this->config['dl_rss_new_update'])
						{
							$mini_status = $this->dlext_status->mini_status_file($dl_cat, $dl_id, $rss);
						}
						else
						{
							$mini_status = '';
						}

						$this->template->assign_block_vars('dl_rss_feed', [
							'DL_RSS_TITLE'	=> $description,
							'DL_RSS_MINI_S'	=> $mini_status,
							'DL_RSS_DESC'	=> $long_desc,
							'DL_RSS_TIME'	=> $last_time,

							'U_DL_RSS'		=> generate_board_url($this->dlext_constants::DL_TRUE) . $this->helper->route('oxpus_dlext_details', ['df_id' => $dl_id]),
						]);
					}

					$display_feed = $this->dlext_constants::DL_TRUE;
				}
			}

			// Restore the user data to the original one to finish the feed here correctly
			if (!$this->config['dl_rss_perms'])
			{
				$this->user->data = $perm_backup['user_backup'];
				unset($perm_backup);
			}
		}

		if (!$this->config['dl_rss_enable'] || !$display_feed)
		{
			switch ($this->config['dl_rss_off_action'])
			{
				case $this->dlext_constants::DL_RSS_ACTION_R_DLX:
					redirect($this->helper->route('oxpus_dlext_index'));
					break;

				case $this->dlext_constants::DL_RSS_ACTION_R_IDX:
					redirect(append_sid($this->root_path . 'index.' . $this->php_ext));
					break;

				default:
					trigger_error($this->config['dl_rss_off_text']);
			}
		}

		$this->template->assign_vars([
			'DL_SITENAME'			=> $this->config['sitename'],
			'DL_BOARD_URL'			=> generate_board_url() . '/',
			'DL_RSS_TIME_TMP'   	=> $timetmp,
			'DL_SITE_DESCRIPTION'	=> $this->config['site_desc'],
			'DL_RSS_LANG'			=> $this->user->data['user_lang'],

			'U_DL_RSS'				=> generate_board_url($this->dlext_constants::DL_TRUE) . $this->helper->route('oxpus_dlext_feed'),
		]);

		return $this->helper->render('@oxpus_dlext/helpers/dl_rss.xml', $this->language->lang('DL_FEED'));
	}
}
