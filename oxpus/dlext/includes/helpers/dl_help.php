<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/*
* connect to phpBB
*/
if ( !defined('IN_PHPBB') )
{
	exit;
}

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
	$help_string = $this->language->lang('DL_NO_HELP_AVIABLE');
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

$json_out = json_encode(array('title' => $this->language->lang('HELP_TITLE'), 'option' => $help_option, 'string' => $help_string));

$http_headers = array(
	'Content-type' => 'text/html; charset=UTF-8',
	'Cache-Control' => 'private, no-cache="set-cookie"',
	'Expires' => gmdate('D, d M Y H:i:s', time()) . ' GMT',
);

foreach ($http_headers as $hname => $hval)
{
	header((string) $hname . ': ' . (string) $hval);
}

$this->template->set_filenames(array(
	'body' => 'dl_json.html')
);
$this->template->assign_var('JSON_OUTPUT', $json_out);
$this->template->display('body');

garbage_collection();
exit_handler();
