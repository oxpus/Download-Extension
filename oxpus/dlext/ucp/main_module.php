<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\ucp;

/**
 * @package ucp
 */
class main_module
{
	public $u_action;

	public function main($id, $mode)
	{
		global $phpbb_container;

		$this->tpl_name = '@oxpus_dlext/ucp/dl_user_config_body';

		/*
		* set the current module title
		*/
		switch ($mode)
		{
			case 'config':
				$this->page_title = 'DL_CONFIG';
				break;
			case 'favorite':
				$this->page_title = 'DL_FAVORITE';
				break;
			case 'dl_privacy':
				$this->page_title = 'DL_PRIVACY';
				break;
		}

		// Mount the right ucp container
		$dlext_container = $phpbb_container->get('oxpus.dlext.' . $mode . '_controller');

		// Set the action path
		$dlext_container->set_action($this->u_action);

		// Start the handling in the mounted container
		$dlext_container->handle();
	}
}
