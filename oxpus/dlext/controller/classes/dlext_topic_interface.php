<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\controller\classes;

/**
 * Interface for acp_banlist_controller
 *
 */
interface dlext_topic_interface
{
	/**
	 * Generate the download topic for new downloads
	 * 
	 * @param int $dl_id download id for which the topic should be posted
	 * @param bool $force true will create a topic even a topic id was found on the download dataset, otherwise the function will not create the topic
	 * @return void
	 * @access public
	*/
	public function gen_dl_topic($dl_id, $force = false);

	/**
	 * Drop or close topics for deleted downloads
	 * 
	 * @param array $topic_ids topic_ids from the deleted downloads
	 * @param string $topic_drop_mode delete or close topics
	 * @param array $dl_ids download ids which should be deleted
	 * @return void
	 * @access public
	*/
	public function delete_topic($topic_ids, $topic_drop_mode = 'drop', $dl_ids = []);

	/**
	 * Update existing topic for updated download
	 * 
	 * @param array $topic_id topic_id which need some updates
	 * @param int $dl_id download id for which the topic needs to be updated
	 * @param string $topic_drop_mode needed to close topics on deleted downloads
	 * @return void
	 * @access public
	*/
	public function update_topic($topic_id, $dl_id, $topic_drop_mode = '');

	/**
	 * _change_auth to switch to an other user if needed as topic poster
	 * Added by Mickroz for changing permissions
	 * code by poppertom69 & RMcGirr83
	 * 
	 * @param int $user_id to switch to the user data
	 * @param string $mode replace or restore user data
	 * @param array $bkup_data backup array form the original user
	 * @return array new or original user data
	 * @access public
	*/
	public function _change_auth($user_id, $mode = 'replace', $bkup_data = false);
}
