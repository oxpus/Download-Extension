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
			case 'thumb_list':
				$table		= $this->dlext_dlext_images_table;
				$field		= 'img_name';
				$data_id	= 'dl_id';
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
		if ($img_type == 'thumb')
		{
			$sql .= ' AND img_index = 1 ';
		}
		if ($img_type == 'thumb_list')
		{
			$sql .= ' AND img_lists = 1 ';
		}

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

		if (($pic_height <= DL_THUMBS_MAX_HEIGHT) && ($pic_width <= DL_THUMBS_MAX_WIDTH))
		{
			$disp_art = $this->dlext_constants::DL_FALSE;
		}

		if (($pic_height / DL_THUMBS_MAX_HEIGHT) > ($pic_width / DL_THUMBS_MAX_WIDTH))
		{
			$thumb_height	= DL_THUMBS_MAX_HEIGHT;
			$thumb_width	= round(DL_THUMBS_MAX_WIDTH * (($pic_width / DL_THUMBS_MAX_WIDTH) / ($pic_height / DL_THUMBS_MAX_HEIGHT)));
		}
		else
		{
			$thumb_height	= round(DL_THUMBS_MAX_HEIGHT * (($pic_height / DL_THUMBS_MAX_HEIGHT) / ($pic_width / DL_THUMBS_MAX_WIDTH)));
			$thumb_width	= DL_THUMBS_MAX_WIDTH;
		}

		$image = $this->_get_image($thumbnail, $file_ext);

		if ($image)
		{
			imagealphablending($image, false);
			imagesavealpha($image, true);

			if ($disp_art)
			{
				$newimage = imagecreatetruecolor($thumb_width, $thumb_height);
				imagealphablending($newimage, false);
				imagesavealpha($newimage, true);
				$transparent = imagecolorallocatealpha($newimage, 255, 255, 255, 127);
				imagefilledrectangle($newimage, 0, 0, $thumb_width, $thumb_height, $transparent);
				imagecopyresampled($newimage, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $pic_width, $pic_height);

				$image = $newimage;
				unset($newimage);
			}

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
