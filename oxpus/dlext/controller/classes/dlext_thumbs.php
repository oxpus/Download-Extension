<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\controller\classes;

class dlext_thumbs implements dlext_thumbs_interface
{
	/* @var \phpbb\request\request_interface */
	protected $request;

	/**
	* Constructor
	*
	* @param \phpbb\request\request_interface 		$request
	*/
	public function __construct(
		\phpbb\request\request_interface $request
	)
	{
		$this->request 		= $request;
	}

	public function handle()
	{
		$thumbnail	= $this->request->variable('thumbnail', '', true);
		$disp_art	= $this->request->variable('disp_art', 0);

		if (!$thumbnail)
		{
			return;
		}

		$thumbnail = base64_decode($thumbnail);

		$file_ext = str_replace('.', '', trim(strrchr(strtolower($thumbnail), '.')));

		if (!file_exists($thumbnail))
		{
			return;
		}

		$pic_size = @getimagesize($thumbnail);
		$pic_width = $pic_size[0];
		$pic_height = $pic_size[1];

		if (!$pic_width || !$pic_height)
		{
			return;
		}

		$max_width = 150;
		$max_height = 100;

		if (($pic_height <= $max_height) && ($pic_width <= $max_width))
		{
			$disp_art = false;
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

		@imagedestroy($image);
		@imagedestroy($newimage);
		unset($image);
		return;
	}

	public function _get_image($pic_path, $file_ext)
	{
		$image = false;

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
