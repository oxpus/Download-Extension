<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\controller;

use Symfony\Component\DependencyInjection\Container;

class ajax
{
	/* @var \phpbb\db\driver\driver_interface */
	protected $db;

	/* @var \phpbb\user */
	protected $user;

	/* @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\language\language */
	protected $language;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\request\request_interface */
	protected $request;

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interfacer		$db
	* @param \phpbb\user							$user
	* @param \phpbb\config\config					$config
	* @param \phpbb\language\language 				$language
	* @param \phpbb\template\template				$template
	* @param \phpbb\request\request_interface 		$request
	* @param Container 								$phpbb_container
	*/
	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		\phpbb\user $user,
		\phpbb\config\config $config,
		\phpbb\language\language $language,
		\phpbb\template\template $template,
		\phpbb\request\request_interface $request,
		Container $phpbb_container
	)
	{
		$this->db 			= $db;
		$this->user 		= $user;
		$this->config 		= $config;
		$this->language		= $language;
		$this->template 	= $template;
		$this->request		= $request;
	}

	public function handle()
	{
		$dl_id		= $this->request->variable('dl_id', 0);
		$rate_point	= $this->request->variable('rate_point', 0);
		$drop		= $this->request->variable('drop', 0);

		if ($dl_id && ($rate_point || $drop))
		{
			$sql = 'SELECT rate_point FROM ' . DL_RATING_TABLE . '
				WHERE dl_id = ' . (int) $dl_id . '
					AND user_id = ' . (int) $this->user->data['user_id'];
			$result = $this->db->sql_query($sql);
			$user_has_rated = $this->db->sql_affectedrows($result);
			$this->db->sql_freeresult($result);

			if ($rate_point && !$user_has_rated)
			{
				$sql = 'INSERT INTO ' . DL_RATING_TABLE . ' ' . $this->db->sql_build_array('INSERT', [
					'rate_point'	=> $rate_point,
					'user_id'		=> $this->user->data['user_id'],
					'dl_id'			=> $dl_id]);

				$user_has_rated = true;
			}
			else if ($drop)
			{
				$sql = 'DELETE FROM ' . DL_RATING_TABLE . '
						WHERE user_id = ' . (int) $this->user->data['user_id'] . '
							AND dl_id = ' . (int) $dl_id;

				$user_has_rated = false;
			}

			$this->db->sql_query($sql);

			$sql = 'SELECT AVG(rate_point) AS rating, COUNT(rate_point) AS total FROM ' . DL_RATING_TABLE . '
				WHERE dl_id = ' . (int) $dl_id . '
				GROUP BY dl_id';
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$new_rating = ceil($row['rating'] * 10);
			$total_ratings = $row['total'];
			$this->db->sql_freeresult($result);

			$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', [
				'rating' => $new_rating]) . ' WHERE id = ' . (int) $dl_id;
			$this->db->sql_query($sql);

			$rate_points_title = $new_rating / 10;

			$rating_title = $this->language->lang('DL_RATING_HOVER', $rate_points_title, $this->config['dl_rate_points']);

			$rate_img = '<span class="dl-rating" title="' . $rating_title . '">';
	
			$rate_yes = '<i class="icon fa-star fa-fw dl-green"></i>';
			$rate_no = '<i class="icon fa-star-o fa-fw dl-yellow"></i>';
			$rate_undo = '<i class="icon fa-times-circle fa-fw dl-red"></i>';

			for ($i = 0; $i < $this->config['dl_rate_points']; ++$i)
			{
				$j = $i + 1;
		
				if ($user_has_rated)
				{
					$ajax = '';
				}
				else
				{
					$ajax = 'onclick="AJAXDLVote(' . $dl_id . ', ' . $j . '); return false;"';
				}
				$rate_img .= ($j <= ceil($rate_points_title) ) ? '<a href="#" ' . $ajax . ' class="dl-rating-img">' . $rate_yes . '</a>' : '<a href="#" ' . $ajax . ' class="dl-rating-img">' . $rate_no . '</a>';
			}
		
			if ($user_has_rated)
			{
				$ajax = 'onclick="AJAXDLUnvote(' . $dl_id . '); return false;"';
				$rate_img .= ' <a href="#" ' . $ajax . ' class="dl-rating-img">' . $rate_undo . '</a>';
			}

			if ($total_ratings)
			{
				$rate_img .= '&nbsp;' . $this->language->lang('DL_RATING_COUNT', $total_ratings);
			}
	
			$json_out = json_encode(['rate_img' => $rate_img, 'dl_id' => $dl_id]);
		
			$http_headers = [
				'Content-type' => 'text/html; charset=UTF-8',
				'Cache-Control' => 'private, no-cache="set-cookie"',
				'Expires' => gmdate('D, d M Y H:i:s', time()) . ' GMT',
			];
		
			foreach ($http_headers as $hname => $hval)
			{
				header((string) $hname . ': ' . (string) $hval);
			}
		
			$this->template->set_filenames(['body' => 'dl_json.html']);
			$this->template->assign_var('JSON_OUTPUT', $json_out);
			$this->template->display('body');
		}

		garbage_collection();
		exit_handler();
	}
}
