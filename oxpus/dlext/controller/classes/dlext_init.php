<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\controller\classes;

use Symfony\Component\DependencyInjection\Container;

class dlext_init implements dlext_init_interface
{
	/* @var string phpBB root path */
	protected $root_path;

	/* @var string phpEx */
	protected $php_ext;

	/* @var \phpbb\db\driver\driver_interface */
	protected $db;

	/* @var \phpbb\config\config */
	protected $config;

	protected $cat_counts;
	protected $dl_index;

	protected $dlext_cache;

	/**
	* Constructor
	*
	* @param string									$root_path
	* @param string									$php_ext
	* @param Container 								$phpbb_container
	* @param \phpbb\extension\manager				$phpbb_extension_manager

	* @param \phpbb\db\driver\driver_interfacer		$db
	* @param \phpbb\config\config					$config
	*/
	public function __construct(
		$root_path,
		$php_ext,
		Container $phpbb_container,
		\phpbb\extension\manager $phpbb_extension_manager,

		\phpbb\db\driver\driver_interface $db,
		\phpbb\config\config $config,
		$dlext_cache
		)
	{
		$this->root_path	= $root_path;
		$this->php_ext 		= $php_ext;
		$this->db 			= $db;
		$this->config 		= $config;

		$this->ext_path		= $phpbb_extension_manager->get_extension_path('oxpus/dlext', true);

		$this->dlext_cache	= $dlext_cache;
	}

	public function root_path()
	{
		return $this->root_path;
	}

	public function php_ext()
	{
		return '.' . $this->php_ext;
	}

	public function dl_file_p()
	{
		$dl_file_p = array();

		$sql = 'SELECT id, cat, file_name, real_file, file_size, extern, free, file_traffic, klicks FROM ' . DOWNLOADS_TABLE . '
				WHERE approve = ' . true;
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$dl_file_p[$row['id']] = $row;
		}
		$this->db->sql_freeresult($result);

		return $dl_file_p;
	}

	public function dl_file_icon()
	{
		return $this->dlext_cache->obtain_dl_files(intval($this->config['dl_new_time']), intval($this->config['dl_edit_time']));
	}
}
