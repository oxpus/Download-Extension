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

	/* @var \phpbb\user */
	protected $user;

	protected $language;
	protected $request;

	/**
	* Constructor
	*
	* @param Container 								$phpbb_container

	* @param \phpbb\config\config					$config
	* @param \phpbb\user							$user
	*/
	public function __construct(
		Container $phpbb_container,

		\phpbb\config\config $config,
		\phpbb\user $user
	)
	{
		$this->config 		= $config;
		$this->user 		= $user;

		$this->language		= $phpbb_container->get('language');
		$this->request		= $phpbb_container->get('request');
		$this->user			= $phpbb_container->get('user');
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

	public function rating_img($rating_points, $rate = false, $df_id = 0, $total_ratings = 0)
	{
		if (!$this->config['dl_enable_rate'])
		{
			return false;
		}

		$rate_points = ceil($rating_points / 10);

		$rating_title = $this->language->lang('DL_RATING_HOVER', $rating_points / 10, $this->config['dl_rate_points']);
		$rate_image = '<span class="dl-rating" title="' . $rating_title . '">';

		$rate_yes = '<i class="icon fa-star fa-fw dl-green"></i>';
		$rate_no = '<i class="icon fa-star-o fa-fw dl-yellow"></i>';
		$rate_undo = '<i class="icon fa-times-circle fa-fw dl-red"></i>';

		for ($i = 0; $i < $this->config['dl_rate_points']; ++$i)
		{
			$j = $i + 1;

			if ($rate)
			{
				$ajax = 'onclick="AJAXDLVote(' . $df_id . ', ' . $j . '); return false;"';
				$rate_image .= ($j <= $rate_points ) ? '<a href="#" ' . $ajax . ' class="dl-rating-img">' . $rate_yes . '</a>' : '<a href="#" ' . $ajax . ' class="dl-rating-img">' . $rate_no . '</a>';
			}
			else
			{
				$rate_image .= ($j <= $rate_points ) ? $rate_yes : $rate_no;
			}
		}

		if (!$rate && $this->user->data['is_registered'])
		{
			$ajax = 'onclick="AJAXDLUnvote(' . $df_id . '); return false;"';
			$rate_image .= ' <a href="#" ' . $ajax . ' class="dl-rating-img">' . $rate_undo . '</a>';
		}

		if ($total_ratings)
		{
			$rate_image .= '&nbsp;' . $this->language->lang('DL_RATING_COUNT', $total_ratings);
		}

		$rate_image .= '</span>';

		return $rate_image;
	}

	public function resize_value($config_name, $config_value)
	{
		switch ($config_name)
		{
			case 'dl_thumb_fsize':
				$quote = 'dl_f_quote';
			break;
			case 'dl_physical_quota':
				$quote = 'dl_x_quota';
			break;
			case 'dl_overall_traffic':
				$quote = 'dl_x_over';
			break;
			case 'dl_overall_guest_traffic':
				$quote = 'dl_x_g_over';
			break;
			case 'dl_newtopic_traffic':
				$quote = 'dl_x_new';
			break;
			case 'dl_reply_traffic':
				$quote = 'dl_x_reply';
			break;
			case 'dl_method_quota':
				$quote = 'dl_m_quote';
			break;
			case 'dl_extern_size':
				$quote = 'dl_e_quote';
			break;
			case 'dl_file_traffic':
				$quote = 'dl_t_quote';
			break;
		}

		$x = $this->request->variable($quote, '');

		switch ($x)
		{
			case 'kb':
				$config_value = floor($config_value * 1024);
			break;
			case 'mb':
				$config_value = floor($config_value * 1048576);
			break;
			case 'gb':
				$config_value = floor($config_value * 1073741824);
			break;
		}

		return $config_value;
	}
}
