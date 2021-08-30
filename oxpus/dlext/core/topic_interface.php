<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\core;

/**
 * Interface for topic_controller
 *
 */
interface topic_interface
{
	/**
	 * Generate or update the download topics for downloads
	 *
	 * @param string $mode the post mode or edit mode needs to be given here
	 * @param int $dl_id download id for which the topic should be posted
	 * @param string $topic_drop_mode needed to close topics on deleted downloads
	 * @return void
	 * @access public
	 */
	public function gen_dl_topic($mode, $dl_id, $topic_drop_mode = '');

	/**
	 * Drop or close topics for deleted downloads
	 *
	 * @param array $topic_ids topic_ids from the deleted downloads
	 * @param string $topic_drop_mode delete or close topics
	 * @param array $dl_ids download ids which should be deleted
	 * @return void
	 * @access public
	 */
	public function delete_dl_topic($topic_ids, $topic_drop_mode = 'drop', $dl_ids = []);

	/**
	 * _change_auth to switch to an other user if needed as topic poster
	 * Added by Mickroz for changing permissions
	 * code by poppertom69 & RMcGirr83
	 *
	 * param int $user_id to switch to the user data
	 * param string $mode replace or restore user data
	 * param array $bkup_data backup array form the original user
	 * return array new or original user data
	 * @access public
	 */
	public function _change_auth($user_id, $mode = 'replace', $bkup_data = false);
}
