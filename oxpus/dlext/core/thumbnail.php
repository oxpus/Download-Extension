<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\core;

class thumbnail implements thumbnail_interface
{
	/* phpbb objects */
	protected $db;
	protected $request;
	protected $filesystem;

	/* extension owned objects */
	protected $dlext_constants;
	protected $dlext_downloads_table;
	protected $dlext_dlext_images_table;
	protected $dlext_dlext_ver_files_table;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\request\request 				$request
	 * @param \phpbb\filesystem\filesystem			$filesystem
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param string								$dlext_downloads_table
	 * @param string								$dlext_dlext_images_table
	 * @param string								$dlext_dlext_ver_files_table
	 */
	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		\phpbb\request\request $request,
		\phpbb\filesystem\filesystem $filesystem,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		$dlext_downloads_table,
		$dlext_dlext_images_table,
		$dlext_dlext_ver_files_table
	)
	{
		$this->db 				= $db;
		$this->request 			= $request;
		$this->filesystem		= $filesystem;

		$this->dlext_constants				= $dlext_constants;
		$this->dlext_downloads_table		= $dlext_downloads_table;
		$this->dlext_dlext_images_table		= $dlext_dlext_images_table;
		$this->dlext_dlext_ver_files_table	= $dlext_dlext_ver_files_table;
	}

	public function handle()
	{
		$pic_id		= $this->request->variable('pic', '', $this->dlext_constants::DL_TRUE);
		$img_type	= $this->request->variable('img_type', '', $this->dlext_constants::DL_TRUE);
		$disp_art	= $this->request->variable('disp_art', 0);

		if (!$pic_id || !$img_type)
		{
			return 'NO_THUMB';
		}

		switch ($img_type)
		{
			case 'thumb':
				$table		= $this->dlext_downloads_table;
				$field		= 'thumbnail';
				$data_id	= 'id';
				$folder		= '/thumbs/';
				break;
			case 'more':
				$table		= $this->dlext_dlext_images_table;
				$field		= 'img_name';
				$data_id	= 'img_id';
				$folder		= '/thumbs/';
				break;
			case 'version':
				$table		= $this->dlext_dlext_ver_files_table;
				$field		= 'file_name';
				$data_id	= 'ver_file_id';
				$folder		= '/version/images/';
				break;
		}

		if (!$table)
		{
			return 'NO_THUMB';
		}

		$sql = 'SELECT ' . $field . '
				FROM ' . $table . '
				WHERE ' . $data_id . ' = ' . (int) $pic_id;
		$result = $this->db->sql_query($sql);
		$real_filename = $this->db->sql_fetchfield($field);
		$this->db->sql_freeresult($result);

		if (!$real_filename)
		{
			return 'NO_THUMB';
		}

		$file_ext	= str_replace('.', '', trim(strrchr(strtolower($real_filename), '.')));
		$thumbnail	= $this->dlext_constants->get_value('files_dir') . $folder . $real_filename;

		if (!$this->filesystem->exists($thumbnail) && strpos($thumbnail, $this->dlext_constants->get_value('files_dir') . '/thumbs/') === 0)
		{
			return 'NOT_EXISTS';
		}

		$pic_size = getimagesize($thumbnail);
		$pic_width = $pic_size[0];
		$pic_height = $pic_size[1];

		if (!$pic_width || !$pic_height)
		{
			return 'NO_WIDTH';
		}

		$max_width = $this->dlext_constants::DL_THUMBS_MAX_WIDTH;
		$max_height = $this->dlext_constants::DL_THUMBS_MAX_HEIGHT;

		if (($pic_height <= $max_height) && ($pic_width <= $max_width))
		{
			$disp_art = $this->dlext_constants::DL_FALSE;
		}

		if (($pic_height / $max_height) > ($pic_width / $max_width))
		{
			$thumb_height	= $max_height;
			$thumb_width	= round($max_width * (($pic_width / $max_width) / ($pic_height / $max_height)));
		}
		else
		{
			$thumb_height	= round($max_height * (($pic_height / $max_height) / ($pic_width / $max_width)));
			$thumb_width	= $max_width;
		}

		$image = $this->_get_image($thumbnail, $file_ext);

		if ($image)
		{
			if ($disp_art)
			{
				$newimage = imagecreatetruecolor($thumb_width, $thumb_height);
				imagecopyresampled($newimage, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $pic_width, $pic_height);

				header('Content-type:image/jpg');
				imagejpeg($newimage);
			}
			else
			{
				if ($file_ext == 'jpg')
				{
					header('Content-type:image/jpg');
					imagejpeg($image);
				}
				else if ($file_ext == 'png')
				{
					header('Content-type:image/png');
					imagepng($image);
				}
				else if ($file_ext == 'gif')
				{
					if (function_exists('imagecreatefromgif'))
					{
						header('Content-type:image/gif');
						imagegif($image);
					}
				}
			}
		}

		garbage_collection();
		exit_handler();
	}

	public function _get_image($pic_path, $file_ext)
	{
		$image = $this->dlext_constants::DL_FALSE;

		if ($file_ext == 'jpg')
		{
			$image = imagecreatefromjpeg($pic_path);
		}
		else if ($file_ext == 'png')
		{
			$image = imagecreatefrompng($pic_path);
		}
		else if ($file_ext == 'gif')
		{
			if (function_exists('imagecreatefromgif'))
			{
				$image = imagecreatefromgif($pic_path);
			}
		}

		return $image;
	}
}
