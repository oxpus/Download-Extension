<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\core;

class format implements format_interface
{
	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames return_ary

	/* phpbb objects */
	protected $config;
	protected $user;
	protected $language;
	protected $request;

	/* extension owned objects */
	protected $dlext_constants;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config					$config
	 * @param \phpbb\user							$user
	 * @param \phpbb\language\language				$language
	 * @param \phpbb\request\request 				$request
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 */
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\user $user,
		\phpbb\language\language $language,
		\phpbb\request\request $request,
		\oxpus\dlext\core\helpers\constants $dlext_constants
	)
	{
		$this->config 			= $config;
		$this->user 			= $user;
		$this->language			= $language;
		$this->request			= $request;

		$this->dlext_constants	= $dlext_constants;
	}

	public function dl_size($input_value, $rnd = 2, $out_type = 'combine')
	{
		if ($input_value < $this->dlext_constants::DL_FILE_SIZE_KBYTE)
		{
			$output_value = $input_value;
			if ($out_type == 'select')
			{
				$output_desc = $this->dlext_constants::DL_FILE_RANGE_BYTE;
			}
			else
			{
				$output_desc = ' ' . $this->language->lang('DL_BYTES');
			}
		}
		else if ($input_value < $this->dlext_constants::DL_FILE_SIZE_MBYTE)
		{
			$output_value = $input_value / $this->dlext_constants::DL_FILE_SIZE_KBYTE;
			if ($out_type == 'select')
			{
				$output_desc = $this->dlext_constants::DL_FILE_RANGE_KBYTE;
			}
			else
			{
				$output_desc = ' ' . $this->language->lang('DL_KB');
			}
		}
		else if ($input_value < $this->dlext_constants::DL_FILE_SIZE_GBYTE)
		{
			$output_value = $input_value / $this->dlext_constants::DL_FILE_SIZE_MBYTE;
			if ($out_type == 'select')
			{
				$output_desc = $this->dlext_constants::DL_FILE_RANGE_MBYTE;
			}
			else
			{
				$output_desc = ' ' . $this->language->lang('DL_MB');
			}
		}
		else
		{
			$output_value = $input_value / $this->dlext_constants::DL_FILE_SIZE_GBYTE;
			if ($out_type == 'select')
			{
				$output_desc = $this->dlext_constants::DL_FILE_RANGE_GBYTE;
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
			return $this->dlext_constants::DL_FALSE;
		}

		$rate_points = ceil($rating_points / $this->dlext_constants::DL_RATING_MULTIFIER);

		$return_ary['count']['title']	= $this->language->lang('DL_RATING_HOVER', $rating_points / $this->dlext_constants::DL_RATING_MULTIFIER, $this->config['dl_rate_points']);
		$return_ary['count']['max']		= $this->config['dl_rate_points'];
		$return_ary['count']['dlId']	= $df_id;

		for ($i = 0; $i < $this->config['dl_rate_points']; ++$i)
		{
			$j = $i + 1;

			if ($rate)
			{
				$return_ary['stars'][$i]['ajax'] = $j;
				$return_ary['stars'][$i]['icon'] = ($j <= $rate_points) ? 'yes' : 'no';
			}
			else
			{
				$return_ary['stars'][$i]['ajax'] = 0;
				$return_ary['stars'][$i]['icon'] = ($j <= $rate_points) ? 'yes' : 'no';
			}
		}

		if (!$rate && $this->user->data['is_registered'])
		{
			$return_ary['count']['undo'] = 1;
		}
		else
		{
			$return_ary['count']['undo'] = 0;
		}

		if ($total_ratings)
		{
			$return_ary['count']['count'] = $this->language->lang('DL_RATING_COUNT', $total_ratings);
		}
		else
		{
			$return_ary['count']['count'] = '-';
		}

		return $return_ary;
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

		$file_size_range = $this->request->variable($quote, '', $this->dlext_constants::DL_TRUE);

		return $this->get_traffic_save_value($config_value, $file_size_range);
	}

	public function get_traffic_save_value($traffic_amount, $traffic_range)
	{
		if ($traffic_amount == 0)
		{
			return 0;
		}

		switch ($traffic_range)
		{
			case $this->dlext_constants::DL_FILE_RANGE_BYTE:
				$traffic_bytes = $traffic_amount;
				break;
			case $this->dlext_constants::DL_FILE_RANGE_KBYTE:
				$traffic_bytes = floor($traffic_amount * $this->dlext_constants::DL_FILE_SIZE_KBYTE);
				break;
			case $this->dlext_constants::DL_FILE_RANGE_MBYTE:
				$traffic_bytes = floor($traffic_amount * $this->dlext_constants::DL_FILE_SIZE_MBYTE);
				break;
			case $this->dlext_constants::DL_FILE_RANGE_GBYTE:
				$traffic_bytes = floor($traffic_amount * $this->dlext_constants::DL_FILE_SIZE_GBYTE);
				break;
			default:
				$traffic_bytes = 0;
		}

		return $traffic_bytes;
	}

	public function get_traffic_display_value($traffic_amount)
	{
		if ($traffic_amount >= $this->dlext_constants::DL_FILE_SIZE_GBYTE)
		{
			$traffic_value = number_format($traffic_amount / $this->dlext_constants::DL_FILE_SIZE_GBYTE, 2);
			$traffic_range = $this->dlext_constants::DL_FILE_RANGE_GBYTE;
		}
		if ($traffic_amount < $this->dlext_constants::DL_FILE_SIZE_GBYTE)
		{
			$traffic_value = number_format($traffic_amount / $this->dlext_constants::DL_FILE_SIZE_MBYTE, 2);
			$traffic_range = $this->dlext_constants::DL_FILE_RANGE_MBYTE;
		}
		if ($traffic_amount < $this->dlext_constants::DL_FILE_SIZE_MBYTE)
		{
			$traffic_value = number_format($traffic_amount / $this->dlext_constants::DL_FILE_SIZE_KBYTE, 2);
			$traffic_range = $this->dlext_constants::DL_FILE_RANGE_KBYTE;
		}
		if ($traffic_amount < $this->dlext_constants::DL_FILE_SIZE_KBYTE)
		{
			$traffic_value = number_format($traffic_amount, 2);
			$traffic_range = $this->dlext_constants::DL_FILE_RANGE_BYTE;
		}

		return ['traffic_value' => $traffic_value, 'traffic_range' => $traffic_range];
	}

	public function dl_hash($value, $type = '', $method = '')
	{
		if (!$method)
		{
			$method = $this->dlext_constants::DL_FILE_HASH_MD5;
		}
		else if (!in_array($method, [
			$this->dlext_constants::DL_FILE_HASH_MD5,
			$this->dlext_constants::DL_FILE_HASH_SHA,
		]))
		{
			$method = $this->dlext_constants::DL_FILE_HASH_MD5;
		}

		if ($type == 'file')
		{
			$function_hash = $method . '_file';

			return $function_hash($value);
		}

		if ($type == 'post')
		{
			$function_hash = $this->dlext_constants::DL_FILE_HASH_PHPBB;
			return $function_hash($value);
		}

		return unique_id() . '_' . $method($value);
	}

	public function dl_shorten_string($text, $mode, $uid, $bitfield, $flags)
	{
		$shorten = $this->dlext_constants::DL_FALSE;

		switch ($mode)
		{
			case 'feed':
				switch ($this->config['dl_rss_desc_length'])
				{
					case $this->dlext_constants::DL_RSS_DESC_LENGTH_FULL:
						$text = censor_text($text);
						strip_bbcode($text, $uid);
						$text = bbcode_nl2br($text);
					break;
					case $this->dlext_constants::DL_RSS_DESC_LENGTH_SHORT:
						$shorten = $this->dlext_constants::DL_TRUE;
						$text_length_config = 'dl_rss_desc_shorten';
					break;
					default:
						$text = '';
				}
			break;
			case 'index':
			case 'search':
				$shorten = $this->dlext_constants::DL_TRUE;
				$text_length_config = 'dl_limit_desc_on_' . $mode;
			break;
		}

		if ($mode == 'feed' && !$shorten)
		{
			return ($text) ? bbcode_nl2br($text) : '';
		}

		$text = censor_text($text);
		$text_tmp = $text;
		strip_bbcode($text_tmp, $uid);

		if ($shorten)
		{
			$text_length = (int) $this->config[$text_length_config];

			if ($text_length && utf8_strlen($text_tmp) > $text_length)
			{
				strip_bbcode($text, $uid);
				$text = truncate_string($text, $text_length, $this->dlext_constants::DL_MAX_STRING_LENGTH, $this->dlext_constants::DL_FALSE);
				$text = bbcode_nl2br($text);
				$text = $this->language->lang('DL_SHORTEN_TEXT', $text);
			}
			else
			{
				$shorten = $this->dlext_constants::DL_FALSE;

				if ($mode == 'feed')
				{
					strip_bbcode($text, $uid);
					$text = bbcode_nl2br($text);
				}
			}
		}

		if (!$shorten)
		{
			$text = generate_text_for_display($text, $uid, $bitfield, $flags);
		}

		return $text;
	}

	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames
}
