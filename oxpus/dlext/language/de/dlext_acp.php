<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2024 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/*
* [ german ] language file for Download Extension
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
	'DL_LIMIT_TITLE_SHOW'				=> 'Aktuelle Limits anzeigen',
	'DL_LIMIT_TITLE_HIDE'				=> 'Eingestellte Limits',
	'DL_PHP_LIMITS'						=> 'Einstellungen in PHP',
	'DL_CUR_LIMITS'						=> 'Einstellungen innerhalb der Download Erweiterung',

	'DL_PHP_INI_EXPLAIN'				=> 'Die PHP-Einstellungen sind in der Datei <strong>%1$s</strong> oder in einer der eingebundenen Konfigurationsdateien zu ändern; siehe PHP-Information.',

	'DL_LIMIT_PHP_FILE_UPLOAD'			=> 'file_upload',
	'DL_LIMIT_PHP_MAX_FILE_UPLOAD'		=> 'max_file_upload',
	'DL_LIMIT_PHP_MAX_INPUT_TIME'		=> 'max_input_time',
	'DL_LIMIT_PHP_MAX_EXECUTION_TIME'	=> 'max_execution_time',
	'DL_LIMIT_PHP_MEMORY_LIMIT'			=> 'memory_limit',
	'DL_LIMIT_PHP_POST_MAX_SIZE'		=> 'post_max_size',
	'DL_LIMIT_PHP_UPLOAD_MAX_FILESIZE'	=> 'upload_max_filesize',

	'DL_LIMIT_TOTAL_REMAIN'				=> 'Verbleibender Speicherplatz für alle Download-Dateien',
	'DL_LIMIT_THUMBNAIL_XY_SIZE'		=> 'Maximale Abmessungen von hochgeladenen Thumbnails',
	'DL_LIMIT_THUMBNAIL_XYSIZE'			=> '%1$s x %2$s Pixel',

	'DL_LIMIT_PHP_FILE_UPLOAD_EXPLAIN'			=> 'Standard = 1 (On)<br />Erlaubt PHP die Verarbeitung von hochgeladenen Dateien.<br />Andernfalls stehen diese Dateien PHP nicht zur Verfügung.',
	'DL_LIMIT_PHP_MAX_FILE_UPLOAD_EXPLAIN'		=> 'Standard = 20, Empfehlung >= 10<br />Begrenzt die Anzahl, gleichzeitig hochgeladener Dateien, welche PHP verarbeiten kann.',
	'DL_LIMIT_PHP_MAX_INPUT_TIME_EXPLAIN'		=> 'Standard = -1 (nicht aktiv)<br />Maximale Verarbeitungsdauer von POST- und GET-Daten in Sekunden.<br />Die Zeitspanne beginnt mit dem Start von PHP und endet mit dem Start des ersten PHP-Scripts.',
	'DL_LIMIT_PHP_MAX_EXECUTION_TIME_EXPLAIN'	=> 'Standard = 30 Sekunden<br />Maximale Ausführungszeit eines PHP-Scriptes vom PHP-Start bis zum Ende eines auszuführenden Scriptes.<br />Nach dieser Zeitspanne bricht PHP die Verarbeitung ab, sofern das Script nicht vorzeitig beendet wurde.',
	'DL_LIMIT_PHP_MEMORY_LIMIT_EXPLAIN'			=> 'Standard = 128 MB (in moderneren PHP-Versionen)<br />Begrenzt den Hauptspeicher des Servers, welchen PHP verwenden darf.<br />Sollte passend der verwendeten Download Dateien erhöht werden.<br />Es wird dringend empfohlen, das verfügbare RAM-Speicherlimit des Servers nicht zu überschreiten.',
	'DL_LIMIT_PHP_POST_MAX_SIZE_EXPLAIN'		=> 'Standard = 8 MB<br />Maximaler Speicherverbrauch für einen HTTP(S)-Uploadstream / HTML-Formular.<br />Wird durch das Limit unter memory_limit begrenzt.<br />Sollte erhöht werden, wenn größere Dateien zum Download bereitgestellt werden sollen.',
	'DL_LIMIT_PHP_UPLOAD_MAX_FILESIZE_EXPLAIN'	=> 'Standard = 2 MB<br />Maximale Dateigröße, welche PHP nach dem Absenden eines HTML-Formulars je Datei verarbeiten darf.<br />Größere Dateien werden PHP nicht zur Verfügung stehen.<br />Sollte erhöht werden, wenn größere Dateien zum Download bereitgestellt werden sollen.<br /><strong>Achtung:</strong><br />Dieses Limit zählt je hochgeladener Datei. Wenn mehrere Dateien gemeinsam hochgeladen werden sollen, wird das Limit je Datei multipliziert und dabei insgesamt durch das Limit post_max_size begrenzt.',

	'DL_LIMIT_TRAFFIC_USER_REMAIN_EXPLAIN'		=> 'Der aktuell für alle registrierten Benutzer zur verfügung stehende Download Traffic im aktuellen Monat.',
	'DL_LIMIT_TRAFFIC_GUESTS_REMAIN_EXPLAIN'	=> 'Der aktuell für alle Gäste zur verfügung stehende Download Traffic im aktuellen Monat.',
	'DL_LIMIT_TOTAL_REMAIN_EXPLAIN'				=> 'Der maximal zur Verfügung gestellte Speicherplatz für alle Dateien, welche zum Download angeboten werden sollen.<br /><strong>Wichtig:</strong><br />Thumbnails und Dateiversionen werden von diesem Limit ausgenommen!<br /><strong>Achtung:</strong><br />Dieses Limit darf den physisch verfügbaren Speicherplatz des Servers nicht erreichen oder gar überschreiten, da andernfalls der Server wegen Speichermangel ausfallen könnnte!<br />Ebenso ist darauf zu achten, dass die Verzeichnisgrößen der Thumbnails und Dateiversionen für dieses physische Limit mit berücksichtigt werden müssen.',
	'DL_LIMIT_THUMBNAIL_SIZE_EXPLAIN'			=> 'Hochgeladene Thumbnails mit einer höheren Dateigröße werden abgelehnt und nicht in die Downloads übernommen.',
	'DL_LIMIT_THUMBNAIL_XY_SIZE_EXPLAIN'		=> 'Maximale Abmessungen in Pixeln für die Breite und Höhe aller hochgeladener Thumbnaildateien.<br />Größere Bilddateien werden abgelehnt und nicht übernommen.',
	'DL_LIMIT_THUMBNAIL_XYSIZE_EXPLAIN'			=> '%1$s x %2$s Pixel',
]);
