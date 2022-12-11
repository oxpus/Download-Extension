<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\notification;

/**
 * Download Extension notifications class
 * This class handles notifications for new downloads
 *
 * @package notifications
 */
class update extends \phpbb\notification\type\base
{
	/**
	 * Get notification type name
	 *
	 * @return string
	 */
	public function get_type()
	{
		return 'oxpus.dlext.notification.type.update';
	}

	/**
	 * Notification option data (for outputting to the user)
	 *
	 * @var bool|array False if the service should use it's default data
	 * 					Array of data (including keys 'id', 'lang', and 'group')
	 */
	public static $notification_option = [
		'lang'	=> 'DL_NOTIFY_TYPE_UPDATE',
		'group'	=> 'DL_NOTIFICATIONS',
	];

	/** @var \phpbb\user_loader */
	protected $user_loader;

	/** @var \phpbb\controller\helper */
	protected $helper;

	public function set_user_loader(\phpbb\user_loader $user_loader)
	{
		$this->user_loader = $user_loader;
	}

	public function set_helper(\phpbb\controller\helper $helper)
	{
		$this->helper	= $helper;
	}

	/**
	 * Is this type available to the current user (defines whether or not it will be shown in the UCP Edit notification options)
	 *
	 * @return bool True/False whether or not this is available to the user
	 */
	public function is_available()
	{
		return true;
	}

	/**
	 * Get the id of the notification
	 *
	 * @param array $data The data for the updated rules
	 * @return int Id of the notification
	 */
	public static function get_item_id($data)
	{
		return $data['df_id'];
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
		unset($data);
		return 0;
	}

	/**
	 * Find the users who will receive notifications
	 *
	 * @param array $data The type specific data for the updated rules
	 * @param array $options Options for finding users for notification
	 * @return array
	 */
	public function find_users_for_notification($data, $options = [])
	{
		$this->user_loader->load_users($data['user_ids']);
		return $this->check_user_notification_options($data['user_ids'], $options);
	}


	/**
	 * Get the user's avatar
	 */
	public function get_avatar()
	{
		return 0;
	}
	/**
	 * Get the HTML formatted title of this notification
	 *
	 * @return string
	 */
	public function get_title()
	{
		return $this->language->lang('DL_NOTIFY_UPDATE', $this->get_data('description'));
	}

	/**
	 * Get the HTML formatted reference of the notification
	 *
	 * @return string
	 */
	public function get_reference()
	{
		return '';
	}

	/**
	 * Get email template
	 *
	 * @return string|bool
	 */
	public function get_email_template()
	{
		return '@oxpus_dlext/downloads_change_notify';
	}
	/**
	 * Get email template variables
	 *
	 * @return array
	 */
	public function get_email_template_variables()
	{
		return [
			'DOWNLOAD'		=> strip_tags(html_entity_decode($this->get_data('description'), ENT_QUOTES)),
			'DESCRIPTION'	=> strip_tags(html_entity_decode($this->get_data('long_desc'), ENT_QUOTES)),
			'CATEGORY'		=> strip_tags(html_entity_decode($this->get_data('cat_name'), ENT_QUOTES)),
			'U_CATEGORY'	=> generate_board_url(true) . $this->helper->route('oxpus_dlext_index', ['cat_df_id' => $this->get_data('df_id')]),
		];
	}

	/**
	 * Get the url to this item
	 *
	 * @return string URL
	 */
	public function get_url()
	{
		return $this->helper->route('oxpus_dlext_index', ['cat_df_id' => $this->get_data('df_id')]);
	}

	/**
	 * Users needed to query before this notification can be displayed
	 *
	 * @return array Array of user_ids
	 */
	public function users_to_query()
	{
		return [];
	}

	/**
	 * Function for preparing the data for insertion in an SQL query
	 * (The service handles insertion)
	 *
	 * @param array $data The data for the new download
	 * @param array $pre_create_data Data from pre_create_insert_array()
	 *
	 */
	public function create_insert_array($data, $pre_create_data = [])
	{
		$this->set_data('user_ids', $data['user_ids']);
		$this->set_data('df_id', $data['df_id']);
		$this->set_data('cat_name', $data['cat_name']);
		$this->set_data('long_desc', $data['long_desc']);
		$this->set_data('description', $data['description']);
		parent::create_insert_array($data, $pre_create_data);
	}
}
