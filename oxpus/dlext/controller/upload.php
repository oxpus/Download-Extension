<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\controller;

class upload
{
	/* phpbb objects */
	protected $config;
	protected $helper;
	protected $request;
	protected $language;

	/* extension owned objects */
	protected $dlext_auth;
	protected $dlext_download;
	protected $dlext_main;
	protected $dlext_physical;
	protected $dlext_footer;
	protected $dlext_fields;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config					$config
	 * @param \phpbb\controller\helper				$helper
	 * @param \phpbb\request\request 				$request
	 * @param \phpbb\language\language				$language
	 * @param \oxpus\dlext\core\auth				$dlext_auth
	 * @param \oxpus\dlext\core\download			$dlext_download
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\physical			$dlext_physical
	 * @param \oxpus\dlext\core\helpers\footer		$dlext_footer
	 */
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\request\request $request,
		\phpbb\language\language $language,
		\oxpus\dlext\core\auth $dlext_auth,
		\oxpus\dlext\core\download $dlext_download,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\physical $dlext_physical,
		\oxpus\dlext\core\helpers\footer $dlext_footer
	)
	{
		$this->config 					= $config;
		$this->helper 					= $helper;
		$this->request					= $request;
		$this->language					= $language;

		$this->dlext_auth				= $dlext_auth;
		$this->dlext_download			= $dlext_download;
		$this->dlext_main				= $dlext_main;
		$this->dlext_physical			= $dlext_physical;
		$this->dlext_footer				= $dlext_footer;
	}

	public function handle()
	{
		$this->dlext_main->dl_handle_active();

		$submit		= $this->request->variable('submit', '');
		$cat_id		= $this->request->variable('cat_id', 0);

		$index			= $this->dlext_main->full_index();
		$cat_auth		= $this->dlext_auth->dl_cat_auth($cat_id);
		$physical_size	= $this->dlext_physical->read_dl_sizes();

		if ($physical_size >= $this->config['dl_physical_quota'])
		{
			trigger_error('DL_BLUE_EXPLAIN');
		}

		if (($this->config['dl_stop_uploads'] && !$this->dlext_auth->user_admin()) || empty($index) || (!$cat_auth['auth_up'] && !$index[$cat_id]['auth_up'] && !$this->dlext_auth->user_admin()))
		{
			trigger_error('DL_NO_PERMISSION');
		}

		if ($submit)
		{
			$this->dlext_download->dl_submit_download('upload');
		}

		$this->dlext_download->dl_edit_download('upload');

		/*
		* include the mod footer
		*/
		$this->dlext_footer->set_parameter('upload', $cat_id, 0, $index);
		$this->dlext_footer->handle();

		/*
		* generate page
		*/
		return $this->helper->render('@oxpus_dlext/dl_edit_body.html', $this->language->lang('DL_UPLOAD'));
	}
}
