<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\phpbb\classes;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class dl_mod
{
	/*
	* init phpBB variables
	*/
	private $phpbb_root_path;
	private $php_ext;
	private $path;

	public $language;

	public function __construct($phpbb_root_path, $php_ext = 'php', $ext_path)
	{
		$this->phpbb_root_path	= $phpbb_root_path;
		$this->php_ext			= '.' . $php_ext;
		$this->path				= $ext_path . 'phpbb/classes/class_';
	}

	public function register()
	{
		spl_autoload_register(array($this, 'dl_class'));
	}

	public function unregister()
	{
		spl_autoload_unregister(array($this, 'dl_class'));
	}

	public function dl_class($class)
	{
		$class = str_replace("oxpus\dlext\phpbb\classes\\", '', $class);

		if (!class_exists($class))
		{
			$path = file_exists($this->path . $class . $this->php_ext);

			if ($path)
			{
				require_once($this->path . $class . $this->php_ext);
			}
		}
	}
}
