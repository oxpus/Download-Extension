<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2022 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\controller;

use Symfony\Component\HttpFoundation\Response;

class unassigned
{
	/* phpbb objects */
	protected $db;
	protected $language;
	protected $request;

	/* extension owned objects */
	protected $dlext_auth;
	protected $dlext_physical;
	protected $dlext_constants;

	protected $dlext_table_dl_cat;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\language\language 				$language
	 * @param \phpbb\request\request 				$request
	 * @param \oxpus\dlext\core\auth				$dlext_auth
	 * @param \oxpus\dlext\core\physical			$dlext_physical
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param string								$dlext_table_dl_cat
	 */
	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		\phpbb\language\language $language,
		\phpbb\request\request $request,
		\oxpus\dlext\core\auth $dlext_auth,
		\oxpus\dlext\core\physical $dlext_physical,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		$dlext_table_dl_cat
	)
	{
		$this->db			= $db;
		$this->language		= $language;
		$this->request		= $request;

		$this->dlext_auth			= $dlext_auth;
		$this->dlext_physical		= $dlext_physical;
		$this->dlext_constants		= $dlext_constants;

		$this->dlext_table_dl_cat	= $dlext_table_dl_cat;
	}

	public function handle()
	{
		$filey = [];
		$filen = [];
		$sizes = [];
		$exist = [];
		$browse_dir = '';
		$unassigned_files = $this->dlext_constants::DL_FALSE;
		$existing_files = [];

		$cat_id = $this->request->variable('cat_id', 0);
		$mode = $this->request->variable('mode', '');

		$return_json = ($mode == 'acp') ? '			<span class="dl-red">' . $this->language->lang('DL_NO_UNASSIGNED_FILES') . '</span>' : '';

		if (!$cat_id)
		{
			return new Response(json_encode($return_json));
		}

		$sql = 'SELECT path FROM ' . $this->dlext_table_dl_cat . ' WHERE id = ' . (int) $cat_id;
		$result = $this->db->sql_query($sql);
		$path = $this->db->sql_fetchfield('path');
		$this->db->sql_freeresult($result);

		$this->dlext_physical->get_files_assignments($path, $browse_dir, $exist, $filey, $filen, $sizes, $unassigned_files, $existing_files);

		if ($unassigned_files)
		{
			if ($mode == 'acp')
			{
				$ext_blacklist = $this->dlext_auth->get_ext_blacklist();

				if (!empty($ext_blacklist))
				{
					$blacklist_explain = '<br>' . $this->language->lang('DL_FORBIDDEN_EXT_EXPLAIN', implode(', ', $ext_blacklist));
				}
				else
				{
					$blacklist_explain = '';
				}

				$return_json = '			<select name="file_name">';
				$return_json .= '				<option value="0">' . $this->language->lang('DL_NO_CHANGE') . '</option>';

				foreach ($exist as $key => $value)
				{
					if (!$value)
					{
						$file_ary = explode('|~|', $filey[$key]);
						$return_json .= '				<option value="' . $file_ary[1] . '">' . $file_ary[1] . '</option>';
					}
				}

				$return_json .= '			</select>';
				$return_json .= '			<span class="small">' . $blacklist_explain . '</span>';
			}
			else
			{
				$return_json = $this->language->lang('DL_UNASSIGNED_EXISTS', $unassigned_files);
			}
		}

		return new Response(json_encode($return_json));
	}
}
