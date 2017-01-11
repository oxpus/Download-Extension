<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if ($df_id && $rate_point)
{
	$sql = 'INSERT INTO ' . DL_RATING_TABLE . ' ' . $this->db->sql_build_array('INSERT', array(
		'rate_point'	=> $rate_point,
		'user_id'		=> $this->user->data['user_id'],
		'dl_id'			=> $df_id));
	$this->db->sql_query($sql);
	
	$sql = 'SELECT AVG(rate_point) AS rating FROM ' . DL_RATING_TABLE . '
		WHERE dl_id = ' . (int) $df_id . '
		GROUP BY dl_id';
	$result = $this->db->sql_query($sql);
	$new_rating = ceil($this->db->sql_fetchfield('rating'));
	$this->db->sql_freeresult($result);
	
	$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', array(
		'rating' => $new_rating)) . ' WHERE id = ' . (int) $df_id;
	$this->db->sql_query($sql);
	
	$rate_img = '';
	
	for ($i = 0; $i < $this->config['dl_rate_points']; $i++)
	{
		$j = $i + 1;
	
		$rate_img .= ($j <= $new_rating ) ? '<img src="' . $ext_path_images . 'dl_rate_yes.png" alt="' . $this->language->lang('IMG_DL_RATE_YES') . '" />' : '<img src="' . $ext_path_images . 'dl_rate_no.png" alt="' . $this->language->lang('IMG_DL_RATE_NO') . '" />';
	}

	$json_out = json_encode(array('rate_img' => $rate_img, 'df_id' => $df_id));

	$http_headers = array(
		'Content-type' => 'text/html; charset=UTF-8',
		'Cache-Control' => 'private, no-cache="set-cookie"',
		'Expires' => gmdate('D, d M Y H:i:s', time()) . ' GMT',
	);

	foreach ($http_headers as $hname => $hval)
	{
		header((string) $hname . ': ' . (string) $hval);
	}

	echo ($json_out);
	flush();
}
