<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/**
 * Language pack for Extension permissions [Spanish]
 */

/**
 * DO NOT CHANGE
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

// Download Extension Permissions
$lang = array_merge($lang, [
	'ACP_DOWNLOADS'			=> 'Panel de Descargas',

	'ACL_A_DL_OVERVIEW'		=> 'Puede ver la pantalla de inicio',
	'ACL_A_DL_CONFIG'		=> 'Puede gestionar los ajustes generales',
	'ACL_A_DL_TRAFFIC'		=> 'Puede gestionar el tráfico',
	'ACL_A_DL_CATEGORIES'	=> 'Puede gestionar las categorías',
	'ACL_A_DL_FILES'		=> 'Puede gestionar las descargas',
	'ACL_A_DL_PERMISSIONS'	=> 'Puede gestionar los permisos',
	'ACL_A_DL_STATS'		=> 'Puede ver y gestionar las estadísticas',
	'ACL_A_DL_BLACKLIST'	=> 'Puede gestionar la lista negra de extensiones de archivo',
	'ACL_A_DL_TOOLBOX'		=> 'Puede utilizar la caja de herramientas',
	'ACL_A_DL_FIELDS'		=> 'Puede gestionar los campos definidos por el usuario',
	'ACL_A_DL_PERM_CHECK'	=> 'Puede probar los permisos del usuario',
	'ACL_A_DL_ASSISTANT'	=> 'Puede ejecutar el asistente de configuración',
]);
