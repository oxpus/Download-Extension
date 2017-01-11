<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dl_ext\includes\classes;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class dl_format extends dl_mod
{
	public function __construct()
	{
		return;
	}

	public function __destruct()
	{
		return;
	}

	public static function dl_size($input_value, $rnd = 2, $out_type = 'combine')
	{
		global $phpbb_container;
		$language = $phpbb_container->get('language');

		if ($input_value < 1024)
		{
			$output_value = $input_value;
			if ($out_type == 'select')
			{
				$output_desc = 'byte';
			}
			else
			{
				$output_desc = '&nbsp;'.$language->lang('DL_BYTES');
			}
		}
		else if ($input_value < 1048576)
		{
			$output_value = $input_value / 1024;
			if ($out_type == 'select')
			{
				$output_desc = 'kb';
			}
			else
			{
				$output_desc = '&nbsp;'.$language->lang('DL_KB');
			}
		}
		else if ($input_value < 1073741824)
		{
			$output_value = $input_value / 1048576;
			if ($out_type == 'select')
			{
				$output_desc = 'mb';
			}
			else
			{
				$output_desc = '&nbsp;'.$language->lang('DL_MB');
			}
		}
		else
		{
			$output_value = $input_value / 1073741824;
			if ($out_type == 'select')
			{
				$output_desc = 'gb';
			}
			else
			{
				$output_desc = '&nbsp;'.$language->lang('DL_GB');
			}
		}

		$output_value = round($output_value, $rnd);

		$data_out = ($out_type == 'combine') ? $output_value . $output_desc : array('size_out' => $output_value, 'range' => $output_desc);

		return $data_out;
	}

	public static function rating_img($rating_points, $rate = false, $df_id = 0, $ext_path_images)
	{
		global $user, $config, $phpbb_container;
		$language = $phpbb_container->get('language');

		if (!$config['dl_enable_rate'])
		{
			return false;
		}

		$rate_points = ceil($rating_points);
		$rate_image = '';

		for ($i = 0; $i < $config['dl_rate_points']; $i++)
		{
			$j = $i + 1;

			if ($rate)
			{
				$ajax = 'onclick="AJAXDLVote(' . $df_id . ', ' . $j . '); return false;"';
				$rate_image .= ($j <= $rate_points ) ? '<a href="#" ' . $ajax . '>' . '<img src="' . $ext_path_images . 'dl_rate_yes.png" alt="' . $language->lang('IMG_DL_RATE_YES') . '" />' . '</a>' : '<a href="#" ' . $ajax . '>' . '<img src="' . $ext_path_images . 'dl_rate_no.png" alt="' . $language->lang('IMG_DL_RATE_NO') . '" />' . '</a>';

			}
			else
			{
				$rate_image .= ($j <= $rate_points ) ? '<img src="' . $ext_path_images . 'dl_rate_yes.png" alt="' . $language->lang('IMG_DL_RATE_YES') . '" />' : '<img src="' . $ext_path_images . 'dl_rate_no.png" alt="' . $language->lang('IMG_DL_RATE_NO') . '" />';
			}
		}

		return $rate_image;
	}

	public static function resize_value($config_name, $config_value)
	{
		global $request;

		switch ($config_name)
		{
			case 'dl_thumb_fsize':				$quote = 'dl_f_quote';	break;
			case 'dl_physical_quota':			$quote = 'dl_x_quota';	break;
			case 'dl_overall_traffic':			$quote = 'dl_x_over';	break;
			case 'dl_overall_guest_traffic':	$quote = 'dl_x_g_over';	break;
			case 'dl_newtopic_traffic':			$quote = 'dl_x_new';	break;
			case 'dl_reply_traffic':			$quote = 'dl_x_reply';	break;
			case 'dl_method_quota':				$quote = 'dl_m_quote';	break;
			case 'dl_extern_size':				$quote = 'dl_e_quote';	break;
			case 'dl_file_traffic':				$quote = 'dl_t_quote';	break;
		}
	
		$x = $request->variable($quote, '');
	
		switch($x)
		{
			case 'kb': $config_value = floor($config_value * 1024);			break;
			case 'mb': $config_value = floor($config_value * 1048576);		break;
			case 'gb': $config_value = floor($config_value * 1073741824);	break;
		}
	
		return $config_value;
	}
}
