<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\notification;

/**
* Download Extension notifications class
* This class handles notifications for new and updates downloads
*
* @package notifications
*/
class dlext extends \phpbb\notification\type\base
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/**
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\language\language          $language
	 * @param \phpbb\user                       $user
	 * @param \phpbb\auth\auth                  $auth
	 * @param string                            $phpbb_root_path
	 * @param string                            $php_ext
	 * @param string                            $user_notifications_table
	 * @param \phpbb\controller\helper          $helper
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\language\language $language, \phpbb\user $user, \phpbb\auth\auth $auth, $phpbb_root_path, $php_ext, $user_notifications_table, \phpbb\controller\helper $helper)
	{
		$this->db		= $db;
		$this->language	= $language;
		$this->helper	= $helper;

		parent::__construct($db, $language, $user, $auth, $phpbb_root_path, $php_ext, $user_notifications_table);
	}

	/**
	* Get notification type name
	*
	* @return string
	*/
	public function get_type()
	{
		return 'oxpus.dlext.notification.type.dlext';
	}

	/**
	* Is this type available to the current user (defines whether or not it will be shown in the UCP Edit notification options)
	*
	* @return bool True/False whether or not this is available to the user
	*/
	public function is_available()
	{
		return false;
	}

	/**
	* Get the id of the notification
	*
	* @param array $data The data for the updated rules
	* @return int Id of the notification
	*/
	public static function get_item_id($data)
	{
		return $data['notification_id'];
	}

	/**
	* Get the id of the parent
	*
	* @param array $data The data for the updated rules
	* @return int Id of the parent
	*/
	public static function get_item_parent_id($data)
	{
		// No parent
		return 0;
	}

	/**
	* Find the users who will receive notifications
	*
	* @param array $data The type specific data for the updated rules
	* @param array $options Options for finding users for notification
	* @return array
	*/
	public function find_users_for_notification($data, $options = array())
	{
		// Grab all registered users (excluding bots and guests)
		$sql = 'SELECT user_id
			FROM ' . USERS_TABLE . '
			WHERE user_new_download <> 0
				AND user_dl_note_type = 2';
		$result = $this->db->sql_query($sql);

		$users = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$users[$row['user_id']] = array('');
		}
		$this->db->sql_freeresult($result);

		return $users;
	}

	/**
	* Users needed to query before this notification can be displayed
	*
	* @return array Array of user_ids
	*/
	public function users_to_query()
	{
		return array();
	}

	/**
	* Get the HTML formatted title of this notification
	*
	* @return string
	*/
	public function get_title()
	{
		return $this->language->lang('NEW_DOWNLOAD_NOTIFICATION');
	}

	/**
	* Get the url to this item
	*
	* @return string URL
	*/
	public function get_url()
	{
		return $this->helper->route('oxpus_dlext_controller', array('view' => 'latest'));
	}

	/**
	* Get email template
	*
	* @return string|bool
	*/
	public function get_email_template()
	{
		return false;
	}

	/**
	* Get email template variables
	*
	* @return array
	*/
	public function get_email_template_variables()
	{
		return array();
	}

	/**
	* Function for preparing the data for insertion in an SQL query
	* (The service handles insertion)
	*
	* @param array $data The data for the updated or new download
	* @param array $pre_create_data Data from pre_create_insert_array()
	*
	* @return array Array of data ready to be inserted into the database
	*/
	public function create_insert_array($data, $pre_create_data = array())
	{
		return parent::create_insert_array($data, $pre_create_data);
	}
}
