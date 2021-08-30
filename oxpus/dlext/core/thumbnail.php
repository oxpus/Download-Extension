<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\core;

use Symfony\Component\HttpFoundation\Response;

class thumbnail implements thumbnail_interface
{
	/* phpbb objects */
	protected $request;
	protected $filesystem;

	/* extension owned objects */
	protected $dlext_constants;

	/**
	 * Constructor
	 *
	 * @param \phpbb\request\request 				$request
	 * @param \phpbb\filesystem\filesystem			$filesystem
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 */
	public function __construct(
		\phpbb\request\request $request,
		\phpbb\filesystem\filesystem $filesystem,
		\oxpus\dlext\core\helpers\constants $dlext_constants
	)
	{
		$this->request 			= $request;
		$this->filesystem		= $filesystem;

		$this->dlext_constants	= $dlext_constants;
	}

	public function handle()
	{
		$thumbnail	= $this->request->variable('pic', '', $this->dlext_constants::DL_TRUE);
		$disp_art	= $this->request->variable('disp_art', 0);

		if (!$thumbnail)
		{
			return 'NO_THUMB';
		}

		$thumbnail	= base64_decode($thumbnail);
		$file_ext	= str_replace('.', '', trim(strrchr(strtolower($thumbnail), '.')));

		if (!$this->filesystem->exists($thumbnail) and strpos($thumbnail, $this->dlext_constants->get_value('files_dir') . '/thumbs/') === 0)
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
				imagejpeg($newimage);
			}
			else
			{
				if ($file_ext == 'jpg')
				{
					imagejpeg($image);
				}
				else if ($file_ext == 'png')
				{
					imagepng($image);
				}
				else if ($file_ext == 'gif')
				{
					if (function_exists('imagecreatefromgif'))
					{
						imagegif($image);
					}
				}
			}
		}

		imagedestroy($image);
		imagedestroy($newimage);
		unset($image);

		return new Response(json_encode(['status' => 'OK']));
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
