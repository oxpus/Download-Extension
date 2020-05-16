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

class dlext_format implements dlext_format_interface
{
	/* @var \phpbb\config\config */
	protected $config;

	protected $language;
	protected $request;

	/**
	* Constructor
	*
	* @param Container 								$phpbb_container

	* @param \phpbb\config\config					$config
	*/
	public function __construct(
		Container $phpbb_container,

		\phpbb\config\config $config
	)
	{
		$this->config 		= $config;

		$this->language		= $phpbb_container->get('language');
		$this->request		= $phpbb_container->get('request');
	}

	public function dl_size($input_value, $rnd = 2, $out_type = 'combine')
	{
		if ($input_value < 1024)
		{
			$output_value = $input_value;
			if ($out_type == 'select')
			{
				$output_desc = 'byte';
			}
			else
			{
				$output_desc = ' ' . $this->language->lang('DL_BYTES');
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
				$output_desc = ' ' . $this->language->lang('DL_KB');
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
				$output_desc = ' ' . $this->language->lang('DL_MB');
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
				$output_desc = ' ' . $this->language->lang('DL_GB');
			}
		}

		$output_value = round($output_value, $rnd);

		$data_out = ($out_type == 'combine') ? $output_value . $output_desc : ['size_out' => $output_value, 'range' => $output_desc];

		return $data_out;
	}

	public function rating_img($rating_points, $rate = false, $df_id = 0)
	{
		if (!$this->config['dl_enable_rate'])
		{
			return false;
		}

		$rate_points = ceil($rating_points);
		$rate_image = '';
		$rate_yes = '<i class="icon fa-star fa-fw dl-green" title=""></i>';
		$rate_no = '<i class="icon fa-star-o fa-fw dl-yellow" title=""></i>';

		for ($i = 0; $i < $this->config['dl_rate_points']; $i++)
		{
			$j = $i + 1;

			if ($rate)
			{
				$ajax = 'onclick="AJAXDLVote(' . $df_id . ', ' . $j . '); return false;"';
				$rate_image .= ($j <= $rate_points ) ? '<a href="#" ' . $ajax . '>' . $rate_yes . '</a>' : '<a href="#" ' . $ajax . '>' . $rate_no . '</a>';

			}
			else
			{
				$rate_image .= ($j <= $rate_points ) ? $rate_yes : $rate_no;
			}
		}

		return $rate_image;
	}

	public function resize_value($config_name, $config_value)
	{
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

		$x = $this->request->variable($quote, '');

		switch($x)
		{
			case 'kb': $config_value = floor($config_value * 1024);			break;
			case 'mb': $config_value = floor($config_value * 1048576);		break;
			case 'gb': $config_value = floor($config_value * 1073741824);	break;
		}

		return $config_value;
	}
}
