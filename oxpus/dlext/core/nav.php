<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\core;

class nav implements nav_interface
{
	/* phpbb objects */
	protected $language;

	/* extension owned objects */
	protected $dlext_auth;

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language		$language
	 * @param \oxpus\dlext\core\auth		$dlext_auth
	 */
	public function __construct(
		\phpbb\language\language $language,
		\oxpus\dlext\core\auth $dlext_auth
	)
	{
		$this->language		= $language;

		$this->dlext_auth	= $dlext_auth;
	}

	public function nav($parent, &$tmp_nav = [])
	{
		$dl_index = $this->dlext_auth->dl_index();

		if (empty($dl_index))
		{
			return;
		}

		$cat_id = (isset($dl_index[$parent]['id'])) ? $dl_index[$parent]['id'] : 0;

		if (!$cat_id)
		{
			return;
		}

		$dl_auth	= $this->dlext_auth->dl_auth();
		$user_admin	= $this->dlext_auth->user_admin();

		if (((isset($dl_index[$cat_id]['auth_view']) && $dl_index[$cat_id]['auth_view']) || (isset($dl_auth[$cat_id]['auth_view']) && $dl_auth[$cat_id]['auth_view']) || $user_admin))
		{
			$tmp_nav[] = [
				'name'		=> $dl_index[$cat_id]['cat_name_nav'],
				'cat_id'	=> $cat_id,
				'parent_id' => $dl_index[$cat_id]['parent'],
			];
		}

		if (isset($dl_index[$parent]['parent']) && $dl_index[$parent]['parent'] != 0)
		{
			$this->nav($dl_index[$parent]['parent'], $tmp_nav);
		}
	}
}
