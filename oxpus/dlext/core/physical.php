<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2021 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/


namespace oxpus\dlext\core;

class physical implements physical_interface
{
	/* phpbb objects */
	protected $root_path;
	protected $language;
	protected $db;
	protected $user;
	protected $extension_manager;
	protected $filesystem;
	protected $finder;

	/* extension owned objects */
	protected $dlext_files;
	protected $dlext_format;
	protected $dlext_constants;

	protected $dlext_table_dl_versions;
	protected $dlext_table_downloads;

	/**
	* Constructor
	*
	* @param string 								$root_path
	* @param \phpbb\language\language				$language
	* @param \phpbb\db\driver\driver_interface		$db
	* @param \phpbb\user							$user
	* @param \phpbb\extension\manager				$extension_manager
	* @param \phpbb\filesystem\filesystem			$filesystem
	* @param \oxpus\dlext\core\files				$dlext_files
	* @param \oxpus\dlext\core\format				$dlext_format
	* @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	* @param string									$dlext_table_dl_versions
	* @param string									$dlext_table_downloads
	*/
	public function __construct(
		$root_path,
		\phpbb\language\language $language,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\user $user,
		\phpbb\extension\manager $extension_manager,
		\phpbb\filesystem\filesystem $filesystem,
		\oxpus\dlext\core\files $dlext_files,
		\oxpus\dlext\core\format $dlext_format,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		$dlext_table_dl_versions,
		$dlext_table_downloads
	)
	{
		$this->root_path			= $root_path;
		$this->db 					= $db;
		$this->user 				= $user;
		$this->language 			= $language;
		$this->extension_manager	= $extension_manager;
		$this->filesystem 			= $filesystem;

		$this->finder				= $this->extension_manager->get_finder();

		$this->dlext_files			= $dlext_files;
		$this->dlext_format			= $dlext_format;
		$this->dlext_constants		= $dlext_constants;

		$this->dlext_table_dl_versions	= $dlext_table_dl_versions;
		$this->dlext_table_downloads	= $dlext_table_downloads;
	}

	public function read_exist_files()
	{
		$dl_files = $this->dlext_files->all_files(0, [], [], 0, 1, ['real_file']);

		$exist_files = [];

		for ($i = 0; $i < count($dl_files); ++$i)
		{
			$exist_files[] = $dl_files[$i]['real_file'];
		}

		$sql = 'SELECT ver_real_file
				FROM ' . $this->dlext_table_dl_versions;
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$exist_files[] = $row['ver_real_file'];
		}

		$this->db->sql_freeresult($result);

