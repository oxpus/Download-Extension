<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2024 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/*
* [ spanish ] language file for Download Extension
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

$lang = array_merge($lang, [
	'DL_LIMIT_TITLE_SHOW'				=> 'Ver límites actuales',
	'DL_LIMIT_TITLE_HIDE'				=> 'Pon límites',
	'DL_PHP_LIMITS'						=> 'Configuraciones en PHP',
	'DL_CUR_LIMITS'						=> 'Configuraciones dentro de la extensión de descarga',

	'DL_PHP_INI_EXPLAIN'				=> 'La configuración de PHP se puede cambiar en el archivo <strong>%1$s</strong> o en uno de los archivos de configuración incluidos; ver información de PHP.',

	'DL_LIMIT_PHP_FILE_UPLOAD'			=> 'file_upload',
	'DL_LIMIT_PHP_MAX_FILE_UPLOAD'		=> 'max_file_upload',
	'DL_LIMIT_PHP_MAX_INPUT_TIME'		=> 'max_input_time',
	'DL_LIMIT_PHP_MAX_EXECUTION_TIME'	=> 'max_execution_time',
	'DL_LIMIT_PHP_MEMORY_LIMIT'			=> 'memory_limit',
	'DL_LIMIT_PHP_POST_MAX_SIZE'		=> 'post_max_size',
	'DL_LIMIT_PHP_UPLOAD_MAX_FILESIZE'	=> 'upload_max_filesize',

	'DL_LIMIT_TOTAL_REMAIN'				=> 'Espacio restante para todos los archivos descargados',
	'DL_LIMIT_THUMBNAIL_XY_SIZE'		=> 'Dimensiones máximas de las miniaturas cargadas',
	'DL_LIMIT_THUMBNAIL_XYSIZE'			=> '%1$s x %2$s Pixel',

	'DL_LIMIT_PHP_FILE_UPLOAD_EXPLAIN'			=> 'Predeterminado = 1 (Activado)<br />Permite que PHP procese archivos cargados.<br />De lo contrario, estos archivos no estarán disponibles para PHP.',
	'DL_LIMIT_PHP_MAX_FILE_UPLOAD_EXPLAIN'		=> 'Predeterminado = 20, recomendación >= 10<br />Limita la cantidad de archivos cargados simultáneamente que PHP puede procesar.',
	'DL_LIMIT_PHP_MAX_INPUT_TIME_EXPLAIN'		=> 'Predeterminado = -1 (no activo)<br />Tiempo máximo de procesamiento para datos POST y GET en segundos.<br />El período de tiempo comienza con el inicio de PHP y finaliza con el inicio del primer script PHP.',
	'DL_LIMIT_PHP_MAX_EXECUTION_TIME_EXPLAIN'	=> 'Predeterminado = 30 segundos<br />Tiempo máximo de ejecución de un script PHP desde el inicio de PHP hasta el final de un script a ejecutar.<br />Después de este período de tiempo, PHP deja de procesarse a menos que el script haya finalizado prematuramente.',
	'DL_LIMIT_PHP_MEMORY_LIMIT_EXPLAIN'			=> 'Predeterminado = 128 MB (en versiones PHP más modernas)<br />Limita la memoria principal del servidor, que PHP puede usar.<br />Debe aumentarse para que coincida con los archivos de descarga utilizados.<br />Se recomienda encarecidamente para utilizar la disponible No exceda el límite de memoria RAM del servidor.',
	'DL_LIMIT_PHP_POST_MAX_SIZE_EXPLAIN'		=> 'Predeterminado = 8 MB<br />Consumo máximo de memoria para un flujo de carga HTTP(S)/formulario HTML.<br />Limitado por el límite bajo límite_memoria.<br />Debe aumentarse cuando haya archivos más grandes disponibles para su descarga.',
	'DL_LIMIT_PHP_UPLOAD_MAX_FILESIZE_EXPLAIN'	=> 'Predeterminado = 2 MB<br />Tamaño máximo de archivo que PHP puede procesar por archivo después de enviar un formulario HTML.<br />Los archivos más grandes no estarán disponibles para PHP.<br />Debe aumentarse si se utilizan archivos más grandes. debe estar disponible para su descarga.<br /><strong>Atención:</strong><br />Este límite cuenta por archivo subido. Si se van a cargar varios archivos juntos, el límite por archivo se multiplica y, en general, está limitado por el límite post_max_size.',

	'DL_LIMIT_TRAFFIC_USER_REMAIN_EXPLAIN'		=> 'El tráfico de descarga actualmente disponible para todos los usuarios registrados en el mes actual.',
	'DL_LIMIT_TRAFFIC_GUESTS_REMAIN_EXPLAIN'	=> 'El tráfico de descarga actualmente disponible para todos los invitados en el mes actual.',
	'DL_LIMIT_TOTAL_REMAIN_EXPLAIN'				=> 'El espacio de almacenamiento máximo proporcionado para todos los archivos que se ofrecerán para descargar.<br /><strong>Importante:</strong><br />¡Las miniaturas y las versiones de archivos están excluidas de este límite!<br /><strong> Atención:</strong><br />Este límite no debe alcanzar ni superar el espacio de almacenamiento físicamente disponible del servidor; de lo contrario, el servidor podría fallar debido a la falta de memoria.<br />También es importante asegurarse de que el Se deben tener en cuenta los tamaños de directorio de las miniaturas y las versiones de archivos para este límite físico.',
	'DL_LIMIT_THUMBNAIL_SIZE_EXPLAIN'			=> 'Las miniaturas cargadas con un tamaño de archivo mayor serán rechazadas y no se incluirán en las descargas.',
	'DL_LIMIT_THUMBNAIL_XY_SIZE_EXPLAIN'		=> 'Dimensiones máximas en píxeles para el ancho y alto de todos los archivos de miniaturas cargados.<br />Los archivos de imágenes más grandes serán rechazados y no aceptados.',
	'DL_LIMIT_THUMBNAIL_XYSIZE_EXPLAIN'			=> '%1$s x %2$s Pixel',
]);
