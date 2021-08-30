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

class help
{
	/* phpbb objects */
	protected $language;
	protected $request;

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language 		$language
	 * @param \phpbb\request\request 		$request
	 */
	public function __construct(
		\phpbb\language\language $language,
		\phpbb\request\request $request
	)
	{
		$this->language		= $language;
		$this->request		= $request;
	}

	public function handle()
	{
		$this->language->add_lang('help', 'oxpus/dlext');

		$help_key	= $this->request->variable('help_key', '');
		$value		= $this->request->variable('value', '', true);
		$value = ($value == 'undefined') ? '' : $value;

		//
		// Pull all user config data
		//
		if ($help_key && $this->language->lang('HELP_' . $help_key) != 'HELP_' . $help_key)
		{
			$help_string = $this->language->lang('HELP_' . $help_key);
		}
		else
		{
			$help_string = $this->language->lang('DL_NO_HELP_AVAILABLE');
		}

		if ($value)
		{
			$help_key = $value;
		}

		if ($value)
		{
			$help_option = $help_key;
		}
		else if ($this->language->lang($help_key) != $help_key)
		{
			$help_option = $this->language->lang($help_key);
		}
		else
		{
			$help_option = '';
		}

		return new Response(json_encode(['title' => $this->language->lang('HELP_TITLE'), 'option' => $help_option, 'string' => $help_string]));
	}
}