		return $exist_files;
	}

	public function read_dl_dirs()
	{
		$download_dir = $this->dlext_constants->get_value('files_dir', $this->dlext_constants::DL_TRUE) . '/downloads/';

		$dirs = $this->finder
			->set_extensions([])
			->core_path($download_dir)
			->find(false);

		foreach ($dirs as $dir => $path)
		{
			$folders[] = [
				'path'		=> $this->root_path . $dir,
				'target'	=> $this->language->lang('DL_MOVE_TARGET', $this->root_path . $dir),
			];
		}

		return $folders;
	}

	public function read_dl_sizes($download_dir = '')
	{
		if ($download_dir)
		{
			$file_size = 0;

			$files = $this->finder
			->set_extensions([])
			->core_path($download_dir)
			->find(false);

			foreach ($files as $file => $path)
			{
				if (basename($file) != 'index.html')
				{
					$file_size += sprintf("%u", filesize($this->root_path . $file));
				}
			}
		}
		else
		{
			$sql = 'SELECT SUM(file_size) AS total_size FROM . ' . $this->dlext_table_downloads;
			$result = $this->db->sql_query($sql);
			$file_size = $this->db->sql_fetchfield('total_size');
			$this->db->sql_freeresult($result);
		}

		return $file_size;
	}

	public function dl_max_upload_size()
	{
		$post_max	= ini_get('post_max_size');
		$upload_max	= ini_get('upload_max_filesize');

		$post_max_unit		= substr($post_max, -1, 1);
		$upload_max_unit	= substr($upload_max, -1, 1);

		$post_max_value		= intval(substr($post_max, 0, strlen($post_max) - 1));
		$upload_max_value	= intval(substr($upload_max, 0, strlen($upload_max) - 1));

		$unit_factor		= [
			'K' => $this->dlext_constants::DL_FILE_SIZE_KBYTE,
			'M' => $this->dlext_constants::DL_FILE_SIZE_MBYTE,
			'G' => $this->dlext_constants::DL_FILE_SIZE_GBYTE
		];

		$post_max_size		= $post_max_value * $unit_factor[$post_max_unit];
		$upload_max_size	= $upload_max_value * $unit_factor[$upload_max_unit];

		$max_upload_size = min($post_max_size, $upload_max_size);

		return $this->dlext_format->dl_size($max_upload_size, 0, 'combine');
	}

	/*
	* Read the existing downloads folder tree to select a path within the categories management
	* instead enter the complete path "old school" manually.
	*/
	public function get_file_base_tree($selected_path = '', $check = 0)
	{
		$file_base = $this->dlext_constants->get_value('files_dir', $this->dlext_constants::DL_TRUE) . '/downloads/';

		$dirs = $this->finder
			->set_extensions([])
			->core_path($file_base)
			->find(false, true);

		foreach ($dirs as $dir => $value)
		{
			$separator = '';

			$cat_path = str_replace($this->dlext_constants->get_value('files_dir', $this->dlext_constants::DL_TRUE) . '/downloads/', '', $dir);

			$path_parts = explode('/', $cat_path);

			if (count($path_parts) > 2)
			{
				for ($i = 1; $i < count($path_parts) - 1; ++$i)
				{
					$separator .= $this->language->lang('DL_SEPERATOR_PREFIX');
				}

				$separator .= $this->language->lang('DL_SEPERATOR_SUFFIX');
			}

			if ($cat_path == $selected_path)
			{
				$selected = $this->dlext_constants::DL_TRUE;
			}
			else
			{
				$selected = $this->dlext_constants::DL_FALSE;
			}

			if ($check)
			{
				$tree[] = $cat_path;
			}
			else
			{
				$tree[] = [
					'cat_path'	=> $cat_path,
					'selected'	=> $selected,
					'entry'		=> $separator . basename($cat_path) . '/',
				];
			}
		}

		return $tree;
	}

	/**
	 * Send the selected file to the user client (webbrowser) = download
	 * Function taken from phpBB 3.3.3
	 * original (c) phpBB Limited <https://www.phpbb.com>
	 * modified by oxpus for download extension
	*/
	public function send_file_to_browser($dl_file_data)
	{
		if (@ob_get_length())
		{
			@ob_end_clean();
		}

		// Now the tricky part... let's dance
		header('Cache-Control: private');

		// Send out the Headers. Do not set Content-Disposition to inline please, it is a security measure for users using the Internet Explorer.
		header('Content-Type: ' . $dl_file_data['mimetype']);

		if (phpbb_is_greater_ie_version($this->user->browser, 7))
		{
			header('X-Content-Type-Options: nosniff');
		}

		if (empty($this->user->browser) || ((strpos(strtolower($this->user->browser), 'msie') !== false) && !phpbb_is_greater_ie_version($this->user->browser, 7)))
		{
			header('Content-Disposition: attachment; ' . header_filename(htmlspecialchars_decode($dl_file_data['real_filename'])));
			if (empty($this->user->browser) || (strpos(strtolower($this->user->browser), 'msie 6.0') !== false))
			{
				header('Expires: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
			}
		}
		else
		{
			header('Content-Disposition: ' . ((strpos($dl_file_data['mimetype'], 'image') === 0) ? 'inline' : 'attachment') . '; ' . header_filename(htmlspecialchars_decode($dl_file_data['real_filename'])));
			if (phpbb_is_greater_ie_version($this->user->browser, 7) && (strpos($dl_file_data['mimetype'], 'image') !== 0))
			{
				header('X-Download-Options: noopen');
			}
		}

		// Close the db connection before sending the file etc.
		file_gc($this->dlext_constants::DL_FALSE);

		if (!set_modified_headers($dl_file_data['filetime'], $this->user->browser))
		{
			$size = $dl_file_data['filesize'];

			if ($size)
			{
				header("Content-Length: $size");
			}

			// Try to deliver in chunks
			set_time_limit(0);

			$out = fopen('php://output', 'wb');

			if (isset($dl_file_data['filestream']))
			{
				$file = fopen('php://memory', 'r+');
				fwrite($file, $dl_file_data['physical_file']);
				rewind($file);
			}
			else
			{
				$file = fopen($dl_file_data['physical_file'], 'rb');
			}

			stream_copy_to_stream($file, $out);

			fclose($out);
			fclose($file);

			flush();
		}

		exit_handler();
	}
}
