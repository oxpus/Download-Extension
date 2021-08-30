<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext;

class ext extends \phpbb\extension\base
{
	public function is_enableable()
	{
		$config = $this->container->get('config');
		return phpbb_version_compare($config['version'], '3.2.0', '>=');
	}

	public function enable_step($old_state)
	{
		switch ($old_state)
		{
			case '': // Empty means nothing has run yet
				$phpbb_notifications = $this->container->get('notification_manager');
				$phpbb_notifications->enable_notifications('oxpus.dlext.notification.type.dlext');
				$phpbb_notifications->enable_notifications('oxpus.dlext.notification.type.update');
				$phpbb_notifications->enable_notifications('oxpus.dlext.notification.type.approve');
				$phpbb_notifications->enable_notifications('oxpus.dlext.notification.type.comments');
				$phpbb_notifications->enable_notifications('oxpus.dlext.notification.type.capprove');
				$phpbb_notifications->enable_notifications('oxpus.dlext.notification.type.broken');
				$phpbb_notifications->enable_notifications('oxpus.dlext.notification.type.bt_assign');
				$phpbb_notifications->enable_notifications('oxpus.dlext.notification.type.bt_status');

				return 'notifications';
				break;

			default:
				return parent::enable_step($old_state);
				break;
		}
	}

	public function disable_step($old_state)
	{
		switch ($old_state)
		{
			case '': // Empty means nothing has run yet
				$phpbb_notifications = $this->container->get('notification_manager');
				$phpbb_notifications->disable_notifications('oxpus.dlext.notification.type.dlext');
				$phpbb_notifications->disable_notifications('oxpus.dlext.notification.type.update');
				$phpbb_notifications->disable_notifications('oxpus.dlext.notification.type.approve');
				$phpbb_notifications->disable_notifications('oxpus.dlext.notification.type.comments');
				$phpbb_notifications->disable_notifications('oxpus.dlext.notification.type.capprove');
				$phpbb_notifications->disable_notifications('oxpus.dlext.notification.type.broken');
				$phpbb_notifications->disable_notifications('oxpus.dlext.notification.type.bt_assign');
				$phpbb_notifications->disable_notifications('oxpus.dlext.notification.type.bt_status');

				return 'notifications';
				break;

			default:
				return parent::disable_step($old_state);
				break;
		}
	}

	public function purge_step($old_state)
	{
		switch ($old_state)
		{
			case '': // Empty means nothing has run yet
				try
				{
					$phpbb_notifications = $this->container->get('notification_manager');
					$phpbb_notifications->purge_notifications('oxpus.dlext.notification.type.dlext');
					$phpbb_notifications->purge_notifications('oxpus.dlext.notification.type.update');
					$phpbb_notifications->purge_notifications('oxpus.dlext.notification.type.approve');
					$phpbb_notifications->purge_notifications('oxpus.dlext.notification.type.comments');
					$phpbb_notifications->purge_notifications('oxpus.dlext.notification.type.capprove');
					$phpbb_notifications->purge_notifications('oxpus.dlext.notification.type.broken');
					$phpbb_notifications->purge_notifications('oxpus.dlext.notification.type.bt_assign');
					$phpbb_notifications->purge_notifications('oxpus.dlext.notification.type.bt_status');
				}
				catch (\phpbb\notification\exception $e)
				{
					// continue
				}

				return 'notifications';
				break;

			default:
				return parent::purge_step($old_state);
				break;
		}
	}
}
