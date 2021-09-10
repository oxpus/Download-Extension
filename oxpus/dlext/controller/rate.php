<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\controller;

use Symfony\Component\HttpFoundation\Response;

class rate
{
	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames return_ary

	/* phpbb objects */
	protected $db;
	protected $user;
	protected $config;
	protected $language;
	protected $request;

	/* extension owned objects */
	protected $dlext_constants;

	protected $dlext_table_dl_ratings;
	protected $dlext_table_downloads;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\user							$user
	 * @param \phpbb\config\config					$config
	 * @param \phpbb\language\language 				$language
	 * @param \phpbb\request\request 				$request
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param string								$dlext_table_dl_ratings
	 * @param string								$dlext_table_downloads
	 */
	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		\phpbb\user $user,
		\phpbb\config\config $config,
		\phpbb\language\language $language,
		\phpbb\request\request $request,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		$dlext_table_dl_ratings,
		$dlext_table_downloads
	)
	{
		$this->db 			= $db;
		$this->user 		= $user;
		$this->config 		= $config;
		$this->language		= $language;
		$this->request		= $request;

		$this->dlext_constants			= $dlext_constants;

		$this->dlext_table_dl_ratings	= $dlext_table_dl_ratings;
		$this->dlext_table_downloads	= $dlext_table_downloads;
	}

	public function handle()
	{
		$dl_id		= $this->request->variable('dl_id', 0);
		$rate_point	= $this->request->variable('rate_point', 0);
		$drop		= $this->request->variable('drop', 0);

		if ($dl_id && ($rate_point || $drop))
		{
			$sql = 'SELECT rate_point FROM ' . $this->dlext_table_dl_ratings . '
				WHERE dl_id = ' . (int) $dl_id . '
					AND user_id = ' . (int) $this->user->data['user_id'];
			$result = $this->db->sql_query($sql);
			$user_has_rated = $this->db->sql_affectedrows();
			$this->db->sql_freeresult($result);

			if ($rate_point && !$user_has_rated)
			{
				$sql = 'INSERT INTO ' . $this->dlext_table_dl_ratings . ' ' . $this->db->sql_build_array('INSERT', [
					'rate_point'	=> $rate_point,
					'user_id'		=> $this->user->data['user_id'],
					'dl_id'			=> $dl_id
				]);

				$user_has_rated = $this->dlext_constants::DL_TRUE;
			}
			else if ($drop)
			{
				$sql = 'DELETE FROM ' . $this->dlext_table_dl_ratings . '
						WHERE user_id = ' . (int) $this->user->data['user_id'] . '
							AND dl_id = ' . (int) $dl_id;

				$user_has_rated = $this->dlext_constants::DL_FALSE;
			}

			$this->db->sql_query($sql);

			$sql = 'SELECT AVG(rate_point) AS rating, COUNT(rate_point) AS total FROM ' . $this->dlext_table_dl_ratings . '
				WHERE dl_id = ' . (int) $dl_id . '
				GROUP BY dl_id';
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			if (!$this->db->sql_affectedrows())
			{
				$new_rating = 0;
				$total_ratings = 0;
			}
			else
			{
				$new_rating = ceil($row['rating'] * $this->dlext_constants::DL_RATING_MULTIFIER);
				$total_ratings = $row['total'];
			}
			$this->db->sql_freeresult($result);

			$sql = 'UPDATE ' . $this->dlext_table_downloads . ' SET ' . $this->db->sql_build_array('UPDATE', [
				'rating' => $new_rating
			]) . ' WHERE id = ' . (int) $dl_id;
			$this->db->sql_query($sql);

			if ($new_rating)
			{
				$rate_points = $new_rating / $this->dlext_constants::DL_RATING_MULTIFIER;
			}
			else
			{
				$rate_points = 0;
			}

			$return_ary['count']['title']	= $this->language->lang('DL_RATING_HOVER', $rate_points, $this->config['dl_rate_points']);
			$return_ary['count']['max']		= $this->config['dl_rate_points'];
			$return_ary['count']['dlId']	= $dl_id;

			for ($i = 0; $i < $this->config['dl_rate_points']; ++$i)
			{
				$j = $i + 1;

				if ($user_has_rated)
				{
					$return_ary['stars'][$i]['ajax'] = 0;
					$return_ary['stars'][$i]['icon'] = ($j <= $rate_points) ? 'yes' : 'no';
				}
				else
				{
					$return_ary['stars'][$i]['ajax'] = $j;
					$return_ary['stars'][$i]['icon'] = ($j <= $rate_points) ? 'yes' : 'no';
				}
			}

			if ($user_has_rated)
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
				$return_ary['count']['count'] = '';
			}

			return new Response(json_encode($return_ary));
		}

		return new Response(json_encode([]));
	}

	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames
}
